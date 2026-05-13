<?php
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';

function home() {
    // test de connexion à la base de données
    $conn = connect();
    
    $jeux = getAllJeux($conn);
    
    include 'app/views/home.php';
}