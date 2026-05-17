<?php
// fonction de connexion à la base de données
function connect() {
    try {
        $host = $GLOBALS['dotenv']['DB_HOST'];
        $user = $GLOBALS['dotenv']['DB_USER'];
        $password = $GLOBALS['dotenv']['DB_PASSWORD'];
        $dbname = $GLOBALS['dotenv']['DB_NAME'];

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $user, $password, $options);
        // Important: désactiver l'émulation des requêtes préparées pour une vraie sécurité
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;

    } catch (Exception $e) {
        echo "✗ Erreur : " . $e->getMessage();
        exit();
    }
}
?>