<?php
/**
 * Modèle : utilisateurs
 * Contient toutes les requêtes SQL liées à la table `utilisateur`.
 */

/**
 * Récupère un utilisateur par son adresse email.
 *
 * @param PDO    $conn
 * @param string $email
 * @return array|null  Tableau associatif ou null si non trouvé.
 */
function getUserByEmail(PDO $conn, string $email): ?array
{
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Récupère un utilisateur par son pseudo.
 *
 * @param PDO    $conn
 * @param string $pseudo
 * @return array|null
 */
function getUserByPseudo(PDO $conn, string $pseudo): ?array
{
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Récupère un utilisateur par son identifiant.
 *
 * @param PDO $conn
 * @param int $id
 * @return array|null
 */
function getUserById(PDO $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Crée un nouvel utilisateur en base.
 * Le mot de passe doit déjà être haché avant l'appel.
 *
 * @param PDO    $conn
 * @param string $firstname
 * @param string $lastname
 * @param string $pseudo
 * @param string $email
 * @param string $hashedPassword
 * @param string $date_inscription  Format Y-m-d H:i:s
 * @param string $question_secrete
 * @param string $reponse_secrete
 * @param int    $id_role           Par défaut : 2 (utilisateur connecté)
 * @return bool
 */
function createUser(PDO $conn, string $firstname, string $lastname, string $pseudo, string $email, string $hashedPassword, string $date_inscription, string $question_secrete, string $reponse_secrete, int $id_role = 2): bool
{
    $stmt = $conn->prepare("INSERT INTO utilisateur (prenom, nom, pseudo, email, mot_de_passe, id_role, date_inscription, question_secrete, reponse_secrete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$firstname, $lastname, $pseudo, $email, $hashedPassword, $id_role, $date_inscription, $question_secrete, $reponse_secrete]);
}

/**
 * Retourne tous les utilisateurs, triés par date d'inscription décroissante.
 *
 * @param PDO $conn
 * @return array
 */
function getAllUsers(PDO $conn): array
{
    $stmt = $conn->prepare("SELECT * FROM utilisateur ORDER BY date_inscription DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Met à jour les informations d'un utilisateur.
 * La réponse secrète n'est mise à jour que si elle est présente dans $data.
 *
 * @param PDO   $conn
 * @param int   $id
 * @param array $data  Clés attendues : nom, prenom, pseudo, email, question_secrete, [reponse_secrete]
 * @return bool
 */
function updateUser(PDO $conn, int $id, array $data): bool
{
    if (!empty($data['reponse_secrete'])) {
        $stmt = $conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, pseudo = ?, email = ?, question_secrete = ?, reponse_secrete = ? WHERE id_utilisateur = ?");
        return $stmt->execute([$data['nom'], $data['prenom'], $data['pseudo'], $data['email'], $data['question_secrete'], $data['reponse_secrete'], $id]);
    } else {
        $stmt = $conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, pseudo = ?, email = ?, question_secrete = ? WHERE id_utilisateur = ?");
        return $stmt->execute([$data['nom'], $data['prenom'], $data['pseudo'], $data['email'], $data['question_secrete'], $id]);
    }
}

/**
 * Supprime un utilisateur et réassigne ses jeux à un administrateur.
 *
 * Comportement des clés étrangères (défini en base) :
 *   - avis.id_utilisateur → ON DELETE SET NULL  (les avis sont anonymisés)
 *   - favori, token_reset  → ON DELETE CASCADE   (supprimés automatiquement)
 *   - jeu.id_utilisateur  → ON DELETE RESTRICT   (réassigné manuellement ci-dessous)
 *
 * @param PDO      $conn
 * @param int      $id       ID de l'utilisateur à supprimer.
 * @param int|null $adminId  ID de l'admin destinataire des jeux. Si null, un admin est trouvé automatiquement.
 * @return bool   False si aucun admin de secours n'est disponible.
 */
function deleteUser(PDO $conn, int $id, ?int $adminId = null): bool
{
    if ($adminId === null) {
        // Recherche d'un autre administrateur pour reprendre les jeux
        $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE id_role = 3 AND id_utilisateur <> ? ORDER BY id_utilisateur ASC LIMIT 1");
        $stmt->execute([$id]);
        $admin   = $stmt->fetch(PDO::FETCH_ASSOC);
        $adminId = $admin ? (int)$admin['id_utilisateur'] : null;
    }

    // Impossible de supprimer sans admin pour reprendre les jeux (contrainte RESTRICT)
    if ($adminId === null) {
        return false;
    }

    // Réassignation des jeux à l'admin avant suppression
    $stmt = $conn->prepare("UPDATE jeu SET id_utilisateur = ? WHERE id_utilisateur = ?");
    $stmt->execute([$adminId, $id]);

    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    return $stmt->execute([$id]);
}

/**
 * Met à jour la date de dernière connexion de l'utilisateur (appelée à chaque login réussi).
 *
 * @param PDO $conn
 * @param int $id_utilisateur
 * @return bool
 */
function updateDerniereConnexion(PDO $conn, int $id_utilisateur): bool
{
    $stmt = $conn->prepare("UPDATE utilisateur SET derniere_connexion = NOW() WHERE id_utilisateur = ?");
    return $stmt->execute([$id_utilisateur]);
}

/**
 * Met à jour le mot de passe d'un utilisateur.
 * Le mot de passe doit déjà être haché avant l'appel.
 *
 * @param PDO    $conn
 * @param int    $id_utilisateur
 * @param string $hashedPassword
 * @return bool
 */
function updatePassword(PDO $conn, int $id_utilisateur, string $hashedPassword): bool
{
    $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
    return $stmt->execute([$hashedPassword, $id_utilisateur]);
}

/**
 * Met à jour le chemin de la photo de profil d'un utilisateur.
 *
 * @param PDO    $conn
 * @param int    $id_utilisateur
 * @param string $imagePath  Chemin public (ex. /uploads/fichier.jpg)
 * @return bool
 */
function updateProfileImage(PDO $conn, int $id_utilisateur, string $imagePath): bool
{
    $stmt = $conn->prepare("UPDATE utilisateur SET photo_profil = ? WHERE id_utilisateur = ?");
    return $stmt->execute([$imagePath, $id_utilisateur]);
}

/**
 * Retourne les jeux mis en favori par un utilisateur.
 *
 * @param PDO $conn
 * @param int $id_utilisateur
 * @return array
 */
function getFavoriteGamesByUser(PDO $conn, int $id_utilisateur): array
{
    $stmt = $conn->prepare("SELECT jeu.id_jeu, jeu.titre FROM jeu JOIN favori ON jeu.id_jeu = favori.id_jeu WHERE favori.id_utilisateur = ?");
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne les jeux ajoutés par un utilisateur, avec le nombre d'avis pour chacun.
 *
 * @param PDO $conn
 * @param int $id_utilisateur
 * @return array
 */
function getAddedGamesByUser(PDO $conn, int $id_utilisateur): array
{
    $stmt = $conn->prepare(
        "SELECT j.id_jeu, j.titre, j.date_ajout, COUNT(a.id_avis) AS nb_commentaires
         FROM jeu j
         LEFT JOIN avis a ON j.id_jeu = a.id_jeu
         WHERE j.id_utilisateur = ?
         GROUP BY j.id_jeu, j.titre, j.date_ajout
         ORDER BY j.date_ajout DESC"
    );
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}