<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomGenre = filter_input(INPUT_POST, 'nomGenre', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le nom du genre est vide
    if (empty($nomGenre)) {
        $_SESSION['error'] = "Le nom du genre ne peut pas être vide.";
        header("Location: ajouter-genre.php");
        exit();
    }

    try {
        // Vérifiez si le genre existe déjà
        $queryCheck = "SELECT id_genre FROM genre WHERE nom_genre = :nomGenre";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le genre existe déjà.";
            header("Location: ajouter-genre.php");
            exit();
        } else {
            // Requête pour ajouter un genre
            $query = "INSERT INTO genre (nom_genre) VALUES (:nomGenre)";
            $statement = $pdo->prepare($query);
            $statement->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "Le genre a été ajouté avec succès.";
                header("Location: modification-gestion-genres.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du genre.";
                header("Location: ajouter-genre.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-genre.php");
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
    <title>Ajouter un Genre - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter un Genre</h1>
        
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
        
        <!-- Formulaire pour ajouter un genre -->
        <section class="formulaire">
            <form action="ajouter-genre.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce genre ?')">
                <label for="nomGenre">Nom du Genre :</label>
                <input type="text" name="nomGenre" id="nomGenre" required>
                <input type="submit" value="Ajouter le Genre">
            </form>
        </section>
        <br><br>
        <center><a class="link-home" href="modification-gestion-genres.php">Retour à la gestion des genres</a></center>
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
