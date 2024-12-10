<?php
include 'db.php'; // Inclusion du fichier de connexion à la base de données
session_start();

// Protection CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Token CSRF invalide.";
        header('Location: modification-gestion-athletes.php');
        exit();
    }
}

// Génération du token CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF sécurisé
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: modification-gestion-athletes.php");
    exit();
} else {
    $id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

    // Vérifiez si l'ID de l'athlète est un entier valide
    if ($id_athlete === false) {
        $_SESSION['error'] = "ID de l'athlète invalide.";
        header("Location: modification-gestion-athletes.php");
        exit();
    }

    try {
        // Préparez la requête SQL pour supprimer l'athlète
        $sql = "DELETE FROM athlete WHERE id_athlete = :id_athlete"; 
        
        // Exécutez la requête SQL avec le paramètre
        $statement = $pdo->prepare($sql);
        $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        
        // Exécutez la requête
        if ($statement->execute()) {
            // Message de succès
            $_SESSION['success'] = "L'athlète a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'athlète.";
        }

        // Redirigez vers la page de gestion des athlètes après la suppression
        header('Location: modification-gestion-athletes.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression de l'athlète : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header('Location: modification-gestion-athletes.php');
        exit();
    }
}

// Afficher les erreurs en PHP (utile pour le développement local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
