<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du lieu est vide
    if (empty($nomLieu)) {
        $_SESSION['error'] = "Le nom du lieu ne peut pas être vide.";
        header("Location: ajouter-lieu.php");
        exit();
    }

    try {
        // Vérifiez si le lieu existe déjà
        $queryCheck = "SELECT id_lieu FROM lieu WHERE nom_lieu = :nomLieu";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le lieu existe déjà.";
            header("Location: ajouter-lieu.php");
            exit();
        } else {
            // Requête pour ajouter un lieu
            $query = "INSERT INTO lieu (nom_lieu) VALUES (:nomLieu)";
            $statement = $pdo->prepare($query);
            $statement->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "Le lieu a été ajouté avec succès.";
                header("Location: modification-gestion-lieu.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du lieu.";
                header("Location: ajouter-lieu.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-lieu.php");
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
    <title>Ajouter un Lieu - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter un Lieu</h1>
        
        <!-- Affichage des messages d'erreur ou de succès -->
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>
        <section class="formulaire">
            <form action="ajouter-lieu.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce lieu ?')">
                <label for="nomLieu">Nom du Lieu :</label>
                <input type="text" name="nomLieu" id="nomLieu" required>
                <input type="submit" value="Ajouter le Lieu">
            </form>
        </section>
        <br><br>
        <center><a class="link-home" href="modification-gestion-lieu.php">Retour à la gestion des lieux</a></center>
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
