<?php
function saveToken($conn, $id_utilisateur, $token, $expiration) {
    $stmt = $conn->prepare("INSERT INTO token_reset (token, date_expiration, id_utilisateur) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $token, $expiration, $id_utilisateur);
    return $stmt->execute();
}

function getTokenData($conn, $token) {
    $stmt = $conn->prepare("SELECT * FROM token_reset WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function deleteToken($conn, $token) {
    $stmt = $conn->prepare("DELETE FROM token_reset WHERE token = ?");
    $stmt->bind_param("s", $token);
    return $stmt->execute();
}