<?php
/**
 * Contrôleur : espace utilisateur
 * Gère le profil (affichage, mise à jour), les avis et la suppression de compte.
 * Accessible uniquement aux utilisateurs connectés (rôle >= 2).
 */

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/models/avis.php';
require_once __DIR__ . '/../../app/models/jeu.php';

/**
 * Page de profil : affiche les informations, jeux ajoutés, favoris et avis de l'utilisateur.
 * Traite également les actions POST : modification/suppression d'avis, suppression de compte,
 * et délègue la mise à jour du profil à updateProfile().
 */
function profile(): void
{
    checkRole(2);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = connect();

        // Détection d'une requête AJAX (pour les réponses JSON)
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        // --- Action : modifier un avis ---
        if (isset($_POST['action']) && $_POST['action'] === 'edit_review') {
            $id          = (int)($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            $note        = isset($_POST['note']) ? (int)$_POST['note'] : 0;

            if (!$id || $commentaire === '' || $note < 1 || $note > 10) {
                $_SESSION['error'] = 'Données invalides pour la modification de l\'avis.';
                header('Location: index.php?url=profile');
                exit();
            }

            // Vérification que l'avis appartient bien à l'utilisateur connecté
            $avis = getAvisById($conn, $id);
            if (!$avis || $avis['id_utilisateur'] !== $_SESSION['id_utilisateur']) {
                $_SESSION['error'] = 'Action non autorisée.';
                header('Location: index.php?url=profile');
                exit();
            }

            if (updateAvis($conn, $id, $commentaire, $note)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Votre avis a été modifié.', 'note' => $note, 'commentaire' => $commentaire]);
                    exit();
                }
                $_SESSION['success'] = 'Votre avis a été modifié.';
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Impossible de modifier l\'avis.']);
                    exit();
                }
                $_SESSION['error'] = 'Impossible de modifier l\'avis.';
            }

            header('Location: index.php?url=profile');
            exit();
        }

        // --- Action : supprimer un avis ---
        if (isset($_POST['action']) && $_POST['action'] === 'delete_review') {
            $id      = (int)($_POST['id'] ?? 0);
            $confirm = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === '1';

            if (!$id || !$confirm) {
                $_SESSION['error'] = 'Suppression annulée ou données manquantes.';
                header('Location: index.php?url=profile');
                exit();
            }

            // Vérification que l'avis appartient bien à l'utilisateur connecté
            $avis = getAvisById($conn, $id);
            if (!$avis || $avis['id_utilisateur'] !== $_SESSION['id_utilisateur']) {
                $_SESSION['error'] = 'Action non autorisée.';
                header('Location: index.php?url=profile');
                exit();
            }

            if (deleteAvis($conn, $id)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Votre avis a été supprimé.']);
                    exit();
                }
                $_SESSION['success'] = 'Votre avis a été supprimé.';
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Impossible de supprimer l\'avis.']);
                    exit();
                }
                $_SESSION['error'] = 'Impossible de supprimer l\'avis.';
            }

            header('Location: index.php?url=profile');
            exit();
        }

        // --- Action : supprimer son propre compte ---
        if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
            $confirm = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === '1';
            $userId  = (int)$_SESSION['id_utilisateur'];

            if (!$confirm) {
                $_SESSION['error'] = 'Suppression annulée ou non confirmée.';
                header('Location: index.php?url=profile');
                exit();
            }

            // Récupération de la photo de profil avant suppression du compte
            $currentUser = getUserById($conn, $userId);
            $imagePath   = $currentUser['photo_profil'] ?? '';

            if (deleteUser($conn, $userId)) {
                // Suppression de la photo de profil personnalisée (pas l'image par défaut)
                if (!empty($imagePath)) {
                    $absolutePath = __DIR__ . '/../../' . ltrim($imagePath, '/');
                    if (is_file($absolutePath) && basename($absolutePath) !== 'default-profile.webp') {
                        @unlink($absolutePath);
                    }
                }

                // Destruction de la session et redirection vers connexion
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['success'] = 'Votre compte a été supprimé.';
                header('Location: index.php?url=login');
                exit();
            }

            $_SESSION['error'] = 'Impossible de supprimer votre compte.';
            header('Location: index.php?url=profile');
            exit();
        }

        // --- Aucune action spécifique : mise à jour du profil ---
        updateProfile();
        return;
    }

    // Affichage du profil : chargement des données de l'utilisateur connecté
    $conn          = connect();
    $user          = getUserById($conn, $_SESSION['id_utilisateur']);
    $favoriteGames = getFavoriteGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedGames    = getAddedGamesByUser($conn, $_SESSION['id_utilisateur']);
    $addedReviews  = getAvisByUser($conn, $_SESSION['id_utilisateur']);
    $activeTab     = $_GET['tab'] ?? 'informations';

    include __DIR__ . '/../../app/views/profile.php';
}

/**
 * Traite la mise à jour des informations du profil :
 * nom, prénom, pseudo, email, question/réponse secrète, mot de passe et photo de profil.
 */
function updateProfile(): void
{
    checkRole(2);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn        = connect();
        $userId      = $_SESSION['id_utilisateur'];
        $currentUser = getUserById($conn, $userId);

        $question_secrete = trim($_POST['question_secrete'] ?? '');
        $reponse_secrete  = trim($_POST['reponse_secrete'] ?? '');

        $data = [
            'prenom'           => trim($_POST['prenom'] ?? ''),
            'nom'              => trim($_POST['nom'] ?? ''),
            'pseudo'           => trim($_POST['pseudo'] ?? ''),
            'email'            => trim($_POST['email'] ?? ''),
            'question_secrete' => $question_secrete,
        ];

        // La réponse secrète n'est mise à jour que si elle est fournie
        if ($reponse_secrete !== '') {
            $data['reponse_secrete'] = $reponse_secrete;
        }

        $newPassword     = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Validation : format de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Le format de l'adresse email n'est pas valide.";
            header('Location: index.php?url=profile');
            exit();
        }

        // Vérification unicité de l'email (autorise la soumission sans changement)
        $existingEmail = getUserByEmail($conn, $data['email']);
        if ($existingEmail && $existingEmail['id_utilisateur'] !== $userId) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
            header('Location: index.php?url=profile');
            exit();
        }

        // Vérification unicité du pseudo (autorise la soumission sans changement)
        $existingUser = getUserByPseudo($conn, $data['pseudo']);
        if ($existingUser && $existingUser['id_utilisateur'] !== $userId) {
            $_SESSION['error'] = "Ce pseudo est déjà utilisé par un autre utilisateur.";
            header('Location: index.php?url=profile');
            exit();
        }

        // Validation du nouveau mot de passe (uniquement s'il est fourni)
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

        // --- Gestion de l'upload de photo de profil ---
        if (isset($_FILES['image_profil']) && $_FILES['image_profil']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image_profil'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
                header('Location: index.php?url=profile');
                exit();
            }

            // Validation du type MIME réel (pas uniquement l'extension)
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $_SESSION['error'] = "Format d'image non autorisé (jpg, png, webp, gif).";
                header('Location: index.php?url=profile');
                exit();
            }
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB maximum
                $_SESSION['error'] = "Image trop volumineuse (max 2MB).";
                header('Location: index.php?url=profile');
                exit();
            }

            // Génération d'un nom de fichier unique pour éviter les collisions
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename  = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $targetDir = __DIR__ . '/../../uploads/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                $_SESSION['error'] = "Impossible d'enregistrer l'image.";
                header('Location: index.php?url=profile');
                exit();
            }

            // Suppression de l'ancienne photo si ce n'est pas l'image par défaut
            if (!empty($currentUser['photo_profil'])) {
                $oldPath = __DIR__ . '/../../' . ltrim($currentUser['photo_profil'], '/');
                if (file_exists($oldPath) && basename($oldPath) !== 'default-profile.webp') {
                    @unlink($oldPath);
                }
            }

            $imagePath    = '/uploads/' . $filename;
            $imageUpdated = updateProfileImage($conn, $userId, $imagePath);
        } else {
            $imageUpdated = true; // Pas de nouvelle image : pas de mise à jour nécessaire
        }

        // Mise à jour des informations du profil en base
        $userUpdated     = updateUser($conn, $userId, $data);
        $passwordUpdated = true;
        if ($newPassword !== '') {
            $hashed          = password_hash($newPassword, PASSWORD_DEFAULT);
            $passwordUpdated = updatePassword($conn, $userId, $hashed);
        }

        if ($userUpdated && $passwordUpdated && $imageUpdated) {
            // Mise à jour du pseudo en session pour affichage immédiat dans la navbar
            $_SESSION['pseudo'] = $data['pseudo'];
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du profil.";
        }
    }

    header('Location: index.php?url=profile');
    exit();
}