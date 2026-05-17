<?php
function getUserByEmail($conn, $email) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getUserByPseudo($conn, $pseudo) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE pseudo = ?");
    $stmt->bind_param("s", $pseudo);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createUser($conn, $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription) {
    $stmt = $conn->prepare("INSERT INTO utilisateur (prenom, nom, pseudo, email, mot_de_passe, id_role, date_inscription) VALUES (?, ?, ?, ?, ?, 2, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $pseudo, $email, $hashedPassword, $date_inscription);
    return $stmt->execute();
}

function getAllUsers($conn) {
    $stmt = $conn->prepare("SELECT * FROM utilisateur ORDER BY date_inscription DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateUser($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, pseudo = ?, email = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("ssssi", $data['nom'], $data['prenom'], $data['pseudo'], $data['email'], $id);
    return $stmt->execute();
}

function deleteUser($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function updateDerniereConnexion($conn, $id_utilisateur) {
    $stmt = $conn->prepare("UPDATE utilisateur SET derniere_connexion = NOW() WHERE id_utilisateur = ?");
    $stmt->bind_param("i", $id_utilisateur);
    return $stmt->execute();
}

function updatePassword($conn, $id_utilisateur, $hashedPassword) {
    $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("si", $hashedPassword, $id_utilisateur);
    return $stmt->execute();
}

function updateProfileImage($conn, $id_utilisateur, $imagePath) {
    $stmt = $conn->prepare("UPDATE utilisateur SET photo_profil = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("si", $imagePath, $id_utilisateur);
    return $stmt->execute();
}

function getFavoriteGamesByUser($conn, $id_utilisateur) {
    $stmt = $conn->prepare("SELECT jeu.id_jeu, jeu.titre FROM jeu JOIN favori ON jeu.id_jeu = favori.id_jeu WHERE favori.id_utilisateur = ?");
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAddedReviewsByUser($conn, $id_utilisateur) {
    $stmt = $conn->prepare("SELECT avis.id_avis, avis.commentaire, avis.note, avis.date_avis, jeu.titre FROM avis JOIN jeu ON avis.id_jeu = jeu.id_jeu WHERE avis.id_utilisateur = ?");
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}