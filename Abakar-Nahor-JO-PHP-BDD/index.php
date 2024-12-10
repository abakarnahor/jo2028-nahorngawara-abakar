<?php
// index.php
session_start();
include 'db.php';  // Inclusion du fichier de connexion à la BDD
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - JO 2028</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" /> 
    <link rel="stylesheet" href="css/css-pour-utilisateur/style.css">
</head>
<body>
    

    <nav>
         <a href="index.php">Accueil</a>
        <a href="lister_sports.php">Sport</a>
        <a href="lister_epreuves.php">Calendrier des Épreuves</a>
        <a href="lister_resultats.php">Résultats</a>
        <a href="admin.php">Accès Administrateur</a>
    </nav>


    <section class="pc-tablette">
        <div class="ligne1">  
      <span><a href="lister_sports.php">Sport</a></span> 
        <a href="lister_epreuves.php">Calendrier des épreuves</a>
       </div>
      
        <div class="ligne2"><span><a href="lister_resultats.php">Résultats</a></span></div>
    </section>

    <section class="telephone-uniquement">
    <div class="ligne1">  
      <a href="lister_sports.php">Sport</a>
        <a href="lister_epreuves.php">Calendrier des Épreuves</a>
        <a href="lister_resultats.php">Résultats</a></div>
    </section>

    <footer>   
        <center> <img src="images/Logo_JO_d'été_-_Los_Angeles_2028.svg.png"  alt=""></center> 
    </footer>
</body>
</html>
