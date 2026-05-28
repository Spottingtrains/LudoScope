<?php
// Chemin vers le fichier .env situé à la racine du projet
$envFile = __DIR__ . '/../.env';

// Vérifie que le fichier .env existe et est lisible
if (!is_readable($envFile)) {
    die("Erreur : fichier .env introuvable. Copiez .env.example en .env et renseignez vos identifiants.");
}

// Parse le fichier .env (format clé=valeur) et retourne un tableau associatif
$dotenv = parse_ini_file($envFile);

if ($dotenv === false) {
    die("Erreur : impossible de parser le fichier .env.");
}

// Stocke les variables d'environnement dans $GLOBALS pour y accéder depuis n'importe quel fichier
$GLOBALS['dotenv'] = $dotenv;
?>