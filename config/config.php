<?php
// Chargement des variables d'environnement depuis le fichier .env
$dotenv = parse_ini_file(__DIR__ . '/.env');
// Vérification que le fichier .env a été chargé correctement
if (!$dotenv) {
    die("Erreur : fichier .env non trouvé");
}
// rendre les variables d'environnement accessibles globalement
$GLOBALS['dotenv'] = $dotenv;
?>