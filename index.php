<?php
/**
 * Point d'entrée unique de l'application (Front Controller).
 *
 * Lit le paramètre `?url=` dans l'URL et dispatch vers la fonction
 * de contrôleur correspondante. Si aucune route ne correspond,
 * retourne une page 404.
 *
 * Exemple : index.php?url=login → login() dans authController.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/database.php';

$url = $_GET['url'] ?? 'home';

switch ($url) {

    // -------------------------------------------------------------------------
    // Page d'accueil
    // -------------------------------------------------------------------------
    case 'home':
        require 'app/controllers/homeController.php';
        home();
        break;

    // -------------------------------------------------------------------------
    // Jeux : catalogue, détail, recherche AJAX, ajout, demandes de modification
    // -------------------------------------------------------------------------
    case 'jeu':
        require 'app/controllers/jeuController.php';
        jeu();
        break;

    case 'jeu/search':
        require 'app/controllers/jeuController.php';
        jeuSearch();
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

    // -------------------------------------------------------------------------
    // Authentification : connexion, inscription, déconnexion, mot de passe oublié
    // -------------------------------------------------------------------------
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

    case 'forgot-password':
        require 'app/controllers/authController.php';
        forgotPassword();
        break;

    // -------------------------------------------------------------------------
    // Profil utilisateur
    // -------------------------------------------------------------------------
    case 'profile':
        require 'app/controllers/userController.php';
        profile();
        break;

    // -------------------------------------------------------------------------
    // Pages statiques (vues incluses directement, sans contrôleur)
    // -------------------------------------------------------------------------
    case 'legal':
        include 'app/views/legal.php';
        break;

    case 'privacy':
        include 'app/views/privacy.php';
        break;

    case 'terms':
        include 'app/views/terms.php';
        break;

    // -------------------------------------------------------------------------
    // Back-office administrateur
    // -------------------------------------------------------------------------
    case 'back-office':
        require 'app/controllers/adminController.php';
        dashboard();
        break;

    // Gestion des utilisateurs
    case 'admin_users':
        require 'app/controllers/adminController.php';
        adminUsers();
        break;

    case 'admin/users/create':
        require 'app/controllers/adminController.php';
        adminUserCreate();
        break;

    case 'admin/users/edit':
        require 'app/controllers/adminController.php';
        adminUserEdit();
        break;

    case 'admin/users/delete':
        require 'app/controllers/adminController.php';
        adminUserDelete();
        break;

    // Gestion du contenu (jeux et avis)
    case 'admin_content':
        require 'app/controllers/adminController.php';
        adminContent();
        break;

    case 'admin_content/edit_game':
        require 'app/controllers/adminController.php';
        adminEditGame();
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

    // -------------------------------------------------------------------------
    // Fallback : page 404
    // -------------------------------------------------------------------------
    default:
        http_response_code(404);
        include 'app/views/404.php';
        break;
}