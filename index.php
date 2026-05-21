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
        jeuAdd();
        break;
    case 'jeu/edit':
        require 'app/controllers/jeuController.php';
        jeuEditRequest();
        break;
    case 'jeu/delete':
        require 'app/controllers/jeuController.php';
        jeuDeleteRequest();
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
    case 'admin_users':
        require 'app/controllers/adminController.php';
        adminUsers();
        break;
    case 'admin/users/create':
        require 'app/controllers/adminController.php';
        adminUserCreate();
        break;
    case 'admin_content':
        require 'app/controllers/adminController.php';
        adminContent();
        break;
    case 'admin_content_update_game':
        require 'app/controllers/adminController.php';
        adminUpdateGame();
        break;
    case 'admin_content_delete_game':
        require 'app/controllers/adminController.php';
        adminDeleteGame();
        break;
    case 'admin_content_update_avis':
        require 'app/controllers/adminController.php';
        adminUpdateAvis();
        break;
    case 'admin_content_delete_avis':
        require 'app/controllers/adminController.php';
        adminDeleteAvis();
        break;
    case 'admin/users/edit':
        require 'app/controllers/adminController.php';
        adminUserEdit();
        break;
    case 'admin/users/delete':
        require 'app/controllers/adminController.php';
        adminUserDelete();
        break;
    case 'forgot-password':
        require 'app/controllers/passwordController.php';
        forgotPassword();
        break;
    case 'reset-password':
        require 'app/controllers/passwordController.php';
        resetPassword();
        break;
    case 'jeu/search':
        require 'app/controllers/jeuController.php';
        jeuSearch();
        break;
        default:
        http_response_code(404);
        include 'app/views/404.php';
        break;
}