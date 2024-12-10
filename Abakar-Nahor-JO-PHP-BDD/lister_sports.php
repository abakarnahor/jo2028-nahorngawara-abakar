<?php
// lister_sports.php
include 'db.php';  // Inclusion du fichier de connexion à la BDD
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-utilisateur/lister_sports.css">
    <title>Liste des Sports</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" /> 

</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <a href="index.php">Accueil</a>
        <a href="lister_sports.php">Sport</a>
        <a href="lister_epreuves.php">Calendrier des Épreuves</a>
        <a href="lister_resultats.php">Résultats</a>
        <a href="admin.php">Accès Administrateur</a>
    </nav>

    <!-- Section principale pour afficher les sports -->
    <h1 style="text-align:center;">Liste des Sports</h1>

    <!-- Tableau pour afficher la liste des sports -->
    <table>
        <thead>
            <tr>
                <th>Sport</th>
                
            </tr>
        </thead>
        <tbody>
            <?php
            // Requête pour récupérer les sports depuis la BDD
            $sql = "SELECT * FROM sport";
            $stmt = $pdo->query($sql);

            // Boucle pour afficher chaque sport dans une ligne du tableau
            while ($row = $stmt->fetch()) {
                echo "<tr>";
          
                echo "<td>" . $row['nom_sport'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <section class="retour">
       <center> <a href="index.php">Retour a l'Accueil</a></center>
    </section>

   <center> <img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
</body>
</html>
