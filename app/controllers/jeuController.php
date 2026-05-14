<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
// dirige vers la page de détail d'un jeu, accessible à tous les utilisateurs
function jeu() {
    $conn = connect();
    // prefer slug if provided, else id
    $slug = isset($_GET['slug']) ? urldecode($_GET['slug']) : null;
    if ($slug) {
        $jeu = getJeuBySlug($conn, $slug);
    } else {
        $jeu = getJeuById($conn, isset($_GET['id']) ? (int)$_GET['id'] : null);
    }
    
    if (!$jeu) {
        http_response_code(404);
        include 'app/views/404.php';
        return;
    }
    // extraire catégories et avis pour la vue
    $categories = $jeu['categories'] ?? [];
    $avis = $jeu['avis'] ?? [];
    include 'app/views/jeu_detail.php';
}
// dirige vers la page d'ajout de jeu, accessible uniquement aux utilisateurs connectés ou admin
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