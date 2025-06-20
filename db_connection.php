<?php
// db_connection.php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = Config::getInstance();
        
        $this->connection = new mysqli(
            $config->get('db.host'),
            $config->get('db.username'),
            $config->get('db.password'),
            $config->get('db.database')
        );

        if ($this->connection->connect_error) {
            error_log("Database connection failed: " . $this->connection->connect_error);
            throw new Exception("Database connection failed");
        }

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($query) {
        return $this->connection->prepare($query);
    }

    public function query($query) {
        return $this->connection->query($query);
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
}

// Initialize database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log($e->getMessage());
    die("A system error has occurred. Please try again later.");
}
