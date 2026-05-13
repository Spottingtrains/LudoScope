<?php
// Chargement des variables d'environnement depuis le fichier .env
$envFile = __DIR__ . '/../.env';

session_start();
//TODO: DEV ONLY - à supprimer avant livraison
// $_SESSION['id_utilisateur'] = 1; // à supprimer - simule un utilisateur connecté (id_utilisateur = 1) pour tester l'accès au back-office
// $_SESSION['id_role'] = 3; // à supprimer - simule un utilisateur avec le rôle admin (id_role = 3) pour tester l'accès au back-office
// $_SESSION['pseudo'] = 'admin'; // à supprimer - simule un pseudo pour l'utilisateur connecté

// Vérification que le fichier .env a été chargé correctement
if (!is_readable($envFile)) {
    die("Erreur : fichier .env non trouvé");
}

$dotenv = parse_ini_file($envFile);

if ($dotenv === false) {
    die("Erreur : impossible de lire le fichier .env");
}
// rendre les variables d'environnement accessibles globalement
$GLOBALS['dotenv'] = $dotenv;
?>