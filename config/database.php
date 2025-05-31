<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'toko_sembako_supri';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_TIMEOUT => 60, 
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_PERSISTENT => true, 
            ];
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database . ";charset=utf8",
                $this->username,
                $this->password,
                $options
            );
            
            // Set timezone di database
            $this->conn->exec("SET time_zone = '+07:00'");
            
            $this->conn->exec("SET SESSION innodb_lock_wait_timeout = 50"); 
            $this->conn->exec("SET SESSION wait_timeout = 60"); 
            $this->conn->exec("SET SESSION interactive_timeout = 60"); 
            
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        if ($this->conn) {
            $this->conn = null;
        }
    }
}
?>
