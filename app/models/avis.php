<?php
function getAvisByJeu($conn, $id_jeu) {
    $stmt = $conn->prepare("SELECT avis.*, utilisateur.pseudo FROM avis LEFT JOIN utilisateur ON avis.id_utilisateur = utilisateur.id_utilisateur WHERE avis.id_jeu = ? ORDER BY avis.date_avis DESC");
    $stmt->bind_param("i", $id_jeu);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAvisByUser($conn, $id_utilisateur) {
    $stmt = $conn->prepare("SELECT avis.*, jeu.titre FROM avis JOIN jeu ON avis.id_jeu = jeu.id_jeu WHERE avis.id_utilisateur = ? ORDER BY avis.date_avis DESC");
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function createAvis($conn, $id_utilisateur, $id_jeu, $commentaire, $note) {
    $stmt = $conn->prepare("INSERT INTO avis (commentaire, note, date_avis, id_utilisateur, id_jeu) VALUES (?, ?, NOW(), ?, ?)");
    $stmt->bind_param("siii", $commentaire, $note, $id_utilisateur, $id_jeu);
    return $stmt->execute();
}

function updateAvis($conn, $id_avis, $commentaire, $note) {
    $stmt = $conn->prepare("UPDATE avis SET commentaire = ?, note = ?, date_modification = NOW() WHERE id_avis = ?");
    $stmt->bind_param("sii", $commentaire, $note, $id_avis);
    return $stmt->execute();
}

function deleteAvis($conn, $id_avis) {
    $stmt = $conn->prepare("DELETE FROM avis WHERE id_avis = ?");
    $stmt->bind_param("i", $id_avis);
    return $stmt->execute();
}

function getDerniersAvis($conn) {
    $stmt = $conn->prepare("SELECT avis.*, utilisateur.pseudo, jeu.titre FROM avis LEFT JOIN utilisateur ON avis.id_utilisateur = utilisateur.id_utilisateur JOIN jeu ON avis.id_jeu = jeu.id_jeu ORDER BY avis.date_avis DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}