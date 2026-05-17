<?php

function getStats($conn) {
    $stats = [];
    $stmt = $conn->query("SELECT COUNT(*) as total FROM jeu");
    $stats['nb_jeux'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as total FROM utilisateur");
    $stats['nb_utilisateurs'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as total FROM avis");
    $stats['nb_avis'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;
    
    return $stats;
}