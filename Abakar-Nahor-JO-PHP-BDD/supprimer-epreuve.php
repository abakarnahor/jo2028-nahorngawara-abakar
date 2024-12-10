<?php
include 'db.php'; // Inclusion du fichier de connexion à la base de données
session_start();

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: modification-gestion-calendrier.php');
        exit();
    }
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: modification-gestion-calendrier.php");
    exit();
} else {
    $id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID de l'épreuve est un entier valide
    if ($id_epreuve === false) {
        $_SESSION['error'] = "ID de l'épreuve invalide.";
        header("Location: modification-gestion-calendrier.php");
        exit();
    }

    try {
        // Préparez la requête SQL pour supprimer l'épreuve
        $sql = "DELETE FROM epreuve WHERE id_epreuve = :id_epreuve"; 
        
        // Exécutez la requête SQL avec le paramètre
        $statement = $pdo->prepare($sql);
        $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($statement->execute()) {
            // Message de succès
            $_SESSION['success'] = "L'épreuve a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'épreuve.";
        }

        // Redirigez vers la page de gestion du calendrier après la suppression
        header('Location: modification-gestion-calendrier.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression de l'épreuve : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header('Location: modification-gestion-calendrier.php');
        exit();
    }
}

// Afficher les erreurs en PHP (utile pour le développement local)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
