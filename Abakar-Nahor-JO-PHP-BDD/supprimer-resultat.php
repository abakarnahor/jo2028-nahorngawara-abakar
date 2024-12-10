<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

// Initialiser une variable pour afficher les messages
$message = "";

// Vérifier si la requête est POST pour traiter la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_athlete = htmlspecialchars($_POST['id_athlete'], ENT_QUOTES, 'UTF-8');
        $id_epreuve = htmlspecialchars($_POST['id_epreuve'], ENT_QUOTES, 'UTF-8');

        // Vérification des IDs
        if (!filter_var($id_athlete, FILTER_VALIDATE_INT) || !filter_var($id_epreuve, FILTER_VALIDATE_INT)) {
            throw new Exception("ID de l'athlète ou de l'épreuve invalide.");
        }

        // Suppression du résultat dans la base de données
        $sql = "DELETE FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $stmt->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt->execute();

        // Redirection après suppression avec message de succès
        $message = "Le résultat a été supprimé avec succès.";
        header('Location: modification-gestion-resultats.php?message=' . urlencode($message));
        exit;
    } catch (Exception $e) {
        // En cas d'erreur, afficher un message
        $message = "Erreur : " . $e->getMessage();
        header('Location: modification-gestion-resultats.php?message=' . urlencode($message));
        exit;
    }
} else {
    // Rediriger si la requête n'est pas POST
    header('Location: modification-gestion-resultats.php');
    exit;
}
?>
