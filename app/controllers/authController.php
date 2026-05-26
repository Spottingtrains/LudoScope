<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['mot_de_passe'] ?? '');

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
            $activeTab = 'login';
            include __DIR__ . '/../../app/views/login.php';
        }
    } else {
        $activeTab = $_GET['tab'] ?? 'login';
        include __DIR__ . '/../../app/views/login.php';
    }
}

function logout() {
    session_destroy();
    header('Location: index.php?logout=1');
    exit();
}

function register() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstname        = trim($_POST['firstname'] ?? '');
        $lastname         = trim($_POST['lastname'] ?? '');
        $pseudo           = trim($_POST['pseudo'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = trim($_POST['mot_de_passe'] ?? '');
        $confirmPassword  = trim($_POST['mot_de_passe_confirm'] ?? '');
        $question_secrete = trim($_POST['question_secrete'] ?? '');
        $reponse_secrete  = trim($_POST['reponse_secrete'] ?? '');
        $date_inscription = date('Y-m-d H:i:s');

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
        if (empty($question_secrete) || empty($reponse_secrete)) {
            $error = "La question secrète et la réponse sont requises.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        $conn = connect();
        $existingUser   = getUserByEmail($conn, $email);
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

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (createUser($conn, $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription, $question_secrete, $reponse_secrete)) {
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

function forgotPassword() {
    $step  = 1;
    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $step  = (int)($_POST['step'] ?? 1);
        $email = trim($_POST['email'] ?? '');
        $conn  = connect();

        // Étape 1 : vérifier que l'email existe
        if ($step === 1) {
            $user = getUserByEmail($conn, $email);
            if (!$user) {
                $error = "Aucun compte associé à cet email.";
                $step  = 1;
            } else {
                $step    = 2;
                $question = $user['question_secrete'];
            }
        }

        // Étape 2 : vérifier la réponse secrète
        elseif ($step === 2) {
            $reponse  = trim($_POST['reponse'] ?? '');
            $question = trim($_POST['question'] ?? '');
            $user     = getUserByEmail($conn, $email);

            if (!$user) {
                $error = "Une erreur est survenue.";
                $step  = 1;
            } elseif ($question !== $user['question_secrete']) {
                $error    = "La question secrète est incorrecte.";
                $step     = 2;
                $question = $user['question_secrete'];
            } elseif ($reponse !== $user['reponse_secrete']) {
                $error    = "La réponse est incorrecte.";
                $step     = 2;
                $question = $user['question_secrete'];
            } else {
                $step = 3;
            }
        }

        // Étape 3 : enregistrer le nouveau mot de passe
        elseif ($step === 3) {
            $newPassword     = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $user            = getUserByEmail($conn, $email);

            if (!$user) {
                $error = "Une erreur est survenue.";
                $step  = 1;
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $newPassword)) {
                $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
                $step  = 3;
            } elseif ($newPassword !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas.";
                $step  = 3;
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                updatePassword($conn, $user['id_utilisateur'], $hashed);
                $_SESSION['success'] = "Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.";
                header('Location: index.php?url=login');
                exit();
            }
        }
    }

    include __DIR__ . '/../../app/views/forgot-password.php';
}