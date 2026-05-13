<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/avis.php';

function dashboard() {
    checkRole(3);
    
    $conn = connect();
    $stats = getStats($conn);
    $derniers_jeux = getDerniersJeux($conn);
    $derniers_avis = getDerniersAvis($conn);
    $demandes = getDemandesEnAttente($conn);
    
    include __DIR__ . '/../../app/views/back-office.php';
}

function adminUsers() {
    checkRole(3);
    
    $conn = connect();
    $users = getAllUsers($conn);
    
    include __DIR__ . '/../../app/views/admin/users.php';
}