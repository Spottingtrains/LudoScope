<?php
/**
 * Contrôleur : réinitialisation du mot de passe par email (token)
 * Utilisé comme alternative à la réinitialisation par question secrète.
 * Nécessite PHPMailer et un serveur SMTP configuré.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/models/token.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../../vendor/autoload.php';

/**
 * Envoie un lien de réinitialisation par email.
 * Le message affiché est identique que l'email existe ou non (sécurité : pas d'énumération).
 */
function forgotPassword(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';

        $conn = connect();
        $user = getUserByEmail($conn, $email);

        if ($user) {
            // Génération d'un token aléatoire valable 1 heure
            $token      = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
            saveToken($conn, $user['id_utilisateur'], $token, $expiration);

            // Construction du lien de réinitialisation
            $baseUrl   = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
            $resetLink = $baseUrl . '/index.php?url=reset-password&token=' . $token;

            // Envoi de l'email via PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host     = 'localhost';
                $mail->Port     = 1025;
                $mail->SMTPAuth = false;

                $mail->setFrom('noreply@ludotheque.fr', 'Ludothèque');
                $mail->addAddress($email);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body    = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $resetLink;

                $mail->send();
            } catch (Exception $e) {
                // Erreur silencieuse : ne pas révéler si l'email est connu du système
            }
        }

        // Message générique dans tous les cas (sécurité : pas d'énumération d'emails)
        $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
        include __DIR__ . '/../../app/views/forgot-password.php';

    } else {
        include __DIR__ . '/../../app/views/forgot-password.php';
    }
}

/**
 * Valide le token reçu par email et permet de définir un nouveau mot de passe.
 * Le token est supprimé après utilisation pour empêcher toute réutilisation.
 */
function resetPassword(): void
{
    $token = $_GET['token'] ?? '';

    $conn      = connect();
    $tokenData = getTokenData($conn, $token);

    // Vérification : token existant et non expiré
    if (!$tokenData || $tokenData['date_expiration'] < date('Y-m-d H:i:s')) {
        $error = "Ce lien est invalide ou a expiré.";
        include __DIR__ . '/../../app/views/reset-password.php';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['mot_de_passe'] ?? '';
        $confirm  = $_POST['mot_de_passe_confirm'] ?? '';

        // Validation : force du mot de passe
        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
            include __DIR__ . '/../../app/views/reset-password.php';
            return;
        }

        // Validation : confirmation
        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
            include __DIR__ . '/../../app/views/reset-password.php';
            return;
        }

        // Mise à jour du mot de passe et suppression du token (usage unique)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        updatePassword($conn, $tokenData['id_utilisateur'], $hashedPassword);
        deleteToken($conn, $token);

        $_SESSION['success'] = "Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.";
        header('Location: index.php?url=login');
        exit();
    }

    include __DIR__ . '/../../app/views/reset-password.php';
}