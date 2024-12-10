<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include 'db.php';

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

// Affichage des messages d'erreur ou de succès
$message = "";

// Si une requête POST est détectée, traiter la modification d'une épreuve
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et sécuriser les données du formulaire
        $id_epreuve = htmlspecialchars($_POST['id_epreuve'], ENT_QUOTES, 'UTF-8');
        $nom_epreuve = htmlspecialchars($_POST['nom_epreuve'], ENT_QUOTES, 'UTF-8');
        $date_epreuve = htmlspecialchars($_POST['date_epreuve'], ENT_QUOTES, 'UTF-8');
        $heure_epreuve = htmlspecialchars($_POST['heure_epreuve'], ENT_QUOTES, 'UTF-8');
        $id_lieu = htmlspecialchars($_POST['id_lieu'], ENT_QUOTES, 'UTF-8');
        $id_sport = htmlspecialchars($_POST['id_sport'], ENT_QUOTES, 'UTF-8');

        // Vérification des ID de l'épreuve, lieu et sport (doivent être des entiers)
        if (!filter_var($id_epreuve, FILTER_VALIDATE_INT) || !filter_var($id_lieu, FILTER_VALIDATE_INT) || !filter_var($id_sport, FILTER_VALIDATE_INT)) {
            throw new Exception("ID invalide.");
        }

        // Vérifier si une épreuve avec ce nom, cette date et ces clés étrangères existe déjà
        $sql_verification = "SELECT COUNT(*) FROM epreuve WHERE nom_epreuve = :nom_epreuve AND date_epreuve = :date_epreuve AND id_epreuve != :id_epreuve";
        $stmt_verification = $pdo->prepare($sql_verification);
        $stmt_verification->bindParam(':nom_epreuve', $nom_epreuve, PDO::PARAM_STR);
        $stmt_verification->bindParam(':date_epreuve', $date_epreuve, PDO::PARAM_STR);
        $stmt_verification->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt_verification->execute();
        $doublon = $stmt_verification->fetchColumn();

        if ($doublon > 0) {
            throw new Exception("Une épreuve avec ce nom et cette date existe déjà.");
        }

        // Mise à jour de l'épreuve dans le calendrier
        $sql = "UPDATE epreuve SET nom_epreuve = :nom_epreuve, date_epreuve = :date_epreuve, heure_epreuve = :heure_epreuve, id_lieu = :id_lieu, id_sport = :id_sport WHERE id_epreuve = :id_epreuve";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt->bindParam(':nom_epreuve', $nom_epreuve, PDO::PARAM_STR);
        $stmt->bindParam(':date_epreuve', $date_epreuve, PDO::PARAM_STR);
        $stmt->bindParam(':heure_epreuve', $heure_epreuve, PDO::PARAM_STR);
        $stmt->bindParam(':id_lieu', $id_lieu, PDO::PARAM_INT);
        $stmt->bindParam(':id_sport', $id_sport, PDO::PARAM_INT);
        $stmt->execute();

        $message = "L'épreuve a été mise à jour avec succès.";
    } catch (Exception $e) {
        // Gérer les erreurs
        $message = "Erreur : " . $e->getMessage();
    }
}

// Suppression d'une épreuve
if (isset($_GET['delete'])) {
    try {
        $id_epreuve = htmlspecialchars($_GET['delete'], ENT_QUOTES, 'UTF-8');

        if (!filter_var($id_epreuve, FILTER_VALIDATE_INT)) {
            throw new Exception("ID de l'épreuve invalide.");
        }

        $sql = "DELETE FROM epreuve WHERE id_epreuve = :id_epreuve";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $stmt->execute();

        $message = "L'épreuve a été supprimée avec succès.";
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// Récupérer toutes les épreuves pour les afficher dans un tableau
$sql = "SELECT epreuve.*, lieu.nom_lieu, sport.nom_sport 
        FROM epreuve 
        JOIN lieu ON epreuve.id_lieu = lieu.id_lieu 
        JOIN sport ON epreuve.id_sport = sport.id_sport";
$stmt = $pdo->query($sql);
$epreuves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modification-gestion-epreuves.css">
    <title>Gestion du Calendrier des Épreuves</title>
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
    <h1 style="text-align:center;">Liste des Épreuves du Calendrier</h1>
    <br><br>
    <center><a href="ajouter-epreuve.php">Ajouter une Épreuve</a></center>

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if ($message): ?>
        <p style="text-align:center; color:green;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <!-- Tableau pour afficher les épreuves -->
    <section class="conteneur">
        <table>
            <thead>
                <tr>
                    <th>Épreuve</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Lieu</th>
                    <th>Sport</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($epreuves as $epreuve): ?>
        <tr>
            <td data-label='Épreuve:'><?= htmlspecialchars($epreuve['nom_epreuve'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label='Date:'><?= htmlspecialchars($epreuve['date_epreuve'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label='Heure:'><?= htmlspecialchars($epreuve['heure_epreuve'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label='Lieu:'><?= htmlspecialchars($epreuve['nom_lieu'], ENT_QUOTES, 'UTF-8') ?></td> <!-- Afficher le nom du lieu -->
            <td data-label='Sport:'><?= htmlspecialchars($epreuve['nom_sport'], ENT_QUOTES, 'UTF-8') ?></td> <!-- Afficher le nom du sport -->
            <td>
                <form method="GET" action="modifier-epreuve.php" style="display:inline;">
                    <input type="hidden" name="id_epreuve"   value="<?= htmlspecialchars($epreuve['id_epreuve'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Modifier</button>
                </form>
            </td>
            <td>
                <form method="GET" action="supprimer-epreuve.php" style="display:inline;">
                    <input type="hidden" name="id_epreuve" value="<?= htmlspecialchars($epreuve['id_epreuve'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette épreuve ?');">Supprimer</button>
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
