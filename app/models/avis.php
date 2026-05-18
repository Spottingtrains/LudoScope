<?php
function getAvisByUser($conn, $id_utilisateur) {
    $stmt = $conn->prepare("SELECT avis.*, jeu.titre FROM avis JOIN jeu ON avis.id_jeu = jeu.id_jeu WHERE avis.id_utilisateur = ? ORDER BY avis.date_avis DESC");
    $stmt->execute([$id_utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function hasAvisFromUser($conn, $id_utilisateur, $id_jeu) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM avis WHERE id_utilisateur = ? AND id_jeu = ?");
    $stmt->execute([$id_utilisateur, $id_jeu]);
    return $stmt->fetchColumn() > 0;
}

function createAvis($conn, $id_utilisateur, $id_jeu, $commentaire, $note) {
    $stmt = $conn->prepare("INSERT INTO avis (commentaire, note, date_avis, id_utilisateur, id_jeu) VALUES (?, ?, NOW(), ?, ?)");
    return $stmt->execute([$commentaire, $note, $id_utilisateur, $id_jeu]);
}

function updateAvis($conn, $id_avis, $commentaire, $note) {
    $stmt = $conn->prepare("UPDATE avis SET commentaire = ?, note = ?, date_modification = NOW() WHERE id_avis = ?");
    return $stmt->execute([$commentaire, $note, $id_avis]);
}

function deleteAvis($conn, $id_avis) {
    $stmt = $conn->prepare("DELETE FROM avis WHERE id_avis = ?");
    return $stmt->execute([$id_avis]);
}

function getDerniersAvis($conn) {
    $stmt = $conn->prepare("SELECT avis.*, utilisateur.pseudo, jeu.titre FROM avis LEFT JOIN utilisateur ON avis.id_utilisateur = utilisateur.id_utilisateur JOIN jeu ON avis.id_jeu = jeu.id_jeu ORDER BY avis.date_avis DESC LIMIT 5");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}