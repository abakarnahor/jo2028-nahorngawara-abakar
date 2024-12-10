<?php
// Inclusion du fichier de connexion à la base de données
include 'db.php';

// Démarre la session
session_start();

// Récupérer tous les athlètes
$sqlAthletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM athlete";
$stmtAthletes = $pdo->query($sqlAthletes);
$athletes = $stmtAthletes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les épreuves
$sqlEpreuves = "SELECT id_epreuve, nom_epreuve FROM epreuve"; // Assurez-vous que la table epreuve existe
$stmtEpreuves = $pdo->query($sqlEpreuves);
$epreuves = $stmtEpreuves->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les pays pour le menu déroulant
$sqlPays = "SELECT id_pays, nom_pays FROM pays";
$stmtPays = $pdo->query($sqlPays);
$pays = $stmtPays->fetchAll(PDO::FETCH_ASSOC);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);
    $id_athlete = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $id_epreuve = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);

    // Vérifiez si les champs requis sont remplis
    if (empty($resultat) || !$id_athlete || !$id_epreuve) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: ajouter-resultat.php");
        exit();
    }

    try {
        // Vérifiez si l'entrée existe déjà
        $checkQuery = "SELECT COUNT(*) FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
        $checkStmt->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['error'] = "Cet athlète a déjà participé à cette épreuve.";
            header("Location: ajouter-resultat.php");
            exit();
        }

        // Requête pour ajouter le résultat
        $query = "INSERT INTO PARTICIPER (id_athlete, resultat, id_epreuve) VALUES (:id_athlete, :resultat, :id_epreuve)";
        $statement = $pdo->prepare($query);
        $statement->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);
        $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été ajouté avec succès.";
            header("Location: modification-gestion-resultats.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: ajouter-resultat.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: ajouter-resultat.php");
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
    <title>Ajouter un Résultat - Accueil Administrateur</title>
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
        <h1 style="text-align:center;">Ajouter un Résultat</h1>
        
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
            <form action="ajouter-resultat.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat ?')">
                
                <label for="resultat">Résultat :</label>
                <input type="text" name="resultat" id="resultat" required>

                <label for="id_athlete">Athlète :</label>
                <select name="id_athlete" id="id_athlete" required>
                    <option value="" disabled selected>Sélectionnez un athlète</option>
                    <?php foreach ($athletes as $athlete): ?>
                        <option value="<?= htmlspecialchars($athlete['id_athlete']) ?>">
                            <?= htmlspecialchars($athlete['nom_athlete'] . ' ' . $athlete['prenom_athlete']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_epreuve">Épreuve :</label>
                <select name="id_epreuve" id="id_epreuve" required>
                    <option value="" disabled selected>Sélectionnez une épreuve</option>
                    <?php foreach ($epreuves as $epreuve): ?>
                        <option value="<?= htmlspecialchars($epreuve['id_epreuve']) ?>">
                            <?= htmlspecialchars($epreuve['nom_epreuve']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" value="Ajouter le Résultat">
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
