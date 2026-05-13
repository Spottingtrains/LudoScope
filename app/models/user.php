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