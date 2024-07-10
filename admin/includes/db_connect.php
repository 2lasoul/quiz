<?php
// Configuration de la base de données
define('DB_HOST', 'localhost'); // ou l'adresse de votre serveur MySQL
define('DB_NAME', 'quiz_management'); // le nom de votre base de données
define('DB_USER', 'root'); // votre nom d'utilisateur MySQL
define('DB_PASSWORD', ''); // votre mot de passe MySQL

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    // Définir le mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Définir l'encodage sur UTF-8
    $pdo->exec("set names utf8");
} catch (PDOException $e) {
    // Afficher un message d'erreur si la connexion échoue
    echo 'Erreur de connexion : ' . $e->getMessage();
    die();
}
?>
