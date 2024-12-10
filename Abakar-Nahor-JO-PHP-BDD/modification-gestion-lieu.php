<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

// Initialiser une variable pour afficher les messages
$message = "";

// Si une requête POST est détectée, traiter la modification d'un lieu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_lieu = htmlspecialchars($_POST['id_lieu'], ENT_QUOTES, 'UTF-8');
        $nom_lieu = htmlspecialchars($_POST['nom_lieu'], ENT_QUOTES, 'UTF-8');

        // Vérification de l'ID du lieu (doit être un entier)
        if (!filter_var($id_lieu, FILTER_VALIDATE_INT)) {
            throw new Exception("ID du lieu invalide.");
        }

        // Vérifier si un lieu avec ce nom existe déjà
        $sql_verification = "SELECT COUNT(*) FROM lieu WHERE nom_lieu = :nom_lieu AND id_lieu != :id_lieu";
        $stmt_verification = $pdo->prepare($sql_verification);
        $stmt_verification->bindParam(':nom_lieu', $nom_lieu, PDO::PARAM_STR);
        $stmt_verification->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $stmt_verification->execute();
        $doublon = $stmt_verification->fetchColumn();

        if ($doublon > 0) {
            throw new Exception("Un lieu avec ce nom existe déjà.");
        }

        // Mise à jour du nom du lieu
        $sql = "UPDATE lieu SET nom_lieu = :nom_lieu WHERE id_lieu = :id_lieu";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $stmt->bindParam(':nom_lieu', $nom_lieu, PDO::PARAM_STR);
        $stmt->execute();

        $message = "Le lieu a été mis à jour avec succès.";
    } catch (Exception $e) {
        // Gérer les erreurs
        $message = "Erreur : " . $e->getMessage();
    }
}

// Suppression d'un lieu
if (isset($_GET['delete'])) {
    try {
        $id_lieu = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');

        if (!filter_var($id_lieu, FILTER_VALIDATE_INT)) {
            throw new Exception("ID du lieu invalide.");
        }

        $sql = "DELETE FROM lieu WHERE id_lieu = :id_lieu";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $stmt->execute();

        $message = "Le lieu a été supprimé avec succès.";
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// Récupérer tous les lieux pour les afficher dans un tableau
$sql = "SELECT * FROM lieu";
$stmt = $pdo->query($sql);
$lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modification-gestion-sports.css">
    <title>Gestion des Lieux</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" /> 

</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <a href="Accueil-admin.php">Accueil Administrateur</a>
        <a href="modification-gestion-sports.php">Gestion Sports</a>
        <a href="modification-gestion-lieu.php">Gestion Lieu</a>
        <a href="modification-gestion-calendrier.php">Gestion Calendrier</a>
        <a href="modification-gestion-pays.php">Gestion Pays</a>
        <a href="modification-gestion-genres.php">Gestion Genres</a>
        <a href="modification-gestion-athletes.php">Gestion Athlètes</a>
        <a href="modification-gestion-resultats.php">Gestion Résultats</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>

    <!-- Section principale pour afficher les résultats -->
    <h1 style="text-align:center;">Liste des Lieux</h1>
<br> <br>
  <center> <a href="ajouter-lieu.php">Ajouter un Lieu</a></center> 

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if ($message): ?>
        <p style="text-align:center; color:green;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    
        <!-- Tableau pour afficher les lieux -->
        <section class="conteneur">
            <table>
                <thead>
                    <tr>
                        <th>Lieu</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($lieux as $lieu): ?>
        <tr>
            <td><?= htmlspecialchars($lieu['nom_lieu'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <!-- Formulaire pour modifier le lieu -->
                <form method="GET" action="modifier-lieu.php" style="display:inline;">
                    <input type="hidden" name="id_lieu" value="<?= htmlspecialchars($lieu['id_lieu'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Modifier</button>
                </form>
            </td>
            <td>
                <!-- Formulaire pour supprimer le lieu -->
                <form method="GET" action="supprimer-lieu.php" style="display:inline;">
                    <input type="hidden" name="id_lieu" value="<?= htmlspecialchars($lieu['id_lieu'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu?');">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        </section>

<center><a href="Accueil-admin.php">Accueil Administration</a></center>

    <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
</body>
</html>
