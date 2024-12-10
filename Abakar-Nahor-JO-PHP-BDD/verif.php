<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    $conn = new PDO('mysql:host=localhost;dbname=jo2028_abakar_nahor', 'root', 'root');
    // Définir le mode d'erreur PDO pour générer des exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données soumises par le formulaire
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Requête préparée pour récupérer l'utilisateur avec ce login
    $stmt = $conn->prepare("SELECT password FROM UTILISATEUR WHERE login = :login");
    $stmt->bindParam(':login', $login);
    $stmt->execute();
    
    // Récupérer le mot de passe haché
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur existe et que le mot de passe correspond
    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie, on enregistre le login de l'utilisateur dans la session
        $_SESSION['username'] = $login;
        header("Location: Accueil-admin.php"); // Rediriger vers la page administrateur
        exit();
    } else {
        // Erreur de connexion, affichage d'un message d'erreur
        echo "<h3>Erreur : Nom d'utilisateur ou mot de passe incorrect.</h3>";
        echo "<a href='admin.php'>Réessayer</a>";
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la page de connexion
    header("Location: admin.php");
    exit();
}
?>
