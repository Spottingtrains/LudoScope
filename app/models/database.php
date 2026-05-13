<?php
function connect() {
    try {
        $host = $GLOBALS['dotenv']['DB_HOST'];
        $user = $GLOBALS['dotenv']['DB_USER'];
        $password = $GLOBALS['dotenv']['DB_PASSWORD'];
        $dbname = $GLOBALS['dotenv']['DB_NAME'];

        $conn = new mysqli($host, $user, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Erreur : " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        // TODO: DEV ONLY - à supprimer avant livraison - affiche un message de succès et les détails de la connexion pour vérifier que tout fonctionne correctement
        // echo "✓ Connexion OK";
        // var_dump($conn);
        return $conn;
        
    } catch (Exception $e) {
        echo "✗ Erreur : " . $e->getMessage();
        exit();
    }
}
?>