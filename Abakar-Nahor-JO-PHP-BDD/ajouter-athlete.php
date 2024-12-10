<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Récupérer tous les pays et genres pour les menus déroulants
$sqlPays = "SELECT id_pays, nom_pays FROM pays";
$stmtPays = $pdo->query($sqlPays);
$pays = $stmtPays->fetchAll(PDO::FETCH_ASSOC);

$sqlGenres = "SELECT id_genre, nom_genre FROM genre";
$stmtGenres = $pdo->query($sqlGenres);
$genres = $stmtGenres->fetchAll(PDO::FETCH_ASSOC);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_athlete = filter_input(INPUT_POST, 'nom_athlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenom_athlete = filter_input(INPUT_POST, 'prenom_athlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);

    // Vérifiez si les champs requis sont remplis
    if (empty($nom_athlete) || empty($prenom_athlete) || !$id_pays || !$id_genre) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: ajouter-athlete.php");
        exit();
    }

    try {
        // Vérifiez si un athlète avec ce nom et ce prénom existe déjà
        $queryCheck = "SELECT id_athlete FROM athlete WHERE nom_athlete = :nom_athlete AND prenom_athlete = :prenom_athlete";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nom_athlete", $nom_athlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenom_athlete", $prenom_athlete, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Un athlète avec ce nom et ce prénom existe déjà.";
            header("Location: ajouter-athlete.php");
            exit();
        } else {
            // Requête pour ajouter un nouvel athlète avec le pays et le genre
            $query = "INSERT INTO athlete (nom_athlete, prenom_athlete, id_pays, id_genre) 
                      VALUES (:nom_athlete, :prenom_athlete, :id_pays, :id_genre)";
            $statement = $pdo->prepare($query);
            $statement->bindParam(":nom_athlete", $nom_athlete, PDO::PARAM_STR);
            $statement->bindParam(":prenom_athlete", $prenom_athlete, PDO::PARAM_STR);
            $statement->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);
            $statement->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'athlète a été ajouté avec succès.";
                header("Location: modification-gestion-athletes.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète.";
                header("Location: ajouter-athlete.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-athlete.php");
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
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />
    <title>Ajouter un Athlète - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter un Athlète</h1>
        
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
            <form action="ajouter-athlete.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlète ?')">
                <label for="nom_athlete">Nom de l'Athlète :</label>
                <input type="text" name="nom_athlete" id="nom_athlete" required>
                
                <label for="prenom_athlete">Prénom de l'Athlète :</label>
                <input type="text" name="prenom_athlete" id="prenom_athlete" required>

                <label for="id_pays">Pays :</label>
                <select name="id_pays" id="id_pays" required>
                    <option value="" disabled selected>Sélectionnez un pays</option>
                    <?php foreach ($pays as $p): ?>
                        <option value="<?= htmlspecialchars($p['id_pays']) ?>">
                            <?= htmlspecialchars($p['nom_pays']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_genre">Genre :</label>
                <select name="id_genre" id="id_genre" required>
                    <option value="" disabled selected>Sélectionnez un genre</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= htmlspecialchars($g['id_genre']) ?>">
                            <?= htmlspecialchars($g['nom_genre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" value="Ajouter l'Athlète">
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
