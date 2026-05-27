<?php
/**
 * Modèle : statistiques globales
 * Fournit les compteurs affichés sur la page d'accueil et dans le tableau de bord admin.
 */

/**
 * Retourne les statistiques globales de l'application.
 *
 * @param PDO $conn
 * @return array  Clés : nb_jeux, nb_utilisateurs, nb_avis
 */
function getStats(PDO $conn): array
{
    $stats = [];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM jeu");
    $stats['nb_jeux'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as total FROM utilisateur");
    $stats['nb_utilisateurs'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as total FROM avis");
    $stats['nb_avis'] = ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])['total'] ?? 0;

    return $stats;
}