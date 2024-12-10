<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['id_pays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: modification-gestion-pays.php");
    exit();
}

// Filtrer l'ID du pays pour valider qu'il s'agit d'un entier
$id_pays = filter_input(INPUT_GET, 'id_pays', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du pays est un entier valide
if (!$id_pays && $id_pays !== 0) {
    $_SESSION['error'] = "ID du pays invalide.";
    header("Location: modification-gestion-pays.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations du pays pour affichage dans le formulaire
try {
    $queryPays = "SELECT nom_pays FROM pays WHERE id_pays = :idPays";
    $statementPays = $pdo->prepare($queryPays);
    $statementPays->bindParam(":idPays", $id_pays, PDO::PARAM_INT);
    $statementPays->execute();

    if ($statementPays->rowCount() > 0) {
        $pays = $statementPays->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Pays non trouvé.";
        header("Location: modification-gestion-pays.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-pays.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du pays est vide
    if (empty($nomPays)) {
        $_SESSION['error'] = "Le nom du pays ne peut pas être vide.";
        header("Location: modifier-pays.php?id_pays=$id_pays");
        exit();
    }

    try {
        // Vérifiez si le pays existe déjà
        $queryCheck = "SELECT id_pays FROM pays WHERE nom_pays = :nomPays AND id_pays <> :idPays";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statementCheck->bindParam(":idPays", $id_pays, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le pays existe déjà.";
            header("Location: modifier-pays.php?id_pays=$id_pays");
            exit();
        }

        // Requête pour mettre à jour le pays
        $query = "UPDATE pays SET nom_pays = :nomPays WHERE id_pays = :idPays";
        $statement = $pdo->prepare($query);
        $statement->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statement->bindParam(":idPays", $id_pays, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le pays a été modifié avec succès.";
            header("Location: modification-gestion-pays.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du pays.";
            header("Location: modifier-pays.php?id_pays=$id_pays");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-pays.php?id_pays=$id_pays");
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
    <title>Modifier un Pays - Accueil Administrateur</title>
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

    <!-- Section principale pour modifier un pays -->
    <main>
      <center><h1>Modifier un Pays</h1></center> 
        
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
        
        <!-- Formulaire pour modifier le pays -->
        <section class="formulaire">
        <form action="modifier-pays.php?id_pays=<?php echo $id_pays; ?>" method="post"
              onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce pays?')">
            <label for="nomPays">Nom du Pays :</label>
            <input type="text" name="nomPays" id="nomPays"
                   value="<?php echo htmlspecialchars($pays['nom_pays']); ?>" required>
            <input type="submit" value="Modifier le Pays">
        </form>
        </section>
        
        <br><br>
        <center><a class="link-home" href="modification-gestion-pays.php">Retour à la gestion des pays</a></center>  
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
