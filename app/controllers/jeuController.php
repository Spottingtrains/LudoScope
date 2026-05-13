<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';

function jeu() {
    $conn = connect();
    $jeu = getJeuById($conn, $_GET['id'] ?? null);
    
    if (!$jeu) {
        http_response_code(404);
        include 'app/views/404.php';
        return;
    }
    
    include 'app/views/jeu.php';
}

function jeuAdd() {
    checkRole(2);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = connect();
        createJeu($conn, $_SESSION['id_utilisateur'], $_POST);
        header('Location: index.php?url=home');
        exit();
    }
    
    include 'app/views/jeu_add.php';
}