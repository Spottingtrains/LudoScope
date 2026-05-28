<?php
/**
 * Modèle : jeux
 * Contient toutes les requêtes SQL liées aux tables `jeu`, `categorie`, `jeu_categorie` et `editeur`.
 */

/**
 * Convertit un texte en slug URL-safe (minuscules, sans accents, tirets).
 * Utilisé pour générer des URLs lisibles à partir des titres de jeux.
 *
 * @param string $text
 * @return string
 */
if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
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

/**
 * Retourne la liste des catégories disponibles (slug → libellé).
 *
 * @return array<string, string>
 */
function getJeuCategoryOptions(): array
{
    return [
        'plateau'    => 'Plateau',
        'ambiance'   => 'Ambiance',
        'cartes'     => 'Cartes',
        'cooperatif' => 'Coopératif',
        'role'       => 'Rôle',
        'des'        => 'Dés',
    ];
}

/**
 * Normalise un libellé de catégorie en slug comparable (minuscules, sans accents ni ponctuation).
 * Permet de comparer des valeurs venant de la base avec les slugs du formulaire.
 *
 * @param string $label
 * @return string
 */
function normalizeJeuCategoryLabel(string $label): string
{
    $label = trim($label);
    if (function_exists('iconv')) {
        $label = iconv('UTF-8', 'ASCII//TRANSLIT', $label);
    }
    $label = strtolower($label);
    $label = preg_replace('~[^a-z0-9]+~', '', $label);
    return $label;
}

/**
 * Extrait les slugs des catégories d'un jeu (issues de la base) pour pré-cocher les cases du formulaire.
 *
 * @param array $jeuCategories  Tableau de catégories avec la clé 'nom_categorie'.
 * @return array                Slugs uniques correspondant aux options du formulaire.
 */
function getSelectedJeuCategorySlugs(array $jeuCategories): array
{
    $categoryOptions    = getJeuCategoryOptions();
    $selectedCategories = [];

    foreach ($jeuCategories as $category) {
        $dbValue = $category['nom_categorie'] ?? '';
        $slug    = normalizeJeuCategoryLabel($dbValue);
        if ($slug !== '' && isset($categoryOptions[$slug])) {
            $selectedCategories[] = $slug;
        }
    }

    return array_values(array_unique($selectedCategories));
}

/**
 * Retourne tous les jeux avec leur éditeur, créateur, note moyenne, nombre d'avis et catégories.
 * Triés par date d'ajout décroissante.
 *
 * @param PDO $conn
 * @return array
 */
function getAllJeux(PDO $conn): array
{
    $stmt = $conn->prepare(
        "SELECT jeu.*,
                editeur.nom_editeur,
                u.pseudo AS pseudo_createur,
                ROUND(AVG(avis.note), 1) AS note_moyenne,
                COUNT(DISTINCT avis.id_avis) AS nb_avis,
                GROUP_CONCAT(DISTINCT categorie.libelle_categorie SEPARATOR ', ') AS categories
         FROM jeu
         LEFT JOIN editeur ON jeu.id_editeur = editeur.id_editeur
         LEFT JOIN utilisateur u ON jeu.id_utilisateur = u.id_utilisateur
         LEFT JOIN avis ON jeu.id_jeu = avis.id_jeu
         LEFT JOIN jeu_categorie ON jeu.id_jeu = jeu_categorie.id_jeu
         LEFT JOIN categorie ON jeu_categorie.id_categorie = categorie.id_categorie
         GROUP BY jeu.id_jeu, u.pseudo
         ORDER BY jeu.date_ajout DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne les N jeux les mieux notés, avec un commentaire admin s'il existe.
 * Utilisé pour la mise en avant sur la page d'accueil.
 *
 * @param PDO $conn
 * @param int $limit  Nombre de jeux à retourner (défaut : 3).
 * @return array
 */
function getBestJeux(PDO $conn, int $limit = 3): array
{
    $stmt = $conn->prepare(
        "SELECT j.*,
                ROUND(AVG(a.note), 1) AS note_moyenne,
                (SELECT a2.commentaire
                 FROM avis a2
                 JOIN utilisateur u2 ON a2.id_utilisateur = u2.id_utilisateur
                 WHERE a2.id_jeu = j.id_jeu AND u2.id_role = 3
                 LIMIT 1) AS commentaire_admin
         FROM jeu j
         LEFT JOIN avis a ON j.id_jeu = a.id_jeu
         GROUP BY j.id_jeu
         HAVING note_moyenne IS NOT NULL
         ORDER BY note_moyenne DESC
         LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne tous les libellés de catégories, triés alphabétiquement.
 *
 * @param PDO $conn
 * @return array
 */
function getAllCategories(PDO $conn): array
{
    $stmt = $conn->prepare("SELECT libelle_categorie FROM categorie ORDER BY libelle_categorie ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Recherche des jeux par terme (titre ou description).
 *
 * @param PDO    $conn
 * @param string $q      Terme de recherche.
 * @param int    $limit  Nombre maximum de résultats.
 * @return array
 */
function searchJeux(PDO $conn, string $q, int $limit = 50): array
{
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
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un jeu complet par son identifiant, avec ses catégories, avis et statistiques.
 *
 * @param PDO      $conn
 * @param int|null $id  Si null, tente de lire $_GET['id'].
 * @return array|null
 */
function getJeuById(PDO $conn, ?int $id = null): ?array
{
    // Fallback sur le paramètre GET si aucun ID fourni
    $id = $id ?? (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    // 1. Récupération du jeu et de son éditeur
    $stmt = $conn->prepare(
        "SELECT j.*, e.nom_editeur
         FROM jeu j
         LEFT JOIN editeur e ON j.id_editeur = e.id_editeur
         WHERE j.id_jeu = ?"
    );
    $stmt->execute([$id]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 2. Jeu introuvable : le contrôleur gère le 404
    if (!$jeu) {
        return null;
    }

    // 3. Récupération des catégories du jeu
    $stmtCat = $conn->prepare(
        "SELECT c.libelle_categorie AS nom_categorie
         FROM categorie c
         JOIN jeu_categorie jc ON c.id_categorie = jc.id_categorie
         WHERE jc.id_jeu = ?"
    );
    $stmtCat->execute([$id]);
    $jeu['categories'] = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    // 4. Récupération des avis avec le pseudo et la photo de l'auteur
    $stmtAvis = $conn->prepare(
        "SELECT a.*, u.pseudo, u.photo_profil
         FROM avis a
         LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
         WHERE a.id_jeu = ?
         ORDER BY a.date_avis DESC"
    );
    $stmtAvis->execute([$id]);
    $jeu['avis'] = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);

    // 5. Calcul de la note moyenne et du nombre d'avis
    $stmtStats = $conn->prepare("SELECT ROUND(AVG(note), 1) AS note_moyenne, COUNT(*) AS nb_avis FROM avis WHERE id_jeu = ?");
    $stmtStats->execute([$id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    $jeu['note_moyenne'] = $stats && $stats['note_moyenne'] !== null ? $stats['note_moyenne'] : null;
    $jeu['nb_avis']      = isset($stats['nb_avis']) ? (int)$stats['nb_avis'] : 0;

    return $jeu;
}

/**
 * Récupère un jeu complet à partir de son slug (généré depuis le titre).
 * Parcourt tous les titres pour trouver une correspondance exacte de slug.
 *
 * @param PDO    $conn
 * @param string $slug
 * @return array|null
 */
function getJeuBySlug(PDO $conn, string $slug): ?array
{
    if (empty($slug)) {
        return null;
    }

    // Récupération de tous les titres pour comparaison par slug
    $stmtAll = $conn->prepare("SELECT id_jeu, titre FROM jeu");
    $stmtAll->execute();
    $resAll  = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

    $foundId = null;
    foreach ($resAll as $row) {
        if (slugify($row['titre']) === $slug) {
            $foundId = (int)$row['id_jeu'];
            break;
        }
    }

    if (!$foundId) {
        return null;
    }

    // Délégation à getJeuById pour récupérer les données complètes
    return getJeuById($conn, $foundId);
}

/**
 * Crée un nouveau jeu en base.
 *
 * @param PDO   $conn
 * @param int   $id_utilisateur  Auteur du jeu (utilisateur connecté).
 * @param array $data            Données du jeu (titre, description, etc.)
 * @return int|false  L'id du jeu créé, ou false en cas d'échec.
 */
function createJeu(PDO $conn, int $id_utilisateur, array $data): int|false
{
    $stmt = $conn->prepare(
        "INSERT INTO jeu (titre, description, nb_joueurs_min, nb_joueurs_max, age_min, duree_partie, complexite, image, date_ajout, auteur, illustrateur, annee_edition, id_utilisateur, id_editeur)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)"
    );
    $ok = $stmt->execute([
        $data['titre'],
        $data['description'],
        $data['nb_joueurs_min'],
        $data['nb_joueurs_max'],
        $data['age_min']       ?? null,
        $data['duree_partie'],
        $data['complexite'],
        $data['image'],
        $data['auteur']        ?? null,
        $data['illustrateur']  ?? null,
        $data['annee_edition'] ?? null,
        $id_utilisateur,
        $data['id_editeur']    ?? null,
    ]);
    return $ok ? (int)$conn->lastInsertId() : false;
}

/**
 * Retourne les 5 derniers jeux ajoutés, avec le pseudo du créateur.
 * Utilisé dans le tableau de bord admin.
 *
 * @param PDO $conn
 * @return array
 */
function getDerniersJeux(PDO $conn): array
{
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

/**
 * Retourne tous les éditeurs, triés alphabétiquement.
 *
 * @param PDO $conn
 * @return array
 */
function getAllEditeurs(PDO $conn): array
{
    $stmt = $conn->prepare("SELECT id_editeur, nom_editeur FROM editeur ORDER BY nom_editeur ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un éditeur par son nom, ou le crée s'il n'existe pas encore.
 * Retourne null si le nom est vide.
 *
 * @param PDO    $conn
 * @param string $nomEditeur
 * @return int|null  L'id_editeur, ou null si nom vide.
 */
function getOrCreateEditeur(PDO $conn, string $nomEditeur): ?int
{
    $nomEditeur = trim($nomEditeur);
    if ($nomEditeur === '') return null;

    // Recherche d'un éditeur existant
    $stmt = $conn->prepare('SELECT id_editeur FROM editeur WHERE nom_editeur = ?');
    $stmt->execute([$nomEditeur]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int)$row['id_editeur'];

    // Création si absent
    $stmt = $conn->prepare('INSERT INTO editeur (nom_editeur) VALUES (?)');
    $stmt->execute([$nomEditeur]);
    return (int)$conn->lastInsertId();
}

/**
 * Met à jour un jeu (réservé à son auteur).
 * La clause WHERE inclut id_utilisateur pour empêcher toute modification par un tiers.
 *
 * @param PDO   $conn
 * @param int   $id_jeu
 * @param int   $id_utilisateur
 * @param array $data
 * @return bool
 */
function updateJeu(PDO $conn, int $id_jeu, int $id_utilisateur, array $data): bool
{
    $stmt = $conn->prepare(
        "UPDATE jeu
         SET titre = ?, description = ?, nb_joueurs_min = ?, nb_joueurs_max = ?, age_min = ?,
             duree_partie = ?, complexite = ?, image = ?, auteur = ?, illustrateur = ?,
             annee_edition = ?, id_editeur = ?
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
        $data['auteur']        ?? null,
        $data['illustrateur']  ?? null,
        $data['annee_edition'] ?? null,
        $data['id_editeur']    ?? null,
        $id_jeu,
        $id_utilisateur,
    ]);
}

/**
 * Met à jour un jeu sans restriction de propriétaire (réservé aux administrateurs).
 *
 * @param PDO   $conn
 * @param int   $id_jeu
 * @param array $data
 * @return bool
 */
function updateJeuAdmin(PDO $conn, int $id_jeu, array $data): bool
{
    $stmt = $conn->prepare(
        "UPDATE jeu
         SET titre = ?, description = ?, nb_joueurs_min = ?, nb_joueurs_max = ?, age_min = ?,
             duree_partie = ?, complexite = ?, image = ?, auteur = ?, illustrateur = ?,
             annee_edition = ?, id_editeur = ?
         WHERE id_jeu = ?"
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
        $data['auteur']        ?? null,
        $data['illustrateur']  ?? null,
        $data['annee_edition'] ?? null,
        $data['id_editeur']    ?? null,
        $id_jeu,
    ]);
}

/**
 * Supprime toutes les associations catégories d'un jeu (table de jointure).
 * Appelée avant toute réinsertion des catégories pour éviter les doublons.
 *
 * @param PDO $conn
 * @param int $jeuId
 * @return bool
 */
function deleteJeuCategories(PDO $conn, int $jeuId): bool
{
    $stmt = $conn->prepare('DELETE FROM jeu_categorie WHERE id_jeu = ?');
    return $stmt->execute([$jeuId]);
}

/**
 * Supprime uniquement la ligne du jeu en base (sans toucher au fichier image).
 * La suppression du fichier est laissée au contrôleur.
 *
 * @param PDO $conn
 * @param int $jeuId
 * @return bool
 */
function deleteJeu(PDO $conn, int $jeuId): bool
{
    $stmt = $conn->prepare('DELETE FROM jeu WHERE id_jeu = ?');
    return $stmt->execute([(int)$jeuId]);
}

/**
 * Supprime la ligne du jeu en base et retourne le nom du fichier image associé.
 * Ne supprime PAS le fichier sur le disque — le contrôleur décide quand le faire.
 * Permet de n'effectuer le unlink() qu'après validation de la transaction.
 *
 * @param PDO $conn
 * @param int $jeuId
 * @return string|null|false  Nom de l'image (string), null si aucune image, false en cas d'erreur.
 */
function deleteJeuAndGetImage(PDO $conn, int $jeuId): string|null|false
{
    // Récupération du nom d'image avant suppression
    $stmt = $conn->prepare('SELECT image FROM jeu WHERE id_jeu = ?');
    $stmt->execute([(int)$jeuId]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jeu) {
        return false;
    }

    $stmt    = $conn->prepare('DELETE FROM jeu WHERE id_jeu = ?');
    $deleted = $stmt->execute([(int)$jeuId]);

    return $deleted ? ($jeu['image'] ?? null) : false;
}

/**
 * Insère les catégories sélectionnées pour un jeu dans la table de jointure.
 * Les slugs sont convertis en libellés pour retrouver l'id_categorie en base.
 *
 * @param PDO   $conn
 * @param int   $jeuId
 * @param array $selectedCats  Tableau de slugs (ex. ['plateau', 'cartes']).
 */
function insertJeuCategories(PDO $conn, int $jeuId, array $selectedCats): void
{
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