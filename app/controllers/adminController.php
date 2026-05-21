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

function adminContent() {
    checkRole(3);
    $conn = connect();
    $jeux = getAllJeux($conn);
    $avis = getAllAvis($conn);
    include __DIR__ . '/../../app/views/admin_content.php';
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