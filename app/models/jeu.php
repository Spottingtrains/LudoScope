<?php
function getAllJeux($conn) {
    $stmt = $conn->prepare("SELECT * FROM jeu ORDER BY date_ajout DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getJeuById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM jeu WHERE id_jeu = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createJeu($conn, $id_utilisateur, $data) {
    $stmt = $conn->prepare("INSERT INTO jeu (titre, description, nb_joueurs_min, nb_joueurs_max, age_min, age_max, duree_partie, complexite, image, date_ajout, id_utilisateur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssiiiiiisi", $data['titre'], $data['description'], $data['nb_joueurs_min'], $data['nb_joueurs_max'], $data['age_min'], $data['age_max'], $data['duree_partie'], $data['complexite'], $data['image'], $id_utilisateur);
    return $stmt->execute();
}

function getDerniersJeux($conn) {
    $stmt = $conn->prepare("SELECT * FROM jeu ORDER BY date_ajout DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDemandesEnAttente($conn) {
    $stmt = $conn->prepare("SELECT * FROM demande WHERE statut = 'en_attente' ORDER BY date_demande DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}