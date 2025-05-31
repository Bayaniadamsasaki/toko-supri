<?php
class Sale {
    private $conn;
    private $table = 'sales';
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
            
            
            $this->conn->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
            return $this->conn->beginTransaction();
        } catch (PDOException $e) {
            
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
            $query = "SELECT * FROM " . $this->table . " ORDER BY date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting sales: " . $e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting sale: " . $e->getMessage());
        }
    }
    
    public function getByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE date BETWEEN :start_date AND :end_date ORDER BY date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting sales by date range: " . $e->getMessage());
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
            $query = "SELECT SUM(total) as total FROM " . $this->table . " WHERE date BETWEEN :start_date AND :end_date AND payment_method = 'cash'";
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
            $query = "INSERT INTO " . $this->table . " (date, customer_name, total, payment_method, status) VALUES (:date, :customer_name, :total, :payment_method, :status)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $data['date']);
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':status', $data['status']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute insert query for sale");
            }
            
            $saleId = $this->conn->lastInsertId();
            
            
            if (!$saleId) {
                throw new Exception("Failed to get a valid lastInsertId after inserting sale");
            }
            
            
            $sale = $this->getById($saleId);
            if (!$sale) {
                
                
                
                
                throw new Exception("Verification failed: Sale record with ID " . $saleId . " not found after insertion.");
            }
            
            return $saleId;
        } catch (PDOException $e) {
            
            throw new Exception("Database error creating sale: " . $e->getMessage());
        } catch (Exception $e) {
            
            throw $e;
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET customer_name = :customer_name, total = :total, payment_method = :payment_method, status = :status WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':total', $data['total']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':id', $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update sale record");
            }
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error updating sale: " . $e->getMessage());
        }
    }
}
?>
