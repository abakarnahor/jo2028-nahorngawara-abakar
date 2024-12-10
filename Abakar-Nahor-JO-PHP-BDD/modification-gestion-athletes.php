<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

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

// Affichage des messages d'erreur ou de succès
$message = "";

// Si une requête POST est détectée, traiter la modification d'un athlète
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_athlete = htmlspecialchars($_POST['id_athlete'], ENT_QUOTES, 'UTF-8');
        $nom_athlete = htmlspecialchars($_POST['nom_athlete'], ENT_QUOTES, 'UTF-8');
        $prenom_athlete = htmlspecialchars($_POST['prenom_athlete'], ENT_QUOTES, 'UTF-8');
        $id_pays = htmlspecialchars($_POST['id_pays'], ENT_QUOTES, 'UTF-8');
        $id_genre = htmlspecialchars($_POST['id_genre'], ENT_QUOTES, 'UTF-8');

        // Vérification des ID de l'athlète, du pays et du genre (doivent être des entiers)
        if (!filter_var($id_athlete, FILTER_VALIDATE_INT) || !filter_var($id_pays, FILTER_VALIDATE_INT) || !filter_var($id_genre, FILTER_VALIDATE_INT)) {
            throw new Exception("ID invalide.");
        }

        // Mise à jour de l'athlète dans la base de données
        $sql = "UPDATE ATHLETE SET nom_athlete = :nom_athlete, prenom_athlete = :prenom_athlete, id_pays = :id_pays, id_genre = :id_genre WHERE id_athlete = :id_athlete";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $stmt->bindParam(':nom_athlete', $nom_athlete, PDO::PARAM_STR);
        $stmt->bindParam(':prenom_athlete', $prenom_athlete, PDO::PARAM_STR);
        $stmt->bindParam(':id_pays', $id_pays, PDO::PARAM_INT);
        $stmt->bindParam(':id_genre', $id_genre, PDO::PARAM_INT);
        $stmt->execute();

        $message = "L'athlète a été mis à jour avec succès.";
    } catch (Exception $e) {
        // Gérer les erreurs
        $message = "Erreur : " . $e->getMessage();
    }
}

// Suppression d'un athlète
if (isset($_GET['delete'])) {
    try {
        $id_athlete = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');

        if (!filter_var($id_athlete, FILTER_VALIDATE_INT)) {
            throw new Exception("ID de l'athlète invalide.");
        }

        $sql = "DELETE FROM ATHLETE WHERE id_athlete = :id_athlete";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $stmt->execute();

        $message = "L'athlète a été supprimé avec succès.";
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// Récupérer tous les athlètes pour les afficher dans un tableau
$sql = "SELECT ATHLETE.*, pays.nom_pays, genre.nom_genre 
        FROM ATHLETE 
        JOIN pays ON ATHLETE.id_pays = pays.id_pays 
        JOIN genre ON ATHLETE.id_genre = genre.id_genre";
$stmt = $pdo->query($sql);
$athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modification-gestion-epreuves.css">
    <title>Gestion des Athlètes</title>
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
    <h1 style="text-align:center;">Liste des Athlètes</h1>
    <br><br>
    <center><a href="ajouter-athlete.php">Ajouter un Athlète</a></center>

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if ($message): ?>
        <p style="text-align:center; color:green;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <!-- Tableau pour afficher les athlètes -->
    <section class="conteneur">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Pays</th>
                    <th>Genre</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($athletes as $athlete): ?>
        <tr>
            <td data-label='Nom:'><?= htmlspecialchars($athlete['nom_athlete'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label='Prénom:'><?= htmlspecialchars($athlete['prenom_athlete'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label='Pays:'><?= htmlspecialchars($athlete['nom_pays'], ENT_QUOTES, 'UTF-8') ?></td> <!-- Afficher le nom du pays -->
            <td data-label='Genre:'><?= htmlspecialchars($athlete['nom_genre'], ENT_QUOTES, 'UTF-8') ?></td> <!-- Afficher le genre -->
            <td>
                <form method="GET" action="modifier-athlete.php" style="display:inline;">
                    <input type="hidden" name="id_athlete" value="<?= htmlspecialchars($athlete['id_athlete'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Modifier</button>
                </form>
            </td>
            <td>
                <form method="GET" action="supprimer-athlete.php" style="display:inline;">
                    <input type="hidden" name="id_athlete" value="<?= htmlspecialchars($athlete['id_athlete'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet athlète ?');">Supprimer</button>
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
