<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si l'ID de l'athlète et de l'épreuve sont fournis dans l'URL
if (!isset($_GET['id_athlete']) || !isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve manquant.";
    header("Location: modification-gestion-resultats.php");
    exit();
}

// Filtrer les IDs pour s'assurer qu'ils sont des entiers
$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si les IDs sont valides
if (!$id_athlete && $id_athlete !== 0 || !$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve invalide.";
    header("Location: modification-gestion-resultats.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations du résultat pour affichage dans le formulaire
try {
    $queryResultat = "SELECT resultat FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
    $statementResultat = $pdo->prepare($queryResultat);
    $statementResultat->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
    $statementResultat->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
    $statementResultat->execute();

    if ($statementResultat->rowCount() > 0) {
        $resultat = $statementResultat->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: modification-gestion-resultats.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-resultats.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérifiez si le résultat est vide
    if (empty($resultat)) {
        $_SESSION['error'] = "Le résultat ne peut pas être vide.";
        header("Location: modifier-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Requête pour mettre à jour le résultat
        $queryUpdate = "UPDATE PARTICIPER SET resultat = :resultat WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
        $statementUpdate = $pdo->prepare($queryUpdate);
        $statementUpdate->bindParam(":resultat", $resultat, PDO::PARAM_STR);
        $statementUpdate->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
        $statementUpdate->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statementUpdate->execute()) {
            $_SESSION['success'] = "Le résultat a été modifié avec succès.";
            header("Location: modification-gestion-resultats.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat.";
            header("Location: modifier-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
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
    <title>Modifier un Résultat - Accueil Administrateur</title>
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

    <!-- Section principale pour modifier un résultat -->
    <main>
      <center><h1>Modifier un Résultat</h1></center> 
        
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
        
        <!-- Formulaire pour modifier le résultat -->
        <section class="formulaire">
        <form action="modifier-resultat.php?id_athlete=<?php echo $id_athlete; ?>&id_epreuve=<?php echo $id_epreuve; ?>" method="post"
              onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat?')">
            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat"
                   value="<?php echo htmlspecialchars($resultat['resultat']); ?>" required>
            <input type="submit" value="Modifier le Résultat">
        </form>
        </section>
        
        <br><br>
        <center><a class="link-home" href="modification-gestion-resultats.php">Retour à la gestion des résultats</a></center>  
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
