<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: modification-gestion-lieu.php");
    exit();
}

// Filtrer l'ID du lieu pour valider qu'il s'agit d'un entier
$id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du lieu est un entier valide
if (!$id_lieu && $id_lieu !== 0) {
    $_SESSION['error'] = "ID du lieu invalide.";
    header("Location: modification-gestion-lieu.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations du lieu pour affichage dans le formulaire
try {
    $queryLieu = "SELECT nom_lieu FROM lieu WHERE id_lieu = :idLieu";
    $statementLieu = $pdo->prepare($queryLieu);
    $statementLieu->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
    $statementLieu->execute();

    if ($statementLieu->rowCount() > 0) {
        $lieu = $statementLieu->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Lieu non trouvé.";
        header("Location: modification-gestion-lieu.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-lieu.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du lieu est vide
    if (empty($nomLieu)) {
        $_SESSION['error'] = "Le nom du lieu ne peut pas être vide.";
        header("Location: modifier-lieu.php?id_lieu=$id_lieu");
        exit();
    }

    try {
        // Vérifiez si le lieu existe déjà (exclure le lieu actuel de la vérification)
        $queryCheck = "SELECT id_lieu FROM lieu WHERE nom_lieu = :nomLieu AND id_lieu <> :idLieu";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le lieu existe déjà.";
            header("Location: modifier-lieu.php?id_lieu=$id_lieu");
            exit();
        }

        // Requête pour mettre à jour le lieu
        $query = "UPDATE lieu SET nom_lieu = :nomLieu WHERE id_lieu = :idLieu";
        $statement = $pdo->prepare($query);
        $statement->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statement->bindParam(":idLieu", $id_lieu, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le lieu a été modifié avec succès.";
            header("Location: modification-gestion-lieu.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du lieu.";
            header("Location: modifier-lieu.php?id_lieu=$id_lieu");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-lieu.php?id_lieu=$id_lieu");
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
    <title>Modifier un Lieu - Accueil Administrateur</title>
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

    <!-- Section principale pour modifier un lieu -->
    <main>
      <center> <h1>Modifier un Lieu</h1></center> 
        
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
        <form action="modifier-lieu.php?id_lieu=<?php echo $id_lieu; ?>" method="post"
              onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu?')">
            <label for="nomLieu">Nom du Lieu :</label>
            <input type="text" name="nomLieu" id="nomLieu"
                   value="<?php echo htmlspecialchars($lieu['nom_lieu']); ?>" required>
            <input type="submit" value="Modifier le Lieu">
        </form>
        </section>
       <br><br>
         <center> <a class="link-home" href="modification-gestion-lieu.php">Retour à la gestion des lieux</a></center>  
        
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
