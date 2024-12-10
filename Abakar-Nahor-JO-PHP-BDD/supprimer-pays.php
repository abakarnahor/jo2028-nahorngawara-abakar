<?php
include 'db.php'; // Assurez-vous que le chemin vers la base de données est correct

session_start();

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: modification-gestion-pays.php');
        exit();
    }
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['id_pays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: modification-gestion-pays.php");
    exit();
} else {
    $id_pays = filter_input(INPUT_GET, 'id_pays', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID du pays est un entier valide
    if ($id_pays === false) {
        $_SESSION['error'] = "ID du pays invalide.";
        header("Location: modification-gestion-pays.php");
        exit();
    }

    try {
        // Préparez la requête SQL pour supprimer le pays
        $sql = "DELETE FROM pays WHERE id_pays = :id_pays"; // Vérifiez si la table s'appelle bien "pays"
        
        // Exécutez la requête SQL avec le paramètre
        $statement = $pdo->prepare($sql); // Assurez-vous que vous utilisez la bonne variable de connexion ici
        $statement->bindParam(':id_pays', $id_pays, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($statement->execute()) {
            // Message de succès
            $_SESSION['success'] = "Le pays a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du pays.";
        }

        // Redirigez vers la page précédente après la suppression
        header('Location: modification-gestion-pays.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du pays : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header('Location: modification-gestion-pays.php');
        exit();
    }
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
