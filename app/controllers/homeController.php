<?php

// inclusion du modèle de la page d'accueil
require_once __DIR__ . '/../models/jeu.php';


// récupération des données nécessaires à l'affichage de la page d'accueil

// try and catch pour gérer les erreurs de connexion à la base de données
// try {
//     $jeux = getJeux();
// } catch (Exception $e) {
//     // gérer l'erreur de connexion à la base de données
//     error_log("Erreur de connexion à la base de données : " . $e->getMessage());
//     $jeux = [];
// }

// envoie des données à la vue
require_once __DIR__ . '/../views/home.php';