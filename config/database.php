<?php
/**
 * Datenbank-Konfiguration
 * Passen Sie diese Werte an Ihre MySQL-Konfiguration an
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'geex_dashboard');

class Database {
    private static $instance = null;
    private $connection = null;
    private $connected = false;
    
    private function __construct() {
        // Keine sofortige Verbindung - erst bei Bedarf
    }
    
    private function connect() {
        if ($this->connected) {
            return;
        }
        
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            $this->connected = true;
        } catch(PDOException $e) {
            throw new Exception("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        $this->connect();
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $this->connect();
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
?>