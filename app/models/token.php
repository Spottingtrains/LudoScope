<?php
function saveToken($conn, $id_utilisateur, $token, $expiration) {
    $stmt = $conn->prepare("INSERT INTO token_reset (token, date_expiration, id_utilisateur) VALUES (?, ?, ?)");
    return $stmt->execute([$token, $expiration, $id_utilisateur]);
}

function getTokenData($conn, $token) {
    $stmt = $conn->prepare("SELECT * FROM token_reset WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function deleteToken($conn, $token) {
    $stmt = $conn->prepare("DELETE FROM token_reset WHERE token = ?");
    return $stmt->execute([$token]);
}