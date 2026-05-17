<?php
if (!function_exists('slugify')) {
    function slugify($text) {
        $text = trim($text);
        if (function_exists('iconv')) {
            $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        }
        $text = strtolower($text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return $text ?: 'n-a';
    }
}
function getAllJeux($conn) {
    $stmt = $conn->prepare("
        SELECT jeu.*, 
            editeur.nom_editeur,
            ROUND(AVG(avis.note), 1) AS note_moyenne,
            COUNT(DISTINCT avis.id_avis) AS nb_avis,
            GROUP_CONCAT(DISTINCT categorie.libelle_categorie SEPARATOR ', ') AS categories
        FROM jeu
        LEFT JOIN editeur ON jeu.id_editeur = editeur.id_editeur
        LEFT JOIN avis ON jeu.id_jeu = avis.id_jeu
        LEFT JOIN jeu_categorie ON jeu.id_jeu = jeu_categorie.id_jeu
        LEFT JOIN categorie ON jeu_categorie.id_categorie = categorie.id_categorie
        GROUP BY jeu.id_jeu
        ORDER BY jeu.date_ajout DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJeuById($conn, $id = null) {
    // 1. Récupérer l'ID depuis l'argument ou l'URL
    $id = $id ?? (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    // 2. Récupérer le jeu + éditeur
    $stmt = $conn->prepare(
        "SELECT j.*, e.nom_editeur
        FROM jeu j
        LEFT JOIN editeur e ON j.id_editeur = e.id_editeur
        WHERE j.id_jeu = ?"
    );
    $stmt->execute([$id]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    // 3. Retourner null si non trouvé (le contrôleur gère le 404)
    if (!$jeu) {
        return null;
    }
    // 4. Récupérer les catégories du jeu
    $stmtCat = $conn->prepare(
        "SELECT c.libelle_categorie AS nom_categorie
        FROM categorie c
        JOIN jeu_categorie jc ON c.id_categorie = jc.id_categorie
        WHERE jc.id_jeu = ?"
    );
    $stmtCat->execute([$id]);
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    // 5. Récupérer les avis du jeu
    $stmtAvis = $conn->prepare(
        "SELECT a.*, u.pseudo
        FROM avis a
        JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
        WHERE a.id_jeu = ?
        ORDER BY a.date_avis DESC"
    );
    $stmtAvis->execute([$id]);
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);
    // 6. Attacher catégories et avis au tableau jeu et retourner
    $jeu['categories'] = $categories;
    $jeu['avis'] = $avis;
    return $jeu;
}

function getJeuBySlug($conn, $slug) {
    if (empty($slug)) {
        return null;
    }

    // Normalize function for slugs
    if (!function_exists('slugify')) {
        function slugify($text) {
            $text = trim($text);
            // transliterate
            if (function_exists('iconv')) {
                $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
            }
            $text = strtolower($text);
            // replace non letters or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);
            // remove unwanted characters
            $text = preg_replace('~[^-a-z0-9]+~', '', $text);
            // trim
            $text = trim($text, '-');
            // remove duplicate -
            $text = preg_replace('~-+~', '-', $text);
            return $text ?: 'n-a';
        }
    }

    $target = $slug;

    // Fetch minimal fields to find matching slug
    $stmtAll = $conn->prepare("SELECT id_jeu, titre FROM jeu");
    $stmtAll->execute();
    $resAll = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    $foundId = null;
    foreach ($resAll as $row) {
        if (slugify($row['titre']) === $target) {
            $foundId = (int)$row['id_jeu'];
            break;
        }
    }

    if (!$foundId) {
        return null;
    }

    // Delegate to getJeuById to fetch full data
    return getJeuById($conn, $foundId);
}

function createJeu($conn, $id_utilisateur, $data) {
    $stmt = $conn->prepare("INSERT INTO jeu (titre, description, nb_joueurs_min, nb_joueurs_max, age_min, age_max, duree_partie, complexite, image, date_ajout, id_utilisateur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    return $stmt->execute([
        $data['titre'],
        $data['description'],
        $data['nb_joueurs_min'],
        $data['nb_joueurs_max'],
        $data['age_min'],
        $data['age_max'],
        $data['duree_partie'],
        $data['complexite'],
        $data['image'],
        $id_utilisateur
    ]);
}

function getDerniersJeux($conn) {
    $stmt = $conn->prepare("SELECT * FROM jeu ORDER BY date_ajout DESC LIMIT 5");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// function getDemandesEnAttente($conn) {
//     $stmt = $conn->prepare("SELECT * FROM demande WHERE statut = 'en_attente' ORDER BY date_demande DESC");
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }