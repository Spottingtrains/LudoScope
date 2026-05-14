<?php

function getStats($conn) {
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM jeu");
    $stats['nb_jeux'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM utilisateur");
    $stats['nb_utilisateurs'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM avis");
    $stats['nb_avis'] = $result->fetch_assoc()['total'];
    
    return $stats;
}