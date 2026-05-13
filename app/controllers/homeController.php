<?php
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';

function home() {
    $conn = connect();
    $jeux = getAllJeux($conn);
    
    include 'app/views/home.php';
}