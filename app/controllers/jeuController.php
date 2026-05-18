<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/database.php';
require_once __DIR__ . '/../../app/models/jeu.php';
// dirige vers la page de détail d'un jeu, accessible à tous les utilisateurs
function jeu() {
    $conn = connect();
    // prefer slug if provided, else id
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
    // extraire catégories et avis pour la vue
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
// dirige vers la page d'ajout de jeu, accessible uniquement aux utilisateurs connectés ou admin
function jeuAdd() {
    checkRole(2);
    $conn = connect();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];

        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $complexite = $_POST['complexite'] ?? '';
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

        if ($annee_edition !== null && ($annee_edition < 1900 || $annee_edition > (int)date('Y')))
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