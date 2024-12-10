<?php
// lister_resultats.php
include 'db.php';  // Inclusion du fichier de connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/css-pour-utilisateur/admin.css"> 
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" /> 
    <title>Résultats des Épreuves</title>
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

    <!-- Section principale pour afficher les résultats -->
    <h1 style="text-align:center;">Connexion</h1>


    <section class="formulaire">
    <form action="verif.php" method="post">
    <label for="login">Login :</label>
    <input type="text" id="login" name="login" required>
    <br>
    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Se connecter">
    </form>
    </section>

   
    <center> <img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png" alt=""></center>
</body>
</html>