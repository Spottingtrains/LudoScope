<?php
/**
 * Contrôleur : page d'accueil
 * Accessible à tous les rôles (visiteur, utilisateur, admin).
 */

require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/stats.php';

/**
 * Affiche la page d'accueil avec le catalogue, les statistiques et les catégories.
 */
function home(): void
{
    $conn = connect();

    // Récupération des données pour la vue
    $jeux       = getAllJeux($conn);
    $bestJeux   = getBestJeux($conn);
    $stats      = getStats($conn);
    $categories = getAllCategories($conn);

    include 'app/views/home.php';
}