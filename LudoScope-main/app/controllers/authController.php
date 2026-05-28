<?php
/**
 * Contrôleur : authentification
 * Gère la connexion, la déconnexion, l'inscription
 * et la réinitialisation du mot de passe par question secrète.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

/**
 * Affiche le formulaire de connexion/inscription (onglets Bootstrap).
 * En POST : vérifie les identifiants et ouvre la session utilisateur.
 */
function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['mot_de_passe'] ?? '');

        $conn = connect();
        $user = getUserByEmail($conn, $email);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Authentification réussie : ouverture de session
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['id_role']        = $user['id_role'];
            $_SESSION['pseudo']         = $user['pseudo'];
            $_SESSION['success']        = "Bienvenue " . $user['pseudo'] . " !";

            // Mise à jour de la date de dernière connexion
            updateDerniereConnexion($conn, $user['id_utilisateur']);

            header('Location: index.php');
            exit();
        }

        // Authentification échouée : retour au formulaire avec message d'erreur
        $error     = "Email ou mot de passe incorrect.";
        $activeTab = 'login';
        include __DIR__ . '/../../app/views/login.php';

    } else {
        // Affichage initial : onglet actif selon le paramètre GET (login ou signin)
        $activeTab = $_GET['tab'] ?? 'login';
        include __DIR__ . '/../../app/views/login.php';
    }
}

/**
 * Détruit la session et redirige vers l'accueil.
 */
function logout(): void
{
    session_destroy();
    header('Location: index.php?logout=1');
    exit();
}

/**
 * Traite le formulaire d'inscription.
 * Valide les champs, vérifie l'unicité de l'email et du pseudo,
 * hache le mot de passe, puis crée le compte.
 */
function register(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et nettoyage des champs
        $firstname        = trim($_POST['firstname'] ?? '');
        $lastname         = trim($_POST['lastname'] ?? '');
        $pseudo           = trim($_POST['pseudo'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = trim($_POST['mot_de_passe'] ?? '');
        $confirmPassword  = trim($_POST['mot_de_passe_confirm'] ?? '');
        $question_secrete = trim($_POST['question_secrete'] ?? '');
        $reponse_secrete  = trim($_POST['reponse_secrete'] ?? '');
        $date_inscription = date('Y-m-d H:i:s');

        // Validation : champs obligatoires
        if (empty($firstname) || empty($lastname) || empty($pseudo) || empty($email) || empty($password)) {
            $error = "Tous les champs sont requis.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        // Validation : format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Le format de l'adresse email n'est pas valide.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        // Validation : force du mot de passe (min. 8 caractères, 1 majuscule, 1 chiffre)
        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        // Validation : confirmation du mot de passe
        if ($password !== $confirmPassword) {
            $error = "Les mots de passe ne correspondent pas.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        // Validation : question et réponse secrètes (utilisées pour la récupération de compte)
        if (empty($question_secrete) || empty($reponse_secrete)) {
            $error = "La question secrète et la réponse sont requises.";
            $activeTab = 'signin';
            include __DIR__ . '/../../app/views/login.php';
            return;
        }

        $conn = connect();

        // Vérification de l'unicité de l'email et du pseudo
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

        // Hachage du mot de passe avant stockage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (createUser($conn, $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription, $question_secrete, $reponse_secrete)) {
            $_SESSION['success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
            header('Location: index.php?url=login');
            exit();
        }

        $error = "Une erreur est survenue lors de l'inscription.";
        $activeTab = 'signin';
        include __DIR__ . '/../../app/views/login.php';

    } else {
        include __DIR__ . '/../../app/views/login.php';
    }
}

/**
 * Réinitialisation du mot de passe en 3 étapes via question secrète.
 *
 * Étape 1 : vérification de l'existence de l'email.
 * Étape 2 : vérification de la réponse secrète.
 * Étape 3 : enregistrement du nouveau mot de passe haché.
 */
function forgotPassword(): void
{
    $step    = 1;
    $error   = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $step  = (int)($_POST['step'] ?? 1);
        $email = trim($_POST['email'] ?? '');
        $conn  = connect();

        // Étape 1 : vérifier que le compte existe
        if ($step === 1) {
            $user = getUserByEmail($conn, $email);
            if (!$user) {
                $error = "Aucun compte associé à cet email.";
                $step  = 1;
            } else {
                $step     = 2;
                $question = $user['question_secrete'];
            }

        // Étape 2 : vérifier la réponse à la question secrète
        } elseif ($step === 2) {
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

        // Étape 3 : enregistrer le nouveau mot de passe
        } elseif ($step === 3) {
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
                // Hachage et mise à jour du mot de passe
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