<?php
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/stats.php';

function home() {
    $conn = connect();
    $jeux = getAllJeux($conn);
    $bestJeux = getBestJeux($conn);   // ← ajouter cette ligne
    $stats = getStats($conn);
    $categories = getAllCategories($conn);
    include 'app/views/home.php';
}