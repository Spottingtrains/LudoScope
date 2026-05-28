<?php
/**
 * Modèle : connexion à la base de données
 * Fournit une connexion PDO partagée par tous les modèles.
 * Les paramètres sont lus depuis les variables d'environnement (fichier .env, non versionné).
 */

/**
 * Retourne une connexion PDO active à la base de données MySQL.
 * Arrête l'exécution si la connexion échoue.
 *
 * @return PDO
 */
function connect(): PDO
{
    try {
        $host     = $GLOBALS['dotenv']['DB_HOST'];
        $user     = $GLOBALS['dotenv']['DB_USER'];
        $password = $GLOBALS['dotenv']['DB_PASSWORD'];
        $dbname   = $GLOBALS['dotenv']['DB_NAME'];

        $dsn     = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lève une exception en cas d'erreur SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats en tableaux associatifs
        ];

        $pdo = new PDO($dsn, $user, $password, $options);

        // Désactive l'émulation des requêtes préparées pour une vraie protection contre les injections SQL
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;

    } catch (Exception $e) {
        die("Erreur de connexion à la base de données.");
    }
}