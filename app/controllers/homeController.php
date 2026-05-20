<?php
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/stats.php';

function home() {
    // test de connexion à la base de données
    $conn = connect();
    $jeux = getAllJeux($conn);
    $stats = getStats($conn);
    $categories = getAllCategories($conn);
    include 'app/views/home.php';
}