<?php
/**
 * Modèle : tokens de réinitialisation de mot de passe
 * Gère les tokens temporaires envoyés par email pour la réinitialisation sécurisée.
 * Chaque token est à usage unique et a une durée de validité limitée.
 */

/**
 * Enregistre un token de réinitialisation en base.
 *
 * @param PDO    $conn
 * @param int    $id_utilisateur
 * @param string $token       Token aléatoire généré (bin2hex 32 octets).
 * @param string $expiration  Date d'expiration au format Y-m-d H:i:s.
 * @return bool
 */
function saveToken(PDO $conn, int $id_utilisateur, string $token, string $expiration): bool
{
    $stmt = $conn->prepare("INSERT INTO token_reset (token, date_expiration, id_utilisateur) VALUES (?, ?, ?)");
    return $stmt->execute([$token, $expiration, $id_utilisateur]);
}

/**
 * Récupère les données d'un token (utilisateur associé et date d'expiration).
 * Le contrôleur est responsable de vérifier si le token est expiré.
 *
 * @param PDO    $conn
 * @param string $token
 * @return array|null  Null si le token n'existe pas.
 */
function getTokenData(PDO $conn, string $token): ?array
{
    $stmt = $conn->prepare("SELECT * FROM token_reset WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Supprime un token après utilisation (usage unique).
 *
 * @param PDO    $conn
 * @param string $token
 * @return bool
 */
function deleteToken(PDO $conn, string $token): bool
{
    $stmt = $conn->prepare("DELETE FROM token_reset WHERE token = ?");
    return $stmt->execute([$token]);
}