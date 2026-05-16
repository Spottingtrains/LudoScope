<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';

function profile() {
    checkRole(2); // Seuls les utilisateurs avec un rôle d'au moins 2 peuvent accéder à cette page

    // Récupérer les informations de l'utilisateur connecté
    $conn = connect();
    $user = getUserById($conn, $_SESSION['id_utilisateur']);
    $favoriteGames = getFavoriteGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedGames = getAddedGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedReviews = getAddedReviewsByUser($conn, $_SESSION['id_utilisateur']);

    include __DIR__ . '/../../app/views/profile.php';
}