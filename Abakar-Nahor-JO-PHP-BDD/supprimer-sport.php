<?php
include 'db.php'; // Assurez-vous que le chemin est correct

session_start();

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: modification-gestion-sports.php');
        exit();
    }
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID du sport est fourni dans l'URL
if (!isset($_GET['id_sport'])) {
    $_SESSION['error'] = "ID du sport manquant.";
    header("Location: modification-gestion-sports.php");
    exit();
} else {
    $id_sport = filter_input(INPUT_GET, 'id_sport', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID du sport est un entier valide
    if ($id_sport === false) {
        $_SESSION['error'] = "ID du sport invalide.";
        header("Location: modification-gestion-sports.php");
        exit();
    }

    try {
        // Préparez la requête SQL pour supprimer le sport
        $sql = "DELETE FROM sport WHERE id_sport = :id_sport"; // Vérifiez si la table s'appelle "sport" et non "SPORT"
        
        // Exécutez la requête SQL avec le paramètre
        $statement = $pdo->prepare($sql); // Assurez-vous que vous utilisez la bonne variable de connexion ici
        $statement->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($statement->execute()) {
            // Message de succès
            $_SESSION['success'] = "Le sport a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du sport.";
        }

        // Redirigez vers la page précédente après la suppression
        header('Location: modification-gestion-sports.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression du sport : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header('Location: modification-gestion-sports.php');
        exit();
    }
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
