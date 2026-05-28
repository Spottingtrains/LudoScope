<?php
/**
 * Contrôleur : jeux
 * Gère l'affichage d'un jeu, la recherche AJAX, l'ajout,
 * et les demandes de modification/suppression par l'auteur.
 */

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/avis.php';
require_once __DIR__ . '/../../app/models/demande.php';

/**
 * Affiche la page de détail d'un jeu.
 * Accessible à tous les rôles. Accepte un slug ou un id en paramètre GET.
 * En POST (utilisateur connecté) : traite la soumission d'un avis.
 */
function jeu(): void
{
    $conn = connect();

    // Récupération du jeu par slug (prioritaire) ou par id
    $slug = isset($_GET['slug']) ? urldecode($_GET['slug']) : null;
    if ($slug) {
        $jeu = getJeuBySlug($conn, $slug);
    } else {
        $jeu = getJeuById($conn, isset($_GET['id']) ? (int)$_GET['id'] : null);
    }

    if (!$jeu) {
        http_response_code(404);
        include 'app/views/404.php';
        return;
    }

    // --- Traitement du formulaire d'avis (utilisateur connecté uniquement) ---
    $avisError = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_utilisateur'])) {
        $commentaire    = trim($_POST['commentaire'] ?? '');
        $note           = isset($_POST['note']) ? (int)$_POST['note'] : 0;
        $id_jeu         = (int)$jeu['id_jeu'];
        $id_utilisateur = (int)$_SESSION['id_utilisateur'];

        if ($commentaire === '') {
            $avisError = 'Le commentaire est requis.';
        } elseif ($note < 1 || $note > 10) {
            $avisError = 'La note doit être comprise entre 1 et 10.';
        } elseif (hasAvisFromUser($conn, $id_utilisateur, $id_jeu)) {
            // Un utilisateur ne peut laisser qu'un seul avis par jeu
            $avisError = 'Vous avez déjà posté un avis pour ce jeu.';
        } else {
            createAvis($conn, $id_utilisateur, $id_jeu, $commentaire, $note);
            // Rechargement de la page pour éviter la resoumission du formulaire (PRG)
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    // Valeurs par défaut si certains champs sont absents en base
    $categories = $jeu['categories'] ?? [];
    $avis       = $jeu['avis'] ?? [];
    $jeu        = array_merge([
        'titre'          => 'Jeu inconnu',
        'nom_editeur'    => null,
        'annee_edition'  => null,
        'nb_joueurs_min' => '?',
        'nb_joueurs_max' => '?',
        'duree_partie'   => '?',
        'description'    => 'Aucune description disponible',
    ], $jeu);

    // Formatage de la date d'ajout du jeu pour l'affichage (dd/mm/YYYY HH:ii:ss)
    $jeu['date_ajout_display'] = '';
    if (!empty($jeu['date_ajout'])) {
        try {
            $dta = DateTime::createFromFormat('Y-m-d H:i:s', $jeu['date_ajout'])
                ?: DateTime::createFromFormat('Y-m-d', $jeu['date_ajout'])
                ?: new DateTime($jeu['date_ajout']);
            if ($dta) $jeu['date_ajout_display'] = $dta->format('d/m/Y H:i:s');
        } catch (Exception $e) {
            $jeu['date_ajout_display'] = $jeu['date_ajout'];
        }
    }

    // Formatage de la date de chaque avis
    foreach ($avis as $ik => $a) {
        $avis[$ik]['date_avis_display'] = '';
        if (!empty($a['date_avis'])) {
            try {
                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $a['date_avis'])
                    ?: DateTime::createFromFormat('Y-m-d', $a['date_avis'])
                    ?: new DateTime($a['date_avis']);
                if ($dt) $avis[$ik]['date_avis_display'] = $dt->format('d/m/Y H:i:s');
            } catch (Exception $e) {
                $avis[$ik]['date_avis_display'] = $a['date_avis'];
            }
        }
    }

    $jeu['avis'] = $avis;

    include 'app/views/jeu_detail.php';
}

/**
 * Endpoint JSON pour la recherche de jeux en AJAX.
 * Retourne un tableau de jeux correspondant à la chaîne `?q=`.
 */
function jeuSearch(): void
{
    header('Content-Type: application/json; charset=utf-8');

    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode([]);
        exit();
    }

    $conn = connect();
    require_once __DIR__ . '/../../app/models/jeu.php';

    $res = searchJeux($conn, $q, 50);

    // Formatage de la réponse JSON pour le front-end
    $payload = array_map(function ($j) {
        return [
            'id'            => (int)($j['id_jeu'] ?? 0),
            'titre'         => $j['titre'] ?? '',
            'image'         => !empty($j['image']) ? $j['image'] : 'default.jpg',
            'note_moyenne'  => $j['note_moyenne'] !== null ? (float)$j['note_moyenne'] : null,
            'slug'          => slugify($j['titre'] ?? ''),
            'complexite'    => $j['complexite'] ?? '',
            'duree_partie'  => $j['duree_partie'] ?? null,
            'nb_joueurs_min' => $j['nb_joueurs_min'] ?? null,
            'nb_joueurs_max' => $j['nb_joueurs_max'] ?? null,
            'age_min'       => $j['age_min'] ?? null,
        ];
    }, $res);

    echo json_encode($payload);
    exit();
}

/**
 * Affiche le formulaire d'ajout d'un jeu.
 * En POST : valide les données, gère l'upload de l'image et crée le jeu en base.
 * Accessible aux utilisateurs connectés (rôle >= 2).
 */
function jeuAdd(): void
{
    checkRole(2);
    $conn = connect();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];

        // Récupération et nettoyage des champs du formulaire
        $titre          = trim($_POST['titre'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $complexite     = trim($_POST['complexite'] ?? '');
        $nb_joueurs_min = isset($_POST['nb_joueurs_min']) && $_POST['nb_joueurs_min'] !== '' ? (int)$_POST['nb_joueurs_min'] : null;
        $nb_joueurs_max = isset($_POST['nb_joueurs_max']) && $_POST['nb_joueurs_max'] !== '' ? (int)$_POST['nb_joueurs_max'] : null;
        $duree_partie   = isset($_POST['duree_partie'])   && $_POST['duree_partie']   !== '' ? (int)$_POST['duree_partie']   : null;
        $age_min        = isset($_POST['age_min'])        && $_POST['age_min']        !== '' ? (int)$_POST['age_min']        : null;
        $annee_edition  = isset($_POST['annee_edition'])  && $_POST['annee_edition']  !== '' ? (int)$_POST['annee_edition']  : null;

        // --- Validations ---
        if ($titre === '')       $errors[] = 'Le titre est requis.';
        if ($description === '') $errors[] = 'La description est requise.';
        if ($complexite === '')  $errors[] = 'La complexité est requise.';

        if ($nb_joueurs_min === null || $nb_joueurs_max === null) {
            $errors[] = 'Le nombre minimum et maximum de joueurs est requis.';
        } elseif ($nb_joueurs_min < 1 || $nb_joueurs_max < 1) {
            $errors[] = 'Le nombre de joueurs doit être supérieur ou égal à 1.';
        } elseif ($nb_joueurs_min > $nb_joueurs_max) {
            $errors[] = 'Le nombre minimum de joueurs doit être inférieur ou égal au maximum.';
        }

        if ($duree_partie !== null && $duree_partie < 1) $errors[] = 'La durée doit être supérieure à 0.';
        if ($age_min      !== null && $age_min      < 0) $errors[] = 'L\'âge minimum est invalide.';

        if ($annee_edition !== null && ($annee_edition < 1901 || $annee_edition > (int)date('Y')))
            $errors[] = 'L\'année d\'édition est invalide.';

        // Au moins une catégorie est obligatoire
        if (empty($_POST['categories'])) $errors[] = 'Veuillez sélectionner au moins une catégorie.';

        // --- Gestion de l'upload d'image ---
        $imagePath = null;
        if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file  = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur lors du téléchargement de l\'image.';
            } else {
                // Validation du type MIME réel (pas uniquement l'extension déclarée)
                $finfo   = new finfo(FILEINFO_MIME_TYPE);
                $mime    = $finfo->file($file['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

                if (!isset($allowed[$mime])) {
                    $errors[] = 'Type d\'image non autorisé (jpg, png, gif, webp).';
                } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB maximum
                    $errors[] = 'Image trop volumineuse (max 5MB).';
                } else {
                    $ext     = $allowed[$mime];
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

        // Données pour la repopulation du formulaire en cas d'erreur
        $old = [
            'titre'            => $titre,
            'description'      => $description,
            'complexite'       => $complexite,
            'nb_joueurs_min'   => $nb_joueurs_min,
            'nb_joueurs_max'   => $nb_joueurs_max,
            'duree_partie'     => $duree_partie,
            'age_min'          => $age_min,
            'nom_editeur'      => trim($_POST['nom_editeur']      ?? ''),
            'nom_auteur'       => trim($_POST['nom_auteur']       ?? ''),
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
                'auteur'         => trim($_POST['nom_auteur']       ?? '') ?: null,
                'illustrateur'   => trim($_POST['nom_illustrateur'] ?? '') ?: null,
                'annee_edition'  => $annee_edition,
                // Récupère l'éditeur existant ou en crée un nouveau
                'id_editeur'     => getOrCreateEditeur($conn, $_POST['nom_editeur'] ?? ''),
            ];

            $jeuId = createJeu($conn, $_SESSION['id_utilisateur'], $data);
            if ($jeuId !== false) {
                insertJeuCategories($conn, $jeuId, $_POST['categories'] ?? []);
                $_SESSION['success'] = 'Jeu ajouté avec succès.';
                header('Location: index.php?url=home');
                exit();
            }

            $errors[] = 'Une erreur est survenue lors de la création du jeu.';
        }

        $error = implode('<br>', $errors);
    }

    // Valeurs par défaut lors du premier affichage du formulaire
    if (!isset($old)) {
        $old = [
            'titre'            => '',
            'description'      => '',
            'complexite'       => '',
            'nb_joueurs_min'   => 2,
            'nb_joueurs_max'   => 4,
            'duree_partie'     => '',
            'age_min'          => '',
            'nom_editeur'      => '',
            'nom_auteur'       => '',
            'nom_illustrateur' => '',
            'annee_edition'    => '',
            'categories'       => [],
        ];
    }

    $categories = getJeuCategoryOptions();
    $editeurs   = getAllEditeurs($conn);
    include 'app/views/jeu_add.php';
}

/**
 * Affiche le formulaire de demande de modification d'un jeu.
 * Réservé à l'auteur du jeu (rôle >= 2). En POST : crée une demande en base
 * avec les modifications proposées (stockées en JSON). L'admin devra valider.
 */
function jeuEditRequest(): void
{
    checkRole(2);
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

    // Seul l'auteur du jeu peut soumettre une demande de modification
    if ((int)$jeu['id_utilisateur'] !== (int)$_SESSION['id_utilisateur']) {
        http_response_code(403);
        include 'app/views/404.php';
        return;
    }

    // Vérification qu'aucune demande n'est déjà en attente pour ce jeu
    $pendingDemande = hasPendingDemande($conn, $idJeu);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pendingDemande) {
        $_SESSION['error'] = 'Une demande est déjà en cours pour ce jeu.';
        header('Location: index.php?url=profile');
        exit();
    }

    $categories         = getJeuCategoryOptions();
    $selectedCategories = getSelectedJeuCategorySlugs($jeu['categories'] ?? []);

    // Pré-remplissage du formulaire avec les valeurs actuelles du jeu
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
        'motif'            => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];

        $titre          = trim($_POST['titre'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $complexite     = trim($_POST['complexite'] ?? '');
        $nb_joueurs_min = isset($_POST['nb_joueurs_min']) && $_POST['nb_joueurs_min'] !== '' ? (int)$_POST['nb_joueurs_min'] : null;
        $nb_joueurs_max = isset($_POST['nb_joueurs_max']) && $_POST['nb_joueurs_max'] !== '' ? (int)$_POST['nb_joueurs_max'] : null;
        $duree_partie   = isset($_POST['duree_partie'])   && $_POST['duree_partie']   !== '' ? (int)$_POST['duree_partie']   : null;
        $age_min        = isset($_POST['age_min'])        && $_POST['age_min']        !== '' ? (int)$_POST['age_min']        : null;
        $annee_edition  = isset($_POST['annee_edition'])  && $_POST['annee_edition']  !== '' ? (int)$_POST['annee_edition']  : null;
        $motif          = trim($_POST['motif'] ?? '');

        // --- Validations ---
        if ($titre === '')       $errors[] = 'Le titre est requis.';
        if ($description === '') $errors[] = 'La description est requise.';
        if ($complexite === '')  $errors[] = 'La complexité est requise.';
        if ($motif === '')       $errors[] = 'Merci d\'expliquer les modifications apportées.';

        if ($nb_joueurs_min === null || $nb_joueurs_max === null) {
            $errors[] = 'Le nombre minimum et maximum de joueurs est requis.';
        } elseif ($nb_joueurs_min < 1 || $nb_joueurs_max < 1) {
            $errors[] = 'Le nombre de joueurs doit être supérieur ou égal à 1.';
        } elseif ($nb_joueurs_min > $nb_joueurs_max) {
            $errors[] = 'Le nombre minimum de joueurs doit être inférieur ou égal au maximum.';
        }

        if ($duree_partie !== null && $duree_partie < 1) $errors[] = 'La durée doit être supérieure à 0.';
        if ($age_min !== null && $age_min < 0)           $errors[] = 'L\'âge minimum est invalide.';

        if ($annee_edition !== null && ($annee_edition < 1901 || $annee_edition > (int)date('Y')))
            $errors[] = 'L\'année d\'édition est invalide.';

        if (empty($_POST['categories'])) $errors[] = 'Veuillez sélectionner au moins une catégorie.';

        // --- Gestion de l'upload d'image ---
        $imagePath = $jeu['image'] ?? null;
        if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Erreur lors du téléchargement de l\'image.';
            } else {
                $finfo   = new finfo(FILEINFO_MIME_TYPE);
                $mime    = $finfo->file($file['tmp_name']);
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

        // Repopulation en cas d'erreur
        $old = [
            'titre'            => $titre,
            'description'      => $description,
            'complexite'       => $complexite,
            'nb_joueurs_min'   => $nb_joueurs_min,
            'nb_joueurs_max'   => $nb_joueurs_max,
            'duree_partie'     => $duree_partie,
            'age_min'          => $age_min,
            'nom_editeur'      => trim($_POST['nom_editeur']      ?? ''),
            'nom_auteur'       => trim($_POST['nom_auteur']       ?? ''),
            'nom_illustrateur' => trim($_POST['nom_illustrateur'] ?? ''),
            'annee_edition'    => $annee_edition,
            'categories'       => $_POST['categories'] ?? [],
            'motif'            => $motif,
        ];

        if (count($errors) === 0) {
            // Construction du payload JSON : motif + modifications proposées
            $payload = [
                'motif'            => $motif,
                'proposed_changes' => [
                    'titre'          => $titre,
                    'description'    => $description,
                    'nb_joueurs_min' => $nb_joueurs_min,
                    'nb_joueurs_max' => $nb_joueurs_max,
                    'age_min'        => $age_min,
                    'duree_partie'   => $duree_partie,
                    'complexite'     => $complexite,
                    'image'          => $imagePath,
                    'auteur'         => trim($_POST['nom_auteur']       ?? '') ?: null,
                    'illustrateur'   => trim($_POST['nom_illustrateur'] ?? '') ?: null,
                    'annee_edition'  => $annee_edition,
                    'nom_editeur'    => trim($_POST['nom_editeur'] ?? ''),
                    'categories'     => $_POST['categories'] ?? [],
                ],
            ];

            $demandeCreated = createDemande($conn, [
                'type_demande'   => 'modification',
                'message'        => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'id_jeu'         => $idJeu,
                'id_utilisateur' => (int)$_SESSION['id_utilisateur'],
            ]);

            if ($demandeCreated) {
                $_SESSION['success'] = 'Votre demande de modification a été envoyée aux administrateurs.';
                header('Location: index.php?url=profile');
                exit();
            }

            $errors[] = 'Impossible d\'enregistrer la demande de modification.';
        }

        $error = implode('<br>', $errors);
    }

    $editeurs = getAllEditeurs($conn);
    include 'app/views/jeu_edit.php';
}

/**
 * Traite la demande de suppression d'un jeu par son auteur.
 * La suppression n'est pas immédiate : une demande est créée en base
 * et soumise à validation par un administrateur.
 * Accessible à l'auteur du jeu uniquement (rôle >= 2).
 */
function jeuDeleteRequest(): void
{
    checkRole(2);
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

    // Seul l'auteur du jeu peut soumettre une demande de suppression
    if ((int)$jeu['id_utilisateur'] !== (int)$_SESSION['id_utilisateur']) {
        http_response_code(403);
        include 'app/views/404.php';
        return;
    }

    $pendingDemande = hasPendingDemande($conn, $idJeu);
    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Blocage si une demande est déjà en cours pour ce jeu
        if ($pendingDemande) {
            $_SESSION['error'] = 'Une demande est déjà en cours pour ce jeu.';
            header('Location: index.php?url=profile');
            exit();
        }

        $motif = trim($_POST['motif'] ?? '');

        if ($motif === '') {
            $error = 'Veuillez expliquer pourquoi vous demandez la suppression.';
        } else {
            $payload = ['motif' => $motif];
            $demandeCreated = createDemande($conn, [
                'type_demande'   => 'suppression',
                'message'        => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'id_jeu'         => $idJeu,
                'id_utilisateur' => (int)$_SESSION['id_utilisateur'],
            ]);

            if ($demandeCreated) {
                $_SESSION['success'] = 'Votre demande de suppression a été envoyée aux administrateurs.';
                header('Location: index.php?url=profile');
                exit();
            }

            $error = 'Impossible d\'envoyer la demande de suppression.';
        }
    }

    include 'app/views/jeu_delete.php';
}