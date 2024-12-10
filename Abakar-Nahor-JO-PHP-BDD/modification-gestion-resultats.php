<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

// Initialiser une variable pour afficher les messages
$message = "";

// Si une requête POST est détectée, traiter la modification ou l'ajout d'un résultat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_athlete = htmlspecialchars($_POST['id_athlete'], ENT_QUOTES, 'UTF-8');
        $id_epreuve = htmlspecialchars($_POST['id_epreuve'], ENT_QUOTES, 'UTF-8');
        $resultat = htmlspecialchars($_POST['resultat'], ENT_QUOTES, 'UTF-8');

        // Vérification des IDs
        if (!filter_var($id_athlete, FILTER_VALIDATE_INT) || !filter_var($id_epreuve, FILTER_VALIDATE_INT)) {
            throw new Exception("ID de l'athlète ou de l'épreuve invalide.");
        }

        // Mise à jour ou ajout du résultat
        if (isset($_POST['modifier'])) {
            // Mise à jour du résultat
            $sql = "UPDATE PARTICIPER SET resultat = :resultat WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
            $stmt = $pdo->prepare($sql);
            $message = "Le résultat a été mis à jour avec succès.";
        } else {
            // Ajout d'un nouveau résultat
            $sql = "INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) VALUES (:id_athlete, :id_epreuve, :resultat)";
            $stmt = $pdo->prepare($sql);
            $message = "Le résultat a été ajouté avec succès.";
        }

        $stmt->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $stmt->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt->bindParam(':resultat', $resultat, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        // Gérer les erreurs
        $message = "Erreur : " . $e->getMessage();
    }
}


// Suppression d'un résultat
if (isset($_GET['delete'])) {
    try {
        $id_athlete = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');
        $id_epreuve = htmlspecialchars($_GET['epreuve'], ENT_QUOTES, 'UTF-8');

        if (!filter_var($id_athlete, FILTER_VALIDATE_INT) || !filter_var($id_epreuve, FILTER_VALIDATE_INT)) {
            throw new Exception("ID de l'athlète ou de l'épreuve invalide.");
        }

        $sql = "DELETE FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $stmt->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt->execute();

        $message = "Le résultat a été supprimé avec succès.";
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}


// Récupérer tous les résultats pour les afficher dans un tableau
$sql = "
    SELECT p.id_athlete, a.nom_athlete, e.nom_epreuve, pay.nom_pays, p.resultat, e.id_epreuve
    FROM PARTICIPER p
    JOIN ATHLETE a ON p.id_athlete = a.id_athlete
    JOIN EPREUVE e ON p.id_epreuve = e.id_epreuve
    JOIN PAYS pay ON a.id_pays = pay.id_pays
    ORDER BY e.nom_epreuve, a.nom_athlete
";
$stmt = $pdo->query($sql);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modification-gestion-resultats.css">
    <title>Gestion des Résultats</title>
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

    <h1 style="text-align:center;">Gestion des Résultats</h1>
    <br><br>

    <!-- Lien pour ajouter un nouveau résultat -->
    <center><a href="ajouter-resultat.php">Ajouter un Résultat</a></center>

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if ($message): ?>
    <p style="text-align:center; color:green;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<!-- Tableau pour afficher les résultats -->
<section class="conteneur">
    <table>
        <thead>
            <tr>
                <th>Épreuve</th>
                <th>Athlète</th>
                <th>Pays</th>
                <th>Résultat</th>
                <th>Modifier</th>
                <th>Supprimer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultats as $resultat): ?>
                <tr>
                    <td data-label='Épreuve:'><?= htmlspecialchars($resultat['nom_epreuve'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label='Athlète:'><?= htmlspecialchars($resultat['nom_athlete'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label='Pays:'><?= htmlspecialchars($resultat['nom_pays'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td  data-label='Résultat:'><?= htmlspecialchars($resultat['resultat'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <!-- Bouton pour modifier -->
                        <form method="GET" action="modifier-resultat.php" style="display:inline;">
                            <input type="hidden" name="id_athlete" value="<?= htmlspecialchars($resultat['id_athlete'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_epreuve" value="<?= htmlspecialchars($resultat['id_epreuve'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit">Modifier</button>
                        </form>
                    </td>
                    <td>
                        <!-- Formulaire pour supprimer -->
                        <form method="POST" action="supprimer-resultat.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_athlete" value="<?= htmlspecialchars($resultat['id_athlete'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_epreuve" value="<?= htmlspecialchars($resultat['id_epreuve'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer le résultat de <?= htmlspecialchars($resultat['nom_athlete'], ENT_QUOTES, 'UTF-8') ?> pour l\'épreuve <?= htmlspecialchars($resultat['nom_epreuve'], ENT_QUOTES, 'UTF-8') ?> ?');">Supprimer</button>
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
