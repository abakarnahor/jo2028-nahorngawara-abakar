<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start(); 

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: modification-gestion-athletes.php");
    exit();
}

// Filtrer l'ID de l'athlète pour valider qu'il s'agit d'un entier
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlète est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: modification-gestion-athletes.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations de l'athlète pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT nom_athlete, prenom_athlete, id_pays, id_genre FROM athlete WHERE id_athlete = :idAthlete";
    $statementAthlete = $pdo->prepare($queryAthlete);
    $statementAthlete->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvé.";
        header("Location: modification-gestion-athletes.php");
        exit();
    }

    // Récupérer tous les pays
    $queryPays = "SELECT id_pays, nom_pays FROM pays";
    $statementPays = $pdo->prepare($queryPays);
    $statementPays->execute();
    $pays = $statementPays->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les genres
    $queryGenres = "SELECT id_genre, nom_genre FROM genre";
    $statementGenres = $pdo->prepare($queryGenres);
    $statementGenres->execute();
    $genres = $statementGenres->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_athlete = filter_input(INPUT_POST, 'nom_athlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenom_athlete = filter_input(INPUT_POST, 'prenom_athlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);

    // Vérifiez si tous les champs sont remplis
    if (empty($nom_athlete) || empty($prenom_athlete) || !$id_pays || !$id_genre) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: modifier-athlete.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête pour mettre à jour l'athlète
        $queryUpdate = "UPDATE athlete SET nom_athlete = :nom_athlete, prenom_athlete = :prenom_athlete, id_pays = :id_pays, id_genre = :id_genre WHERE id_athlete = :id_athlete";
        $statementUpdate = $pdo->prepare($queryUpdate);
        $statementUpdate->bindParam(":nom_athlete", $nom_athlete, PDO::PARAM_STR);
        $statementUpdate->bindParam(":prenom_athlete", $prenom_athlete, PDO::PARAM_STR);
        $statementUpdate->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);
        $statementUpdate->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);
        $statementUpdate->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statementUpdate->execute()) {
            $_SESSION['success'] = "L'athlète a été modifié avec succès.";
            header("Location: modification-gestion-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'athlète.";
            header("Location: modifier-athlete.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-athlete.php?id_athlete=$id_athlete");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modifier-epreuve.css">
    <title>Modifier un Athlète</title>
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

    <main>
        <center><h1>Modifier un Athlète</h1></center>

        <!-- Affichage des messages d'erreur ou de succès -->
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        ?>
        
        <section class="formulaire">
            <form action="modifier-athlete.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlète ?')">
                <label for="nom_athlete">Nom de l'Athlète :</label>
                <input type="text" name="nom_athlete" id="nom_athlete"
                       value="<?php echo htmlspecialchars($athlete['nom_athlete']); ?>" required>
                
                <label for="prenom_athlete">Prénom de l'Athlète :</label>
                <input type="text" name="prenom_athlete" id="prenom_athlete"
                       value="<?php echo htmlspecialchars($athlete['prenom_athlete']); ?>" required>

                <!-- Menu déroulant pour le pays -->
                <label for="id_pays">Pays :</label>
                <select name="id_pays" id="id_pays" required>
                    <option value="">Sélectionnez un pays</option>
                    <?php foreach ($pays as $p): ?>
                        <option value="<?php echo $p['id_pays']; ?>" <?php echo ($athlete['id_pays'] == $p['id_pays']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nom_pays']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Menu déroulant pour le genre -->
                <label for="id_genre">Genre :</label>
                <select name="id_genre" id="id_genre" required>
                    <option value="">Sélectionnez un genre</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g['id_genre']; ?>" <?php echo ($athlete['id_genre'] == $g['id_genre']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['nom_genre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              
                <input type="submit" value="Modifier l'Athlète">
            </form>
        </section>

        <br><br>
        <center><a class="link-home" href="modification-gestion-athletes.php">Retour à la gestion des athlètes</a></center>  
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
