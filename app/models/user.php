<?php
function getUserByEmail($conn, $email) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUserByPseudo($conn, $pseudo) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function createUser($conn, $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription, $id_role = 2) {
    $stmt = $conn->prepare("INSERT INTO utilisateur (prenom, nom, pseudo, email, mot_de_passe, id_role, date_inscription) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$firstname, $lastname, $pseudo, $email, $hashedPassword, $id_role, $date_inscription]);
}

function getAllUsers($conn) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur ORDER BY date_inscription DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateUser($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, pseudo = ?, email = ? WHERE id_utilisateur = ?");
    return $stmt->execute([$data['nom'], $data['prenom'], $data['pseudo'], $data['email'], $id]);
}

function deleteUser($conn, $id, $adminId = null) {
    if ($adminId === null) {
        $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE id_role = 3 AND id_utilisateur <> ? ORDER BY id_utilisateur ASC LIMIT 1");
        $stmt->execute([(int)$id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $adminId = $admin ? (int)$admin['id_utilisateur'] : null;
    }

    if ($adminId === null) {
        return false;
    }

    // 1. Réassigner les jeux de l'utilisateur supprimé à l'admin
    $stmt = $conn->prepare("UPDATE jeu SET id_utilisateur = ? WHERE id_utilisateur = ?");
    $stmt->execute([$adminId, $id]);

    // 2. Supprimer l'utilisateur
    // → MySQL passe automatiquement avis.id_utilisateur à NULL (ON DELETE SET NULL)
    // → Les favoris et tokens sont supprimés automatiquement (ON DELETE CASCADE)
    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    return $stmt->execute([$id]);
}

function updateDerniereConnexion($conn, $id_utilisateur) {
    $stmt = $conn->prepare("UPDATE utilisateur SET derniere_connexion = NOW() WHERE id_utilisateur = ?");
    return $stmt->execute([$id_utilisateur]);
}

function updatePassword($conn, $id_utilisateur, $hashedPassword) {
    $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
    return $stmt->execute([$hashedPassword, $id_utilisateur]);
}

function updateProfileImage($conn, $id_utilisateur, $imagePath) {
    $stmt = $conn->prepare("UPDATE utilisateur SET photo_profil = ? WHERE id_utilisateur = ?");
    return $stmt->execute([$imagePath, $id_utilisateur]);
}

function getFavoriteGamesByUser($conn, $id_utilisateur) {
    $stmt = $conn->prepare("SELECT jeu.id_jeu, jeu.titre FROM jeu JOIN favori ON jeu.id_jeu = favori.id_jeu WHERE favori.id_utilisateur = ?");
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAddedGamesByUser($conn, $id_utilisateur) {
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