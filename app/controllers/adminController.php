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

        if ($demande && $demande['statut'] === 'en_attente' && $demande['type_demande'] === 'modification_jeu') {
            $payload = json_decode($demande['message'], true);
            $response = trim($_POST['reponse_admin'] ?? '');

            if ($decision === 'accepter' && is_array($payload) && !empty($payload['proposed_changes'])) {
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
            } elseif ($decision === 'refuser') {
                updateDemandeStatut($conn, $idDemande, 'refuse', $response !== '' ? $response : 'Demande refusée par l\'administration.');
                $_SESSION['success'] = 'La demande de modification a été refusée.';
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
    
    include __DIR__ . '/../../app/views/admin/users.php';
}