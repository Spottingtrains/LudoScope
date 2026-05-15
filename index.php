<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/database.php';

$url = $_GET['url'] ?? 'home';
// Routing simple basé sur l'URL
switch($url) {
    case 'home':
        require 'app/controllers/homeController.php';
        home();
        break;
    case 'jeu':
        require 'app/controllers/jeuController.php';
        jeu();
        break;
    case 'jeu/add':
        require 'app/controllers/jeuController.php';
        jeu_add();
        break;
    case 'login':
        require 'app/controllers/authController.php';
        login();
        break;
    case 'register':
        require 'app/controllers/authController.php';
        register();
        break;
    case 'logout':
        require 'app/controllers/authController.php';
        logout();
        break;
    case 'profile':
        require 'app/controllers/userController.php';
        profile();
        break;
    case 'back-office':
        require 'app/controllers/adminController.php';
        dashboard();
        break;
    case 'admin/users':
        require 'app/controllers/adminController.php';
        adminUsers();
        break;
    case 'forgot-password':
        require 'app/controllers/passwordController.php';
        forgotPassword();
        break;
    case 'reset-password':
        require 'app/controllers/passwordController.php';
        resetPassword();
        break;
        default:
        http_response_code(404);
        include 'app/views/404.php';
        break;
}