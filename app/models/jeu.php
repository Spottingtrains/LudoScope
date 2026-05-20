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
    // Select jeu.* (includes auteur/illustrateur columns), plus editor name and aggregated stats/categories
    $stmt = $conn->prepare("SELECT jeu.*, 
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
        ORDER BY jeu.date_ajout DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllCategories($conn) {
    $stmt = $conn->prepare("SELECT libelle_categorie FROM categorie ORDER BY libelle_categorie ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Recherche de jeux par terme (titre ou description).
 * Retourne un tableau de jeux (limité) avec les champs de base.
 */
function searchJeux($conn, $q, $limit = 50) {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare(
        "SELECT j.*, ROUND(AVG(a.note), 1) AS note_moyenne,
                GROUP_CONCAT(DISTINCT c.libelle_categorie SEPARATOR ', ') AS categories
        FROM jeu j
        LEFT JOIN avis a ON j.id_jeu = a.id_jeu
        LEFT JOIN jeu_categorie jc ON j.id_jeu = jc.id_jeu
        LEFT JOIN categorie c ON jc.id_categorie = c.id_categorie
        WHERE j.titre LIKE ? OR j.description LIKE ?
        GROUP BY j.id_jeu
        ORDER BY j.date_ajout DESC
        LIMIT ?"
    );
    $stmt->bindParam(1, $like, PDO::PARAM_STR);
    $stmt->bindParam(2, $like, PDO::PARAM_STR);
    $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJeuById($conn, $id = null) {
    // 1. Récupérer l'ID depuis l'argument ou l'URL
    $id = $id ?? (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    // 2. Récupérer le jeu + éditeur
    // Simple fetch: jeu contains auteur and illustrateur as varchar columns; join editeur for name
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
        "SELECT a.*, u.pseudo, u.photo_profil
        FROM avis a
        LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
        WHERE a.id_jeu = ?
        ORDER BY a.date_avis DESC"
    );
    $stmtAvis->execute([$id]);
    $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);
    // 6. Attacher catégories et avis au tableau jeu et retourner
    $jeu['categories'] = $categories;
    $jeu['avis'] = $avis;
    // 7. Calculer la note moyenne et le nombre d'avis pour ce jeu
    $stmtStats = $conn->prepare("SELECT ROUND(AVG(note), 1) AS note_moyenne, COUNT(*) AS nb_avis FROM avis WHERE id_jeu = ?");
    $stmtStats->execute([$id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    $jeu['note_moyenne'] = $stats && $stats['note_moyenne'] !== null ? $stats['note_moyenne'] : null;
    $jeu['nb_avis'] = isset($stats['nb_avis']) ? (int)$stats['nb_avis'] : 0;
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
    // Insert using auteur/illustrateur varchar columns present on jeu table
    $stmt = $conn->prepare("INSERT INTO jeu (titre, description, nb_joueurs_min, nb_joueurs_max, age_min, duree_partie, complexite, image, date_ajout, auteur, illustrateur, annee_edition, id_utilisateur, id_editeur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $data['titre'],
        $data['description'],
        $data['nb_joueurs_min'],
        $data['nb_joueurs_max'],
        $data['age_min'] ?? null,
        $data['duree_partie'],
        $data['complexite'],
        $data['image'],
        $data['auteur'] ?? null,
        $data['illustrateur'] ?? null,
        $data['annee_edition'] ?? null,
        $id_utilisateur,
        $data['id_editeur'] ?? null,
    ]);
    if ($ok) {
        return (int)$conn->lastInsertId();
    }
    return false;
}

function getDerniersJeux($conn) {
    $stmt = $conn->prepare(
        "SELECT jeu.*, u.pseudo
        FROM jeu
        LEFT JOIN utilisateur u ON jeu.id_utilisateur = u.id_utilisateur
        ORDER BY jeu.date_ajout DESC
        LIMIT 5"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllEditeurs($conn) {
    $stmt = $conn->prepare("SELECT id_editeur, nom_editeur FROM editeur ORDER BY nom_editeur ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrCreateEditeur($conn, $nomEditeur) {
    $nomEditeur = trim($nomEditeur);
    if ($nomEditeur === '') return null;

    $stmt = $conn->prepare('SELECT id_editeur FROM editeur WHERE nom_editeur = ?');
    $stmt->execute([$nomEditeur]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int)$row['id_editeur'];

    $stmt = $conn->prepare('INSERT INTO editeur (nom_editeur) VALUES (?)');
    $stmt->execute([$nomEditeur]);
    return (int)$conn->lastInsertId();
}

function updateJeu($conn, $id_jeu, $id_utilisateur, $data) {
    $stmt = $conn->prepare(
        "UPDATE jeu
        SET titre = ?, description = ?, nb_joueurs_min = ?, nb_joueurs_max = ?, age_min = ?, duree_partie = ?, complexite = ?, image = ?, auteur = ?, illustrateur = ?, annee_edition = ?, id_editeur = ?
        WHERE id_jeu = ? AND id_utilisateur = ?"
    );

    return $stmt->execute([
        $data['titre'],
        $data['description'],
        $data['nb_joueurs_min'],
        $data['nb_joueurs_max'],
        $data['age_min'],
        $data['duree_partie'],
        $data['complexite'],
        $data['image'],
        $data['auteur'] ?? null,
        $data['illustrateur'] ?? null,
        $data['annee_edition'] ?? null,
        $data['id_editeur'] ?? null,
        $id_jeu,
        $id_utilisateur,
    ]);
}

function deleteJeuCategories($conn, $jeuId) {
    $stmt = $conn->prepare('DELETE FROM jeu_categorie WHERE id_jeu = ?');
    return $stmt->execute([$jeuId]);
}

function deleteJeu($conn, $jeuId) {
    $stmt = $conn->prepare('SELECT image FROM jeu WHERE id_jeu = ?');
    $stmt->execute([(int)$jeuId]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jeu) {
        return false;
    }

    $stmt = $conn->prepare('DELETE FROM jeu WHERE id_jeu = ?');
    $deleted = $stmt->execute([(int)$jeuId]);

    if (!$deleted) {
        return false;
    }

    if (!empty($jeu['image'])) {
        $imagePath = __DIR__ . '/../../uploads/' . $jeu['image'];
        if (is_file($imagePath)) {
            @unlink($imagePath);
        }
    }

    return true;
}

function insertJeuCategories($conn, $jeuId, $selectedCats) {
    $categoryLabels = [
        'plateau'    => 'Plateau',
        'ambiance'   => 'Ambiance',
        'cartes'     => 'Cartes',
        'cooperatif' => 'Coopératif',
        'role'       => 'Rôle',
        'des'        => 'Dés',
    ];
    $selectStmt = $conn->prepare('SELECT id_categorie FROM categorie WHERE libelle_categorie = ?');
    $insertStmt = $conn->prepare('INSERT INTO jeu_categorie (id_jeu, id_categorie) VALUES (?, ?)');
    foreach ($selectedCats as $slug) {
        if (!isset($categoryLabels[$slug])) continue;
        $selectStmt->execute([$categoryLabels[$slug]]);
        $row = $selectStmt->fetch(PDO::FETCH_ASSOC);
        if ($row) $insertStmt->execute([$jeuId, $row['id_categorie']]);
    }
}

// function getDemandesEnAttente($conn) {
//     $stmt = $conn->prepare("SELECT * FROM demande WHERE statut = 'en_attente' ORDER BY date_demande DESC");
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }