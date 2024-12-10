<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

// Initialiser une variable pour afficher les messages
$message = "";

// Si une requête POST est détectée, traiter la modification d'un genre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_genre = htmlspecialchars($_POST['id_genre'], ENT_QUOTES, 'UTF-8');
        $nom_genre = htmlspecialchars($_POST['nom_genre'], ENT_QUOTES, 'UTF-8');

        // Vérification de l'ID du genre (doit être un entier)
        if (!filter_var($id_genre, FILTER_VALIDATE_INT)) {
            throw new Exception("ID du genre invalide.");
        }

        // Vérifier si un genre avec ce nom existe déjà
        $sql_verification = "SELECT COUNT(*) FROM genre WHERE nom_genre = :nom_genre AND id_genre != :id_genre";
        $stmt_verification = $pdo->prepare($sql_verification);
        $stmt_verification->bindParam(':nom_genre', $nom_genre, PDO::PARAM_STR);
        $stmt_verification->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
        $stmt_verification->execute();
        $doublon = $stmt_verification->fetchColumn();

        if ($doublon > 0) {
            throw new Exception("Un genre avec ce nom existe déjà.");
        }

        // Mise à jour du nom du genre
        $sql = "UPDATE genre SET nom_genre = :nom_genre WHERE id_genre = :id_genre";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
        $stmt->bindParam(':nom_genre', $nom_genre, PDO::PARAM_STR);
        $stmt->execute();

        $message = "Le genre a été mis à jour avec succès.";
    } catch (Exception $e) {
        // Gérer les erreurs
        $message = "Erreur : " . $e->getMessage();
    }
}

// Suppression d'un genre
if (isset($_GET['delete'])) {
    try {
        $id_genre = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');

        if (!filter_var($id_genre, FILTER_VALIDATE_INT)) {
            throw new Exception("ID du genre invalide.");
        }

        $sql = "DELETE FROM genre WHERE id_genre = :id_genre";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
        $stmt->execute();

        $message = "Le genre a été supprimé avec succès.";
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// Récupérer tous les genres pour les afficher dans un tableau
$sql = "SELECT * FROM genre";
$stmt = $pdo->query($sql);
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modification-gestion-sports.css">
    <title>Gestion des Genres</title>
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
    <h1 style="text-align:center;">Listes des Genres</h1>
<br> <br>
  <center> <a href="ajouter-genre.php">Ajouter un Genre</a></center> 

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if ($message): ?>
        <p style="text-align:center; color:green;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    
        <!-- Tableau pour afficher les genres -->
        <section class="conteneur">
            <table>
                <thead>
                    <tr>
                        <th>Genre</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($genres as $genre): ?>
        <tr>
            <td><?= htmlspecialchars($genre['nom_genre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <!-- Formulaire pour modifier le genre -->
                <form method="GET" action="modifier-genre.php" style="display:inline;">
                    <input type="hidden" name="id_genre" value="<?= htmlspecialchars($genre['id_genre'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Modifier</button>
                </form>
            </td>
            <td>
                <!-- Formulaire pour supprimer le genre -->
                <form method="GET" action="supprimer-genre.php" style="display:inline;">
                    <input type="hidden" name="id_genre" value="<?= htmlspecialchars($genre['id_genre'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce genre?');">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        </section>

<center><a href="Accueil-admin.php">Accueil Administration</a></center>
    </section>

    <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
</body>
</html>
