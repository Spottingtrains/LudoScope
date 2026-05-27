<?php
/**
 * Modèle : avis
 * Contient toutes les requêtes SQL liées à la table `avis`.
 */

/**
 * Retourne tous les avis postés par un utilisateur, avec le titre du jeu associé.
 *
 * @param PDO $conn
 * @param int $id_utilisateur
 * @return array
 */
function getAvisByUser(PDO $conn, int $id_utilisateur): array
{
    $stmt = $conn->prepare(
        "SELECT avis.*, jeu.titre
         FROM avis
         JOIN jeu ON avis.id_jeu = jeu.id_jeu
         WHERE avis.id_utilisateur = ?
         ORDER BY avis.date_avis DESC"
    );
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si un utilisateur a déjà posté un avis pour un jeu donné.
 * Utilisé pour empêcher les doublons (un avis par utilisateur par jeu).
 *
 * @param PDO $conn
 * @param int $id_utilisateur
 * @param int $id_jeu
 * @return bool
 */
function hasAvisFromUser(PDO $conn, int $id_utilisateur, int $id_jeu): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM avis WHERE id_utilisateur = ? AND id_jeu = ?");
    $stmt->execute([$id_utilisateur, $id_jeu]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Crée un nouvel avis pour un jeu.
 *
 * @param PDO    $conn
 * @param int    $id_utilisateur
 * @param int    $id_jeu
 * @param string $commentaire
 * @param int    $note           Entre 1 et 10.
 * @return bool
 */
function createAvis(PDO $conn, int $id_utilisateur, int $id_jeu, string $commentaire, int $note): bool
{
    $stmt = $conn->prepare("INSERT INTO avis (commentaire, note, date_avis, id_utilisateur, id_jeu) VALUES (?, ?, NOW(), ?, ?)");
    return $stmt->execute([$commentaire, $note, $id_utilisateur, $id_jeu]);
}

/**
 * Met à jour le commentaire et la note d'un avis (par l'auteur).
 * Met également à jour la date de modification.
 *
 * @param PDO    $conn
 * @param int    $id_avis
 * @param string $commentaire
 * @param int    $note
 * @return bool
 */
function updateAvis(PDO $conn, int $id_avis, string $commentaire, int $note): bool
{
    $stmt = $conn->prepare("UPDATE avis SET commentaire = ?, note = ?, date_modification = NOW() WHERE id_avis = ?");
    return $stmt->execute([$commentaire, $note, $id_avis]);
}

/**
 * Met à jour uniquement le commentaire d'un avis (utilisé par l'admin).
 *
 * @param PDO    $conn
 * @param int    $id_avis
 * @param string $commentaire
 * @return bool
 */
function updateAvisCommentaire(PDO $conn, int $id_avis, string $commentaire): bool
{
    $stmt = $conn->prepare("UPDATE avis SET commentaire = ?, date_modification = NOW() WHERE id_avis = ?");
    return $stmt->execute([$commentaire, $id_avis]);
}

/**
 * Supprime définitivement un avis.
 *
 * @param PDO $conn
 * @param int $id_avis
 * @return bool
 */
function deleteAvis(PDO $conn, int $id_avis): bool
{
    $stmt = $conn->prepare("DELETE FROM avis WHERE id_avis = ?");
    return $stmt->execute([$id_avis]);
}

/**
 * Retourne les 5 derniers avis postés, avec le pseudo de l'auteur et le titre du jeu.
 * Utilisé dans le tableau de bord admin.
 *
 * @param PDO $conn
 * @return array
 */
function getDerniersAvis(PDO $conn): array
{
    $stmt = $conn->prepare(
        "SELECT avis.*, utilisateur.pseudo, jeu.titre
         FROM avis
         LEFT JOIN utilisateur ON avis.id_utilisateur = utilisateur.id_utilisateur
         JOIN jeu ON avis.id_jeu = jeu.id_jeu
         ORDER BY avis.date_avis DESC
         LIMIT 5"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un avis par son identifiant.
 *
 * @param PDO $conn
 * @param int $id_avis
 * @return array|null
 */
function getAvisById(PDO $conn, int $id_avis): ?array
{
    $stmt = $conn->prepare("SELECT * FROM avis WHERE id_avis = ?");
    $stmt->execute([(int)$id_avis]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Retourne tous les avis, avec le pseudo de l'auteur et le titre du jeu.
 * Utilisé dans la gestion de contenu du back-office.
 *
 * @param PDO $conn
 * @return array
 */
function getAllAvis(PDO $conn): array
{
    $stmt = $conn->prepare(
        "SELECT avis.*, utilisateur.pseudo, jeu.titre
         FROM avis
         LEFT JOIN utilisateur ON avis.id_utilisateur = utilisateur.id_utilisateur
         JOIN jeu ON avis.id_jeu = jeu.id_jeu
         ORDER BY avis.date_avis DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}