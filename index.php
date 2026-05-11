<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/database.php';

$url = $_GET['url'] ?? 'home';

switch($url) {
    case 'home':
        require 'app/controllers/homeController.php';
        break;
    case 'jeu':
        require 'app/controllers/jeuController.php';
        break;
    case 'signin':
    case 'login':
        require 'app/controllers/userController.php';
        break;
    case 'back-office':
        require 'app/controllers/adminController.php';
        break;
    default:
        http_response_code(404);
        include 'app/views/404.php';
        break;
}