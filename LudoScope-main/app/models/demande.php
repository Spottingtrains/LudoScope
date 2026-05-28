<?php
/**
 * Modèle : demandes
 * Gère les demandes de modification et de suppression de jeux soumises par les utilisateurs.
 * Les modifications proposées sont stockées en JSON dans le champ `message`.
 *
 * Statuts possibles : 'en_attente', 'traite', 'refuse'
 * Types possibles   : 'modification', 'suppression'
 */

/**
 * Crée une nouvelle demande (modification ou suppression).
 *
 * @param PDO   $conn
 * @param array $data  Clés attendues : type_demande, message (JSON), id_jeu, id_utilisateur
 * @return bool
 */
function createDemande(PDO $conn, array $data): bool
{
    $stmt = $conn->prepare(
        "INSERT INTO demande (type_demande, message, date_demande, statut, reponse_admin, id_jeu, id_utilisateur)
         VALUES (?, ?, NOW(), 'en_attente', NULL, ?, ?)"
    );
    return $stmt->execute([
        $data['type_demande'],
        $data['message'],
        $data['id_jeu'],
        $data['id_utilisateur'],
    ]);
}

/**
 * Retourne toutes les demandes en attente, avec le titre du jeu et le pseudo de l'auteur.
 * Triées de la plus récente à la plus ancienne.
 *
 * @param PDO $conn
 * @return array
 */
function getDemandesEnAttente(PDO $conn): array
{
    $stmt = $conn->prepare(
        "SELECT d.*, j.titre AS jeu_titre, u.pseudo AS utilisateur_pseudo
         FROM demande d
         LEFT JOIN jeu j ON d.id_jeu = j.id_jeu
         LEFT JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
         WHERE d.statut = 'en_attente'
         ORDER BY d.date_demande DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère une demande par son identifiant.
 * Inclut le titre du jeu, l'id du propriétaire du jeu et le pseudo de l'auteur de la demande.
 *
 * @param PDO $conn
 * @param int $id_demande
 * @return array|null
 */
function getDemandeById(PDO $conn, int $id_demande): ?array
{
    $stmt = $conn->prepare(
        "SELECT d.*, j.titre AS jeu_titre, j.id_utilisateur AS jeu_owner_id, u.pseudo AS utilisateur_pseudo
         FROM demande d
         LEFT JOIN jeu j ON d.id_jeu = j.id_jeu
         LEFT JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
         WHERE d.id_demande = ?"
    );
    $stmt->execute([(int)$id_demande]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Met à jour le statut d'une demande et enregistre la réponse de l'admin.
 *
 * @param PDO         $conn
 * @param int         $id_demande
 * @param string      $statut        'traite' ou 'refuse'
 * @param string|null $reponseAdmin  Message facultatif affiché à l'utilisateur.
 * @return bool
 */
function updateDemandeStatut(PDO $conn, int $id_demande, string $statut, ?string $reponseAdmin = null): bool
{
    $stmt = $conn->prepare("UPDATE demande SET statut = ?, reponse_admin = ? WHERE id_demande = ?");
    return $stmt->execute([$statut, $reponseAdmin, $id_demande]);
}

/**
 * Vérifie si une demande est déjà en attente pour un jeu donné.
 * Empêche les demandes en double pour le même jeu.
 *
 * @param PDO $conn
 * @param int $id_jeu
 * @return bool
 */
function hasPendingDemande(PDO $conn, int $id_jeu): bool
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM demande WHERE id_jeu = ? AND statut = 'en_attente'");
    $stmt->execute([$id_jeu]);
    return (int)$stmt->fetchColumn() > 0;
}