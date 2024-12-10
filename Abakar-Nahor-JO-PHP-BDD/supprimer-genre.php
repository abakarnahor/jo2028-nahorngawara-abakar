<?php
include 'db.php'; // Assurez-vous que le chemin est correct

session_start();

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: modification-gestion-genres.php');
        exit();
    }
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID du genre est fourni dans l'URL
if (!isset($_GET['id_genre'])) {
    $_SESSION['error'] = "ID du genre manquant.";
    header("Location: modification-gestion-genres.php");
    exit();
} else {
    $id_genre = filter_input(INPUT_GET, 'id_genre', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID du genre est un entier valide
    if ($id_genre === false) {
        $_SESSION['error'] = "ID du genre invalide.";
        header("Location: modification-gestion-genres.php");
        exit();
    }

    try {
        // Préparez la requête SQL pour supprimer le genre
        $sql = "DELETE FROM genre WHERE id_genre = :id_genre"; // Vérifiez si la table s'appelle "genre"
        
        // Exécutez la requête SQL avec le paramètre
        $statement = $pdo->prepare($sql); // Utilisez la bonne variable de connexion à la base de données
        $statement->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($statement->execute()) {
            // Message de succès
            $_SESSION['success'] = "Le genre a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du genre.";
        }

        // Redirigez vers la page précédente après la suppression
        header('Location: modification-gestion-genres.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du genre : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header('Location: modification-gestion-genres.php');
        exit();
    }
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
