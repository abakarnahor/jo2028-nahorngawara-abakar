<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Récupérer tous les lieux et sports pour les menus déroulants
$sqlLieux = "SELECT id_lieu, nom_lieu FROM lieu";
$stmtLieux = $pdo->query($sqlLieux);
$lieux = $stmtLieux->fetchAll(PDO::FETCH_ASSOC);

$sqlSports = "SELECT id_sport, nom_sport FROM sport";
$stmtSports = $pdo->query($sqlSports);
$sports = $stmtSports->fetchAll(PDO::FETCH_ASSOC);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING);
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);

    // Vérifiez si les champs requis sont remplis
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || !$id_lieu || !$id_sport) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: ajouter-epreuve.php");
        exit();
    }

    try {
        // Vérifiez si une épreuve avec ce nom et cette date existe déjà
        $queryCheck = "SELECT id_epreuve FROM epreuve WHERE nom_epreuve = :nom_epreuve AND date_epreuve = :date_epreuve";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Une épreuve avec ce nom et cette date existe déjà.";
            header("Location: ajouter-epreuve.php");
            exit();
        } else {
            // Requête pour ajouter une nouvelle épreuve avec le lieu et le sport
            $query = "INSERT INTO epreuve (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport) 
                      VALUES (:nom_epreuve, :date_epreuve, :heure_epreuve, :id_lieu, :id_sport)";
            $statement = $pdo->prepare($query);
            $statement->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
            $statement->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
            $statement->bindParam(":heure_epreuve", $heure_epreuve, PDO::PARAM_STR);
            $statement->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);
            $statement->bindParam(":id_sport", $id_sport, PDO::PARAM_INT);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'épreuve a été ajoutée avec succès.";
                header("Location: modification-gestion-calendrier.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve.";
                header("Location: ajouter-epreuve.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-epreuve.php");
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
    <title>Ajouter une Épreuve - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter une Épreuve</h1>
        
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
            <form action="ajouter-epreuve.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve ?')">
                <label for="nom_epreuve">Nom de l'Épreuve :</label>
                <input type="text" name="nom_epreuve" id="nom_epreuve" required>
                
                <label for="date_epreuve">Date de l'Épreuve :</label>
                <input type="date" name="date_epreuve" id="date_epreuve" required>
                
                <label for="heure_epreuve">Heure de l'Épreuve :</label>
                <input type="time" name="heure_epreuve" id="heure_epreuve" required>

                <label for="id_lieu">Lieu :</label>
                <select name="id_lieu" id="id_lieu" required>
                    <option value="" disabled selected>Sélectionnez un lieu</option>
                    <?php foreach ($lieux as $lieu): ?>
                        <option value="<?= htmlspecialchars($lieu['id_lieu']) ?>">
                            <?= htmlspecialchars($lieu['nom_lieu']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_sport">Sport :</label>
                <select name="id_sport" id="id_sport" required>
                    <option value="" disabled selected>Sélectionnez un sport</option>
                    <?php foreach ($sports as $sport): ?>
                        <option value="<?= htmlspecialchars($sport['id_sport']) ?>">
                            <?= htmlspecialchars($sport['nom_sport']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" value="Ajouter l'Épreuve">
            </form>
        </section>
        
        <br><br>
        <center><a class="link-home" href="modification-gestion-calendrier.php">Retour à la gestion des épreuves</a></center>
    </main>

    <footer>
        <center><img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
    </footer>
</body>
</html>
