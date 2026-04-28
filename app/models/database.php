// models/database.php
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
        echo "✓ Connexion OK";
        return $conn;
        
    } catch (Exception $e) {
        echo "✗ Erreur : " . $e->getMessage();
        exit();
    }
}
?>