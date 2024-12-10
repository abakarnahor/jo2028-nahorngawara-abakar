<?php
// lister_epreuves.php
include 'db.php';  // Inclusion du fichier de connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-utilisateur/lister_epreuves.css"> <!-- Réutilisation du même fichier CSS -->
    <title>Calendrier des Épreuves</title>
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

    <!-- Section principale pour afficher les épreuves -->
    <h1 style="text-align:center;">Calendrier des Épreuves</h1>

    <!-- Tableau pour afficher la liste des épreuves -->
    <table>
        <thead>
            <tr>
                <th>Épreuve</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Lieu</th>
                <th>Sport</th>
            </tr>
        </thead>
        <tbody>
    <?php
    // Requête pour récupérer les épreuves, leurs lieux et sports associés depuis la base de données
    $sql = "SELECT e.nom_epreuve, e.date_epreuve, e.heure_epreuve, l.nom_lieu, s.nom_sport
            FROM epreuve e
            JOIN lieu l ON e.id_lieu = l.id_lieu
            JOIN sport s ON e.id_sport = s.id_sport";
    $stmt = $pdo->query($sql);

    // Boucle pour afficher chaque épreuve dans une ligne du tableau
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td data-label='Épreuve:'>" . $row['nom_epreuve'] . "</td>";
        echo "<td data-label='Date:'>" . $row['date_epreuve'] . "</td>";
        echo "<td data-label='Heure:'>" . $row['heure_epreuve'] . "</td>";
        echo "<td data-label='Lieu:'>" . $row['nom_lieu'] . "</td>";
        echo "<td data-label='Sport:'>" . $row['nom_sport'] . "</td>";
        echo "</tr>";
    }
    ?>
</tbody>
    </table>

    <section class="retour">
       <center> <a href="index.php">Retour à l'Accueil</a></center>
    </section>
    <center> <img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>

</body>
</html>
