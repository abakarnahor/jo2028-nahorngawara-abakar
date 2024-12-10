<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start(); 

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: modification-gestion-calendrier.php");
    exit();
}

// Filtrer l'ID de l'épreuve pour valider qu'il s'agit d'un entier
$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'épreuve est un entier valide
if (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: modification-gestion-calendrier.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations de l'épreuve pour affichage dans le formulaire
try {
    $queryEpreuve = "SELECT nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport FROM epreuve WHERE id_epreuve = :idEpreuve";
    $statementEpreuve = $pdo->prepare($queryEpreuve);
    $statementEpreuve->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEpreuve->execute();

    if ($statementEpreuve->rowCount() > 0) {
        $epreuve = $statementEpreuve->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Épreuve non trouvée.";
        header("Location: modification-gestion-calendrier.php");
        exit();
    }

    // Récupérer tous les lieux
    $queryLieux = "SELECT id_lieu, nom_lieu FROM lieu";
    $statementLieux = $pdo->prepare($queryLieux);
    $statementLieux->execute();
    $lieux = $statementLieux->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les sports
    $querySports = "SELECT id_sport, nom_sport FROM sport";
    $statementSports = $pdo->prepare($querySports);
    $statementSports->execute();
    $sports = $statementSports->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modification-gestion-calendrier.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING);
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);

    // Vérifiez si tous les champs sont remplis
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || !$id_lieu || !$id_sport) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: modifier-epreuve.php?id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Vérifiez si une épreuve avec ce nom et cette date existe déjà
        $queryCheck = "SELECT id_epreuve FROM epreuve WHERE nom_epreuve = :nom_epreuve AND date_epreuve = :date_epreuve AND id_epreuve <> :id_epreuve";
        $statementCheck = $pdo->prepare($queryCheck);
        $statementCheck->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Une épreuve avec ce nom et cette date existe déjà.";
            header("Location: modifier-epreuve.php?id_epreuve=$id_epreuve");
            exit();
        }

        // Requête pour mettre à jour l'épreuve
        $queryUpdate = "UPDATE epreuve SET nom_epreuve = :nom_epreuve, date_epreuve = :date_epreuve, heure_epreuve = :heure_epreuve, id_lieu = :id_lieu, id_sport = :id_sport WHERE id_epreuve = :id_epreuve";
        $statementUpdate = $pdo->prepare($queryUpdate);
        $statementUpdate->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
        $statementUpdate->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
        $statementUpdate->bindParam(":heure_epreuve", $heure_epreuve, PDO::PARAM_STR);
        $statementUpdate->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);
        $statementUpdate->bindParam(":id_sport", $id_sport, PDO::PARAM_INT);
        $statementUpdate->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statementUpdate->execute()) {
            $_SESSION['success'] = "L'épreuve a été modifiée avec succès.";
            header("Location: modification-gestion-calendrier.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'épreuve.";
            header("Location: modifier-epreuve.php?id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modifier-epreuve.php?id_epreuve=$id_epreuve");
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
    <title>Modifier une Épreuve</title>
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
        <center><h1>Modifier une Épreuve</h1></center>

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
            <form action="modifier-epreuve.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cette épreuve ?')">
                <label for="nom_epreuve">Nom de l'Épreuve :</label>
                <input type="text" name="nom_epreuve" id="nom_epreuve"
                       value="<?php echo htmlspecialchars($epreuve['nom_epreuve']); ?>" required>
                
                <label for="date_epreuve">Date de l'Épreuve :</label>
                <input type="date" name="date_epreuve" id="date_epreuve"
                       value="<?php echo htmlspecialchars($epreuve['date_epreuve']); ?>" required>
                
                <label for="heure_epreuve">Heure de l'Épreuve :</label>
                <input type="time" name="heure_epreuve" id="heure_epreuve"
                       value="<?php echo htmlspecialchars($epreuve['heure_epreuve']); ?>" required>

                <!-- Menu déroulant pour le lieu -->
                <label for="id_lieu">Lieu :</label>
                <select name="id_lieu" id="id_lieu" required>
                    <option value="">Sélectionnez un lieu</option>
                    <?php foreach ($lieux as $lieu): ?>
                        <option value="<?php echo $lieu['id_lieu']; ?>" <?php echo ($epreuve['id_lieu'] == $lieu['id_lieu']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lieu['nom_lieu']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Menu déroulant pour le sport -->
                <label for="id_sport">Sport :</label>
                <select name="id_sport" id="id_sport" required>
                    <option value="">Sélectionnez un sport</option>
                    <?php foreach ($sports as $sport): ?>
                        <option value="<?php echo $sport['id_sport']; ?>" <?php echo ($epreuve['id_sport'] == $sport['id_sport']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sport['nom_sport']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              
                <input type="submit" value="Modifier l'Épreuve">
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
