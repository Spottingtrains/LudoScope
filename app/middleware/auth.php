<?php
/**
 * Middleware : contrôle d'accès par rôle.
 * À appeler en début de chaque fonction de contrôleur protégée.
 *
 * Rôles disponibles :
 *   1 = Visiteur (non connecté)
 *   2 = Utilisateur connecté
 *   3 = Administrateur
 */

/**
 * Retourne le rôle de l'utilisateur actuellement en session.
 *
 * @return int|null  L'id_role de l'utilisateur, ou null si non connecté.
 */
function check_role(): ?int
{
    return isset($_SESSION['id_role']) ? $_SESSION['id_role'] : null;
}

/**
 * Vérifie que l'utilisateur connecté possède le rôle minimum requis.
 * Affiche une page 403 et stoppe l'exécution si le droit est insuffisant.
 *
 * @param int $roleMin  Niveau minimum requis (1, 2 ou 3).
 */
function checkRole(int $roleMin): void
{
    if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] < $roleMin) {
        http_response_code(403);
        include __DIR__ . '/../views/404.php';
        exit();
    }
}