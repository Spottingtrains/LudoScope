<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/models/token.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../../vendor/autoload.php';

function forgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';

        $conn = connect();
        $user = getUserByEmail($conn, $email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
            saveToken($conn, $user['id_utilisateur'], $token, $expiration);

            $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
            $resetLink = $baseUrl . '/index.php?url=reset-password&token=' . $token;

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
                // on n'affiche pas l'erreur pour ne pas révéler si l'email existe
            }
        }

        // Message générique dans tous les cas (sécurité)
        $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
        include __DIR__ . '/../../app/views/forgot-password.php';
    } else {
        include __DIR__ . '/../../app/views/forgot-password.php';
    }
}

function resetPassword() {
    $token = $_GET['token'] ?? '';

    $conn = connect();
    $tokenData = getTokenData($conn, $token);

    if (!$tokenData || $tokenData['date_expiration'] < date('Y-m-d H:i:s')) {
        $error = "Ce lien est invalide ou a expiré.";
        include __DIR__ . '/../../app/views/reset-password.php';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['mot_de_passe'] ?? '';
        $confirm  = $_POST['mot_de_passe_confirm'] ?? '';

        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
            $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.";
            include __DIR__ . '/../../app/views/reset-password.php';
            return;
        }

        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
            include __DIR__ . '/../../app/views/reset-password.php';
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        updatePassword($conn, $tokenData['id_utilisateur'], $hashedPassword);

        deleteToken($conn, $token);

        $_SESSION['success'] = "Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.";
        header('Location: index.php?url=login');
        exit();
    }

    include __DIR__ . '/../../app/views/reset-password.php';
}