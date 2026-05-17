<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';

function profile() {
    checkRole(2); // Seuls les utilisateurs avec un rôle d'au moins 2 peuvent accéder à cette page

    // Si le formulaire est soumis, déléguer au handler de mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        updateProfile();
        return;
    }

    // Récupérer les informations de l'utilisateur connecté
    $conn = connect();
    $user = getUserById($conn, $_SESSION['id_utilisateur']);
    $favoriteGames = getFavoriteGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedGames = getAddedGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedReviews = getAddedReviewsByUser($conn, $_SESSION['id_utilisateur']);

    include __DIR__ . '/../../app/views/profile.php';
}

function updateProfile() {
    checkRole(2); // Seuls les utilisateurs avec un rôle d'au moins 2 peuvent accéder à cette page

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = connect();
        $userId = $_SESSION['id_utilisateur'];
        $currentUser = getUserById($conn, $userId);

        $data = [
            'prenom' => trim(htmlspecialchars($_POST['prenom'] ?? '')),
            'nom' => trim(htmlspecialchars($_POST['nom'] ?? '')),
            'pseudo' => trim(htmlspecialchars($_POST['pseudo'] ?? '')),
            'email' => trim(htmlspecialchars($_POST['email'] ?? '')),
        ];

        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Validation basique
        if (empty($data['prenom']) || empty($data['nom']) || empty($data['pseudo']) || empty($data['email'])) {
            $_SESSION['error'] = "Tous les champs sont requis.";
            header('Location: index.php?url=profile');
            exit();
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Le format de l'adresse email n'est pas valide.";
            header('Location: index.php?url=profile');
            exit();
        }

        // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
        $existingEmail = getUserByEmail($conn, $data['email']); // permet à l'utilisateur de soumettre le formulaire sans changer son email
        if ($existingEmail && $existingEmail['id_utilisateur'] !== $userId) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
            header('Location: index.php?url=profile');
            exit();
        }

        // Vérifier que le pseudo n'est pas déjà utilisé par un autre utilisateur
        $existingUser = getUserByPseudo($conn, $data['pseudo']); // permet à l'utilisateur de soumettre le formulaire sans changer son pseudo
        if ($existingUser && $existingUser['id_utilisateur'] !== $userId) {
            $_SESSION['error'] = "Ce pseudo est déjà utilisé par un autre utilisateur.";
            header('Location: index.php?url=profile');
            exit();
}

        // Vérifier mot de passe si fourni
        if ($newPassword !== '') {
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                header('Location: index.php?url=profile');
                exit();
            }
            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
                header('Location: index.php?url=profile');
                exit();
            }
        }

        // Gestion de l'upload d'image de profil
        if (isset($_FILES['image_profil']) && $_FILES['image_profil']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image_profil'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
                header('Location: index.php?url=profile');
                exit();
            }
            // Validation type et taille
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) {
                $_SESSION['error'] = "Format d'image non autorisé (jpg, png, webp, gif).";
                header('Location: index.php?url=profile');
                exit();
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                $_SESSION['error'] = "Image trop volumineuse (max 2MB).";
                header('Location: index.php?url=profile');
                exit();
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $targetDir = __DIR__ . '/../../uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $targetPath = $targetDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $_SESSION['error'] = "Impossible d'enregistrer l'image.";
                header('Location: index.php?url=profile');
                exit();
            }

            // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
            if (!empty($currentUser['photo_profil'])) {
                $oldPath = __DIR__ . '/../../' . ltrim($currentUser['photo_profil'], '/');
                if (file_exists($oldPath) && basename($oldPath) !== 'default-profile.webp') {
                    @unlink($oldPath);
                }
            }

            // Stocker le chemin public (préfixé par /uploads)
            $imagePath = '/uploads/' . $filename;
            $imageUpdated = updateProfileImage($conn, $userId, $imagePath);
        } else {
            $imageUpdated = true;
        }

        // Mise à jour du profil
        $userUpdated = updateUser($conn, $userId, $data);
        $passwordUpdated = true;
        if ($newPassword !== '') {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $passwordUpdated = updatePassword($conn, $userId, $hashed);
        }

        if ($userUpdated && $passwordUpdated && $imageUpdated) {
            $_SESSION['pseudo'] = $data['pseudo'];
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du profil.";
        }
    }

    header('Location: index.php?url=profile');
    exit();
}