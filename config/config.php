<?php
// Chargement des variables d'environnement depuis le fichier .env
$envFile = __DIR__ . '/../.env';

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