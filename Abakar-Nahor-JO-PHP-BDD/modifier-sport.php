<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();


// Vérifiez si l'ID du sport est fourni dans l'URL
if (!isset($_GET['id_sport'])) {
    $_SESSION['error'] = "ID du sport manquant.";
    header("Location: modification-gestion-sports.php");
    exit();
}

// Filtrer l'ID du sport pour valider qu'il s'agit d'un entier
$id_sport = filter_input(INPUT_GET, 'id_sport', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du sport est un entier valide
if (!$id_sport && $id_sport !== 0) {
    $_SESSION['error'] = "ID du sport invalide.";
    header("Location: modification-gestion-sports.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations du sport pour affichage dans le formulaire
try {
    $querySport = "SELECT nom_sport FROM sport WHERE id_sport = :idSport";
    $statementSport = $pdo->prepare($querySport);
    $statementSport->bindParam(":idSport", $id_sport, PDO::PARAM_INT);
    $statementSport->execute();

    if ($statementSport->rowCount() > 0) {
        $sport = $statementSport->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Sport non trouvé.";
        header("Location: modification-gestion-sports.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-sports.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomSport = filter_input(INPUT_POST, 'nomSport', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du sport est vide
    if (empty($nomSport)) {
        $_SESSION['error'] = "Le nom du sport ne peut pas être vide.";
        header("Location: modifier-sport.php?id_sport=$id_sport");
        exit();
    }

    try {
        // Vérifiez si le sport existe déjà
        $queryCheck = "SELECT id_sport FROM sport WHERE nom_sport = :nomSport AND id_sport <> :idSport";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomSport", $nomSport, PDO::PARAM_STR);
        $statementCheck->bindParam(":idSport", $id_sport, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le sport existe déjà.";
            header("Location: modifier-sport.php?id_sport=$id_sport");
            exit();
        }

        // Requête pour mettre à jour le sport
        $query = "UPDATE sport SET nom_sport = :nomSport WHERE id_sport = :idSport";
        $statement = $pdo->prepare($query);
        $statement->bindParam(":nomSport", $nomSport, PDO::PARAM_STR);
        $statement->bindParam(":idSport", $id_sport, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le sport a été modifié avec succès.";
            header("Location: modification-gestion-sports.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du sport.";
            header("Location: modifier-sport.php?id_sport=$id_sport");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-sport.php?id_sport=$id_sport");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modifier-sports.css"> 
    <title>Modifier un Sport - Accueil Administrateur</title>
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

    <!-- Section principale pour modifier un sport -->
    <main>
      <center> <h1>Modifier un Sport</h1></center> 
        
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
        <form action="modifier-sport.php?id_sport=<?php echo $id_sport; ?>" method="post"
              onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce sport?')">
            <label for="nomSport">Nom du Sport :</label>
            <input type="text" name="nomSport" id="nomSport"
                   value="<?php echo htmlspecialchars($sport['nom_sport']); ?>" required>
            <input type="submit" value="Modifier le Sport">
        </form>
        </section>
       <br><br>
         <center> <a class="link-home" href="modification-gestion-sports.php">Retour à la gestion des sports</a></center>  
        
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
