<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du pays est vide
    if (empty($nomPays)) {
        $_SESSION['error'] = "Le nom du pays ne peut pas être vide.";
        header("Location: ajouter-pays.php");
        exit();
    }

    try {
        // Vérifiez si le pays existe déjà
        $queryCheck = "SELECT id_pays FROM pays WHERE nom_pays = :nomPays";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le pays existe déjà.";
            header("Location: ajouter-pays.php");
            exit();
        } else {
            // Requête pour ajouter un pays
            $query = "INSERT INTO pays (nom_pays) VALUES (:nomPays)";
            $statement = $pdo->prepare($query);
            $statement->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "Le pays a été ajouté avec succès.";
                header("Location: modification-gestion-pays.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du pays.";
                header("Location: ajouter-pays.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-pays.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-admin/modifier-sports.css"> <!-- Adapter le fichier CSS -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" /> 
    <title>Ajouter un Pays - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter un Pays</h1>
        
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
        
        <!-- Formulaire pour ajouter un pays -->
        <section class="formulaire">
            <form action="ajouter-pays.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce pays ?')">
                <label for="nomPays">Nom du Pays :</label>
                <input type="text" name="nomPays" id="nomPays" required>
                <input type="submit" value="Ajouter le Pays">
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
