<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim(htmlspecialchars($_POST['email'] ?? ''));
        $password = trim(htmlspecialchars($_POST['mot_de_passe'] ?? ''));

        $conn = connect();
        $user = getUserByEmail($conn, $email);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['id_role'] = $user['id_role'];
            $_SESSION['pseudo'] = $user['pseudo'];
            $_SESSION['success'] = "Bienvenue " . $user['pseudo'] . " !";
            updateDerniereConnexion($conn, $user['id_utilisateur']);
            header('Location: index.php');
            exit();
        } else {
            // Authentification échouée
            $error = "Email ou mot de passe incorrect.";
            include __DIR__ . '/../../app/views/login.php';
        }
    } else {
        include __DIR__ . '/../../app/views/login.php';
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}

function register() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstname = trim(htmlspecialchars($_POST['firstname'] ?? ''));
        $lastname = trim(htmlspecialchars($_POST['lastname'] ?? ''));
        $pseudo = trim(htmlspecialchars($_POST['pseudo'] ?? ''));
        $email = trim(htmlspecialchars($_POST['email'] ?? ''));
        $password = trim(htmlspecialchars($_POST['mot_de_passe'] ?? ''));
        $confirmPassword = trim(htmlspecialchars($_POST['mot_de_passe_confirm'] ?? ''));
        $date_inscription = date('Y-m-d H:i:s');

        // Validation basique
        if (empty($firstname) || empty($lastname) || empty($pseudo) || empty($email) || empty($password)) {
            $error = "Tous les champs sont requis.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Le format de l'adresse email n'est pas valide.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }
        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }
        if ($password !== $confirmPassword) {
            $error = "Les mots de passe ne correspondent pas.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        $conn = connect();
        $existingUser = getUserByEmail($conn, $email);
        $existingPseudo = getUserByPseudo($conn, $pseudo);

        if ($existingUser) {
            $error = "Un utilisateur avec cet email existe déjà.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        if ($existingPseudo) {
            $error = "Un utilisateur avec ce pseudo existe déjà.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        // Hash du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (createUser($conn, $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription)) {
            $_SESSION['success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
            header('Location: index.php?url=login');
            exit();
        } else {
            $error = "Une erreur est survenue lors de l'inscription.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
        }
    } else {
        include __DIR__ . '/../../app/views/login.php';
    }
}