<?php
class Purchase {
    private $conn;
    private $table = 'purchases';
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        $this->reconnect();
    }
    
    private function reconnect() {
        try {
            $this->conn = $this->database->getConnection();
            if (!$this->conn) {
                throw new Exception("Database connection failed");
            }
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        }
    }

    public function beginTransaction() {
        try {
            if (!$this->conn) {
                $this->reconnect();
            }
            
            // Set transaction isolation level
            $this->conn->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
            return $this->conn->beginTransaction();
        } catch (PDOException $e) {
            // Try to reconnect and retry once
            $this->reconnect();
            return $this->conn->beginTransaction();
        }
    }
    
    public function commit() {
        try {
            if (!$this->conn) {
                $this->reconnect();
            }
            return $this->conn->commit();
        } catch (PDOException $e) {
            $this->rollback();
            throw new Exception("Failed to commit transaction: " . $e->getMessage());
        }
    }
    
    public function rollback() {
        try {
            if (!$this->conn) {
                $this->reconnect();
            }
            return $this->conn->rollBack();
        } catch (PDOException $e) {
            throw new Exception("Failed to rollback transaction: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function getAll() {
        try {
            $query = "SELECT p.*, s.name as supplier_name
                      FROM " . $this->table . " p
                      JOIN suppliers s ON p.supplier_id = s.id
                      ORDER BY p.date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting purchases: " . $e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT p.*, s.name as supplier_name 
                      FROM " . $this->table . " p
                      JOIN suppliers s ON p.supplier_id = s.id
                      WHERE p.id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting purchase: " . $e->getMessage());
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT p.*, s.name as supplier_name 
                      FROM " . $this->table . " p
                      JOIN suppliers s ON p.supplier_id = s.id
                      WHERE p.date BETWEEN :start_date AND :end_date 
                      ORDER BY p.date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting purchases by date range: " . $e->getMessage());
        }
    }
    
    public function getTotalByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT SUM(total) as total FROM " . $this->table . " WHERE date BETWEEN :start_date AND :end_date";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Error getting total by date range: " . $e->getMessage());
        }
    }
    
    public function getTotalCashByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT SUM(total) as total FROM " . $this->table . " WHERE date BETWEEN :start_date AND :end_date AND status = 'completed'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Error getting total cash by date range: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (date, supplier_id, total, status) VALUES (:date, :supplier_id, :total, :status)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $data['date']);
            $stmt->bindParam(':supplier_id', $data['supplier_id']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            throw new Exception("Failed to create purchase record");
        } catch (PDOException $e) {
            throw new Exception("Error creating purchase: " . $e->getMessage());
        }
    }
}
?>
