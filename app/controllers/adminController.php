<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/user.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/avis.php';
require_once __DIR__ . '/../../app/models/demande.php';
require_once __DIR__ . '/../../app/models/stats.php';

function dashboard() {
    checkRole(3);
    
    $conn = connect();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_demande'], $_POST['decision'])) {
        $idDemande = (int)$_POST['id_demande'];
        $decision = $_POST['decision'];
        $demande = getDemandeById($conn, $idDemande);

        if ($demande && $demande['statut'] === 'en_attente') {
            $payload = json_decode($demande['message'], true);
            $response = trim($_POST['reponse_admin'] ?? '');

            if ($demande['type_demande'] === 'modification' && $decision === 'accepter' && is_array($payload) && !empty($payload['proposed_changes'])) {
                $changes = $payload['proposed_changes'];
                $data = [
                    'titre'          => $changes['titre'] ?? '',
                    'description'    => $changes['description'] ?? '',
                    'nb_joueurs_min' => isset($changes['nb_joueurs_min']) ? (int)$changes['nb_joueurs_min'] : null,
                    'nb_joueurs_max' => isset($changes['nb_joueurs_max']) ? (int)$changes['nb_joueurs_max'] : null,
                    'age_min'        => isset($changes['age_min']) ? (int)$changes['age_min'] : null,
                    'duree_partie'   => isset($changes['duree_partie']) ? (int)$changes['duree_partie'] : null,
                    'complexite'     => $changes['complexite'] ?? '',
                    'image'          => $changes['image'] ?? ($demande['image'] ?? null),
                    'auteur'         => $changes['auteur'] ?? null,
                    'illustrateur'   => $changes['illustrateur'] ?? null,
                    'annee_edition'  => isset($changes['annee_edition']) && $changes['annee_edition'] !== '' ? (int)$changes['annee_edition'] : null,
                    'id_editeur'     => getOrCreateEditeur($conn, $changes['nom_editeur'] ?? ''),
                ];

                if (updateJeu($conn, (int)$demande['id_jeu'], (int)$demande['jeu_owner_id'], $data)) {
                    deleteJeuCategories($conn, (int)$demande['id_jeu']);
                    insertJeuCategories($conn, (int)$demande['id_jeu'], $changes['categories'] ?? []);
                    updateDemandeStatut($conn, $idDemande, 'traite', $response !== '' ? $response : 'Modification appliquée.');
                    $_SESSION['success'] = 'La demande de modification a été acceptée.';
                } else {
                    $_SESSION['error'] = 'Impossible d\'appliquer les modifications du jeu.';
                }
            } elseif ($demande['type_demande'] === 'modification' && $decision === 'refuser') {
                updateDemandeStatut($conn, $idDemande, 'refuse', $response !== '' ? $response : 'Demande refusée par l\'administration.');
                $_SESSION['success'] = 'La demande de modification a été refusée.';
            } elseif ($demande['type_demande'] === 'suppression' && $decision === 'accepter') {
                $confirmAdminDelete = isset($_POST['confirm_admin_delete']) && $_POST['confirm_admin_delete'] === '1';

                if (!$confirmAdminDelete) {
                    $_SESSION['error'] = 'Veuillez confirmer la suppression définitive avant de valider.';
                } else {
                    try {
                        $conn->beginTransaction();
                        $jeuId = (int)$demande['id_jeu'];
                        if (!deleteJeuCategories($conn, $jeuId)) {
                            throw new Exception('Erreur suppression catégories.');
                        }
                        $imageName = deleteJeuAndGetImage($conn, $jeuId);
                        if ($imageName === false) {
                            throw new Exception('Impossible de supprimer le jeu en base.');
                        }
                        $conn->commit();

                        if (!empty($imageName)) {
                            $imagePath = __DIR__ . '/../../uploads/' . $imageName;
                            if (is_file($imagePath)) @unlink($imagePath);
                        }

                        updateDemandeStatut($conn, $idDemande, 'traite', $response !== '' ? $response : 'Suppression appliquée.');
                        $_SESSION['success'] = 'La demande de suppression a été acceptée.';
                    } catch (Exception $e) {
                        if ($conn->inTransaction()) $conn->rollBack();
                        error_log('admin dashboard suppression error: ' . $e->getMessage());
                        $_SESSION['error'] = 'Impossible de supprimer le jeu.';
                    }
                }
            } elseif ($demande['type_demande'] === 'suppression' && $decision === 'refuser') {
                updateDemandeStatut($conn, $idDemande, 'refuse', $response !== '' ? $response : 'Demande refusée par l\'administration.');
                $_SESSION['success'] = 'La demande de suppression a été refusée.';
            }

            header('Location: index.php?url=back-office');
            exit();
        }
    }

    $stats = getStats($conn);
    $derniers_jeux = getDerniersJeux($conn);
    $derniers_avis = getDerniersAvis($conn);
    $demandes = getDemandesEnAttente($conn);
    
    include __DIR__ . '/../../app/views/back-office.php';
}

function adminUsers() {
    checkRole(3);
    
    $conn = connect();
    $users = getAllUsers($conn);
    // Récupérer l'ID de l'utilisateur connecté pour la vue
    $currentId = isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : null;

    include __DIR__ . '/../../app/views/admin_users.php';
}

function adminUserCreate() {
    checkRole(3);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Méthode non autorisée.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    $conn = connect();
    $role = isset($_POST['role']) ? (int)$_POST['role'] : 0;
    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!in_array($role, [2, 3], true)) {
        $_SESSION['error'] = 'Veuillez choisir un rôle valide.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    if ($pseudo === '' || $email === '' || $password === '') {
        $_SESSION['error'] = 'Tous les champs sont requis.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Le format de l\'adresse email n\'est pas valide.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{8,}$/', $password)) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    if (getUserByEmail($conn, $email)) {
        $_SESSION['error'] = 'Un utilisateur avec cet email existe déjà.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    if (getUserByPseudo($conn, $pseudo)) {
        $_SESSION['error'] = 'Un utilisateur avec ce pseudo existe déjà.';
        header('Location: index.php?url=admin_users');
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $dateInscription = date('Y-m-d H:i:s');

    if (createUser($conn, '', '', $pseudo, $email, $hashedPassword, $dateInscription, $role)) {
        $_SESSION['success'] = 'Utilisateur créé avec succès.';
    } else {
        $_SESSION['error'] = 'Impossible de créer l\'utilisateur.';
    }

    header('Location: index.php?url=admin_users');
    exit();
}

function adminContent() {
    checkRole(3);
    $conn = connect();
    $jeux = getAllJeux($conn);
    $avis = getAllAvis($conn);
    include __DIR__ . '/../../app/views/admin_content.php';
}

function adminEditGame() {
    checkRole(3);
    $conn = connect();

    $idJeu = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($idJeu <= 0) {
        http_response_code(404);
        include 'app/views/404.php';
        return;
    }

    $jeu = getJeuById($conn, $idJeu);
    if (!$jeu) {
        http_response_code(404);
        include 'app/views/404.php';
        return;
    }

    $categories = getJeuCategoryOptions();
    $selectedCategories = getSelectedJeuCategorySlugs($jeu['categories'] ?? []);

    $old = [
        'titre'            => $jeu['titre'] ?? '',
        'description'      => $jeu['description'] ?? '',
        'complexite'       => $jeu['complexite'] ?? '',
        'nb_joueurs_min'   => $jeu['nb_joueurs_min'] ?? 2,
        'nb_joueurs_max'   => $jeu['nb_joueurs_max'] ?? 4,
        'duree_partie'     => $jeu['duree_partie'] ?? '',
        'age_min'          => $jeu['age_min'] ?? '',
        'nom_editeur'      => $jeu['nom_editeur'] ?? '',
        'nom_auteur'       => $jeu['auteur'] ?? '',
        'nom_illustrateur' => $jeu['illustrateur'] ?? '',
        'annee_edition'    => $jeu['annee_edition'] ?? '',
        'categories'       => $selectedCategories,
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];

        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $complexite = trim($_POST['complexite'] ?? '');
        $nb_joueurs_min = isset($_POST['nb_joueurs_min']) && $_POST['nb_joueurs_min'] !== '' ? (int)$_POST['nb_joueurs_min'] : null;
        $nb_joueurs_max = isset($_POST['nb_joueurs_max']) && $_POST['nb_joueurs_max'] !== '' ? (int)$_POST['nb_joueurs_max'] : null;
        $duree_partie   = isset($_POST['duree_partie'])   && $_POST['duree_partie']   !== '' ? (int)$_POST['duree_partie']   : null;
        $age_min        = isset($_POST['age_min'])        && $_POST['age_min']        !== '' ? (int)$_POST['age_min']        : null;
        $annee_edition  = isset($_POST['annee_edition'])  && $_POST['annee_edition']  !== '' ? (int)$_POST['annee_edition']  : null;

        if ($titre === '') $errors[] = 'Le titre est requis.';
        if ($description === '') $errors[] = 'La description est requise.';
        if ($complexite === '') $errors[] = 'La complexité est requise.';

        if ($nb_joueurs_min === null || $nb_joueurs_max === null) {
            $errors[] = 'Le nombre minimum et maximum de joueurs est requis.';
        } elseif ($nb_joueurs_min < 1 || $nb_joueurs_max < 1) {
            $errors[] = 'Le nombre de joueurs doit être supérieur ou égal à 1.';
        } elseif ($nb_joueurs_min > $nb_joueurs_max) {
            $errors[] = 'Le nombre minimum de joueurs doit être inférieur ou égal au maximum.';
        }

        if ($duree_partie !== null && $duree_partie < 1) $errors[] = 'La durée doit être supérieure à 0.';
        if ($age_min !== null && $age_min < 0) $errors[] = 'L\'âge minimum est invalide.';

        if ($annee_edition !== null && ($annee_edition < 1901 || $annee_edition > (int)date('Y'))) {
            $errors[] = 'L\'année d\'édition est invalide.';
        }

        if (empty($_POST['categories'])) $errors[] = 'Veuillez sélectionner au moins une catégorie.';

        $imagePath = $jeu['image'] ?? null;
        if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur lors du téléchargement de l\'image.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (!isset($allowed[$mime])) {
                    $errors[] = 'Type d\'image non autorisé (jpg, png, gif, webp).';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Image trop volumineuse (max 5MB).';
                } else {
                    $ext      = $allowed[$mime];
                    $filename = uniqid('jeu_', true) . '.' . $ext;
                    $destDir  = __DIR__ . '/../../uploads';
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
                        $errors[] = 'Impossible de déplacer l\'image.';
                    } else {
                        $imagePath = $filename;
                    }
                }
            }
        }

        $old = [
            'titre'            => $titre,
            'description'      => $description,
            'complexite'       => $complexite,
            'nb_joueurs_min'   => $nb_joueurs_min,
            'nb_joueurs_max'   => $nb_joueurs_max,
            'duree_partie'     => $duree_partie,
            'age_min'          => $age_min,
            'nom_editeur'      => trim($_POST['nom_editeur'] ?? ''),
            'nom_auteur'       => trim($_POST['nom_auteur'] ?? ''),
            'nom_illustrateur' => trim($_POST['nom_illustrateur'] ?? ''),
            'annee_edition'    => $annee_edition,
            'categories'       => $_POST['categories'] ?? [],
        ];

        if (count($errors) === 0) {
            $data = [
                'titre'          => $titre,
                'description'    => $description,
                'nb_joueurs_min' => $nb_joueurs_min,
                'nb_joueurs_max' => $nb_joueurs_max,
                'age_min'        => $age_min,
                'duree_partie'   => $duree_partie,
                'complexite'     => $complexite,
                'image'          => $imagePath,
                'auteur'         => trim($_POST['nom_auteur'] ?? '') ?: null,
                'illustrateur'   => trim($_POST['nom_illustrateur'] ?? '') ?: null,
                'annee_edition'  => $annee_edition,
                'id_editeur'     => getOrCreateEditeur($conn, $_POST['nom_editeur'] ?? ''),
            ];

            if (updateJeuAdmin($conn, $idJeu, $data)) {
                deleteJeuCategories($conn, $idJeu);
                insertJeuCategories($conn, $idJeu, $_POST['categories'] ?? []);
                $_SESSION['success'] = 'Jeu mis à jour.';
                header('Location: index.php?url=admin_content');
                exit();
            }

            $errors[] = 'Impossible de mettre à jour le jeu.';
        }

        $error = implode(' · ', $errors);
    }

    $editeurs = getAllEditeurs($conn);
    include __DIR__ . '/../../app/views/admin_jeu_edit.php';
}

function adminUpdateGame() {
    checkRole(3);
    $conn = connect();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Méthode non autorisée.';
        header('Location: index.php?url=admin_content');
        exit();
    }

    $id = isset($_POST['id_jeu']) ? (int)$_POST['id_jeu'] : 0;
    if (!$id) {
        $_SESSION['error'] = 'Jeu invalide.';
        header('Location: index.php?url=admin_content');
        exit();
    }

    $data = [
        'titre' => trim($_POST['titre'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'nb_joueurs_min' => isset($_POST['nb_joueurs_min']) && $_POST['nb_joueurs_min'] !== '' ? (int)$_POST['nb_joueurs_min'] : null,
        'nb_joueurs_max' => isset($_POST['nb_joueurs_max']) && $_POST['nb_joueurs_max'] !== '' ? (int)$_POST['nb_joueurs_max'] : null,
        'age_min' => isset($_POST['age_min']) && $_POST['age_min'] !== '' ? (int)$_POST['age_min'] : null,
        'duree_partie' => isset($_POST['duree_partie']) && $_POST['duree_partie'] !== '' ? (int)$_POST['duree_partie'] : null,
        'complexite' => $_POST['complexite'] ?? '',
        'image' => $_POST['image'] ?? null,
        'auteur' => $_POST['auteur'] ?? null,
        'illustrateur' => $_POST['illustrateur'] ?? null,
        'annee_edition' => isset($_POST['annee_edition']) && $_POST['annee_edition'] !== '' ? (int)$_POST['annee_edition'] : null,
        'id_editeur' => isset($_POST['id_editeur']) && $_POST['id_editeur'] !== '' ? (int)$_POST['id_editeur'] : null,
    ];

    if (updateJeuAdmin($conn, $id, $data)) {
        $_SESSION['success'] = 'Jeu mis à jour.';
    } else {
        $_SESSION['error'] = 'Impossible de mettre à jour le jeu.';
    }
    header('Location: index.php?url=admin_content');
    exit();
}

function adminDeleteGame() {
    checkRole(3);
    $conn = connect();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Méthode non autorisée.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if (!$id) {
        $_SESSION['error'] = 'Jeu invalide.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    try {
        $conn->beginTransaction();
        if (!deleteJeuCategories($conn, $id)) {
            throw new Exception('Erreur suppression catégories.');
        }
        $imageName = deleteJeuAndGetImage($conn, $id);
        if ($imageName === false) {
            throw new Exception('Impossible de supprimer le jeu en base.');
        }
        $conn->commit();

        if (!empty($imageName)) {
            $imagePath = __DIR__ . '/../../uploads/' . $imageName;
            if (is_file($imagePath)) @unlink($imagePath);
        }

        $_SESSION['success'] = 'Jeu supprimé définitivement.';
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        error_log('adminDeleteGame error: ' . $e->getMessage());
        $_SESSION['error'] = 'Impossible de supprimer le jeu.';
    }
    header('Location: index.php?url=admin_content');
    exit();
}

function adminUpdateAvis() {
    checkRole(3);
    $conn = connect();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Méthode non autorisée.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    $id = isset($_POST['id_avis']) ? (int)$_POST['id_avis'] : 0;
    if (!$id) {
        $_SESSION['error'] = 'Avis invalide.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    $commentaire = trim($_POST['commentaire'] ?? '');
    $note = isset($_POST['note']) && $_POST['note'] !== '' ? (int)$_POST['note'] : null;
    if (updateAvis($conn, $id, $commentaire, $note)) {
        $_SESSION['success'] = 'Avis mis à jour.';
    } else {
        $_SESSION['error'] = 'Impossible de mettre à jour l\'avis.';
    }
    header('Location: index.php?url=admin_content');
    exit();
}

function adminDeleteAvis() {
    checkRole(3);
    $conn = connect();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = 'Méthode non autorisée.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if (!$id) {
        $_SESSION['error'] = 'Avis invalide.';
        header('Location: index.php?url=admin_content');
        exit();
    }
    if (deleteAvis($conn, $id)) {
        $_SESSION['success'] = 'Avis supprimé définitivement.';
    } else {
        $_SESSION['error'] = 'Impossible de supprimer l\'avis.';
    }
    header('Location: index.php?url=admin_content');
    exit();
}

function adminUserEdit() {
    checkRole(3);
    $conn = connect();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $user = getUserById($conn, $id);
    if (!$user) {
        http_response_code(404);
        include __DIR__ . '/../../app/views/404.php';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'nom'    => trim($_POST['nom'] ?? $user['nom']),
            'prenom' => trim($_POST['prenom'] ?? $user['prenom']),
            'pseudo' => trim($_POST['pseudo'] ?? $user['pseudo']),
            'email'  => trim($_POST['email'] ?? $user['email']),
        ];

        if (updateUser($conn, $id, $data)) {
            $_SESSION['success'] = 'Utilisateur mis à jour.';
            header('Location: index.php?url=admin_users');
            exit();
        } else {
            $_SESSION['error'] = 'Impossible de mettre à jour l\'utilisateur.';
        }
    }

    include __DIR__ . '/../../app/views/admin_user_edit.php';
}

function adminUserDelete() {
    checkRole(3);
    $conn = connect();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        // Récupérer l'ID de l'utilisateur connecté (clé canonique 'id_utilisateur')
        $currentId = isset($_SESSION['id_utilisateur']) ? (int)$_SESSION['id_utilisateur'] : null;

        if ($currentId !== null && $currentId === $id) {
            $_SESSION['error'] = 'Vous ne pouvez pas supprimer votre propre compte administrateur.';
        } else {
            if ($id && deleteUser($conn, $id)) {
                $_SESSION['success'] = 'Utilisateur supprimé.';
            } else {
                $_SESSION['error'] = 'Impossible de supprimer l\'utilisateur.';
            }
        }
    } else {
        $_SESSION['error'] = 'Méthode non autorisée.';
    }
    header('Location: index.php?url=admin_users');
    exit();
}