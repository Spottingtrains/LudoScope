<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
require_once __DIR__ . '/../../app/models/avis.php';
require_once __DIR__ . '/../../app/models/demande.php';

// dirige vers la page de détail d'un jeu, accessible à tous les utilisateurs
function jeu() {
    $conn = connect();

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

    // Traitement du formulaire d'avis
    $avisError = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_utilisateur'])) {
        $commentaire = trim($_POST['commentaire'] ?? '');
        $note = isset($_POST['note']) ? (int)$_POST['note'] : 0;
        $id_jeu = (int)$jeu['id_jeu'];
        $id_utilisateur = (int)$_SESSION['id_utilisateur'];

        if ($commentaire === '') {
            $avisError = 'Le commentaire est requis.';
        } elseif ($note < 1 || $note > 10) {
            $avisError = 'La note doit être comprise entre 1 et 10.';
        } elseif (hasAvisFromUser($conn, $id_utilisateur, $id_jeu)) {
            $avisError = 'Vous avez déjà posté un avis pour ce jeu.';
        } else {
            createAvis($conn, $id_utilisateur, $id_jeu, $commentaire, $note);
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    $categories = $jeu['categories'] ?? [];
    $avis = $jeu['avis'] ?? [];
    $jeu = array_merge([
        'titre'          => 'Jeu inconnu',
        'nom_editeur'    => null,
        'annee_edition'  => null,
        'nb_joueurs_min' => '?',
        'nb_joueurs_max' => '?',
        'duree_partie'   => '?',
        'description'    => 'Aucune description disponible',
    ], $jeu);

    include 'app/views/jeu_detail.php';
}

// endpoint JSON pour la recherche AJAX
function jeuSearch() {
    header('Content-Type: application/json; charset=utf-8');
    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode([]);
        exit();
    }
    $conn = connect();
    require_once __DIR__ . '/../../app/models/jeu.php';
    $res = searchJeux($conn, $q, 50);
    $payload = array_map(function($j){
        return [
            'id' => (int)($j['id_jeu'] ?? 0),
            'titre' => $j['titre'] ?? '',
            'image' => !empty($j['image']) ? $j['image'] : 'default.jpg',
            'note_moyenne' => $j['note_moyenne'] !== null ? (float)$j['note_moyenne'] : null,
            'slug' => slugify($j['titre'] ?? ''),
            'complexite' => $j['complexite'] ?? '',
            'duree_partie' => $j['duree_partie'] ?? null,
            'nb_joueurs_min' => $j['nb_joueurs_min'] ?? null,
            'nb_joueurs_max' => $j['nb_joueurs_max'] ?? null,
            'age_min' => $j['age_min'] ?? null,
        ];
    }, $res);
    echo json_encode($payload);
    exit();
}

// dirige vers la page d'ajout de jeu, accessible uniquement aux utilisateurs connectés ou admin
function jeuAdd() {
    checkRole(2);
    $conn = connect();

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

        // --- Validations ---
        if ($titre === '')       $errors[] = 'Le titre est requis.';
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
        if ($age_min      !== null && $age_min      < 0) $errors[] = 'L\'âge minimum est invalide.';

        if ($annee_edition !== null && ($annee_edition < 1901 || $annee_edition > (int)date('Y')))
            $errors[] = 'L\'année d\'édition est invalide.';

        // au moins une catégorie requise
        if (empty($_POST['categories'])) $errors[] = 'Veuillez sélectionner au moins une catégorie.';

        // --- Upload image ---
        $imagePath = null;
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
                } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB maximum
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

        // --- Repopulation du formulaire en cas d'erreur ---
        $old = [
            'titre'            => $titre,
            'description'      => $description,
            'complexite'       => $complexite,
            'nb_joueurs_min'   => $nb_joueurs_min,
            'nb_joueurs_max'   => $nb_joueurs_max,
            'duree_partie'     => $duree_partie,
            'age_min'          => $age_min,
            'nom_editeur'      => trim($_POST['nom_editeur']       ?? ''),
            'nom_auteur'       => trim($_POST['nom_auteur']        ?? ''),
            'nom_illustrateur' => trim($_POST['nom_illustrateur']  ?? ''),
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
                'id_editeur'     => getOrCreateEditeur($conn, $_POST['nom_editeur'] ?? ''),
            ];

            $jeuId = createJeu($conn, $_SESSION['id_utilisateur'], $data);
            if ($jeuId !== false) {
                insertJeuCategories($conn, $jeuId, $_POST['categories'] ?? []);
                // message de succès affiché sur la page d'accueil via la session
                $_SESSION['success'] = 'Jeu ajouté avec succès.';
                header('Location: index.php?url=home');
                exit();
            } else {
                $errors[] = 'Une erreur est survenue lors de la création du jeu.';
            }
        }

        $error = implode('<br>', $errors);
    }

    // --- Valeurs par défaut (premier affichage) ---
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

    $categories = [
        'plateau'    => 'Plateau',
        'ambiance'   => 'Ambiance',
        'cartes'     => 'Cartes',
        'cooperatif' => 'Coopératif',
        'role'       => 'Rôle',
        'des'        => 'Dés',
    ];
    $editeurs = getAllEditeurs($conn);
    include 'app/views/jeu_add.php';
}

// dirige vers la page de modification d'un jeu, accessible uniquement à son auteur
function jeuEditRequest() {
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

    if ((int)$jeu['id_utilisateur'] !== (int)$_SESSION['id_utilisateur']) {
        http_response_code(403);
        include 'app/views/404.php';
        return;
    }

    $pendingDemande = hasPendingDemande($conn, $idJeu);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pendingDemande) {
        $_SESSION['error'] = 'Une demande est déjà en cours pour ce jeu.';
        header('Location: index.php?url=profile');
        exit();
    }

    $categories = [
        'plateau'    => 'Plateau',
        'ambiance'   => 'Ambiance',
        'cartes'     => 'Cartes',
        'cooperatif' => 'Coopératif',
        'role'       => 'Rôle',
        'des'        => 'Dés',
    ];
    $dbValueToSlug = [
    'plateau'    => 'plateau',
    'ambiance'   => 'ambiance',
    'cartes'     => 'cartes',
    'coopératif' => 'cooperatif',
    'rôle'       => 'role',
    'dés'        => 'des',
    ];
    $selectedCategories = [];
    foreach ($jeu['categories'] ?? [] as $category) {
        $dbValue = $category['nom_categorie'] ?? '';
        if (isset($dbValueToSlug[$dbValue])) {
            $selectedCategories[] = $dbValueToSlug[$dbValue];
        }
    }

    $old = [
        'titre'                   => $jeu['titre'] ?? '',
        'description'             => $jeu['description'] ?? '',
        'complexite'              => $jeu['complexite'] ?? '',
        'nb_joueurs_min'          => $jeu['nb_joueurs_min'] ?? 2,
        'nb_joueurs_max'          => $jeu['nb_joueurs_max'] ?? 4,
        'duree_partie'            => $jeu['duree_partie'] ?? '',
        'age_min'                 => $jeu['age_min'] ?? '',
        'nom_editeur'             => $jeu['nom_editeur'] ?? '',
        'nom_auteur'              => $jeu['auteur'] ?? '',
        'nom_illustrateur'        => $jeu['illustrateur'] ?? '',
        'annee_edition'           => $jeu['annee_edition'] ?? '',
        'categories'              => $selectedCategories,
        'motif' => '',
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
        $motif = trim($_POST['motif'] ?? '');

        if ($titre === '')       $errors[] = 'Le titre est requis.';
        if ($description === '') $errors[] = 'La description est requise.';
        if ($complexite === '')  $errors[] = 'La complexité est requise.';
        if ($motif === '') $errors[] = 'Merci d’expliquer les modifications apportées.';

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
            'titre'                   => $titre,
            'description'             => $description,
            'complexite'              => $complexite,
            'nb_joueurs_min'          => $nb_joueurs_min,
            'nb_joueurs_max'          => $nb_joueurs_max,
            'duree_partie'            => $duree_partie,
            'age_min'                 => $age_min,
            'nom_editeur'             => trim($_POST['nom_editeur'] ?? ''),
            'nom_auteur'              => trim($_POST['nom_auteur'] ?? ''),
            'nom_illustrateur'        => trim($_POST['nom_illustrateur'] ?? ''),
            'annee_edition'           => $annee_edition,
            'categories'              => $_POST['categories'] ?? [],
            'motif' => $motif,
        ];

        if (count($errors) === 0) {
            $payload = [
                'motif' => $motif,
                'proposed_changes' => [
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

// demande de suppression d'un jeu par l'auteur
function jeuDeleteRequest() {
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

    if ((int)$jeu['id_utilisateur'] !== (int)$_SESSION['id_utilisateur']) {
        http_response_code(403);
        include 'app/views/404.php';
        return;
    }

    $pendingDemande = hasPendingDemande($conn, $idJeu);

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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