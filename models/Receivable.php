<?php
class Receivable {
    private $conn;
    private $table = 'receivables';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT r.*, c.name as customer_name 
                      FROM " . $this->table . " r
                      JOIN customers c ON r.customer_id = c.id
                      ORDER BY r.due_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT r.*, c.name as customer_name 
                      FROM " . $this->table . " r
                      JOIN customers c ON r.customer_id = c.id
                      WHERE r.id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Kesalahan: " . $e->getMessage();
            return null;
        }
    }
    
    public function getByCustomerId($customerId) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE customer_id = :customer_id ORDER BY due_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customerId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Kesalahan: " . $e->getMessage();
            return [];
        }
    }
    
    public function getTotalPaidByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT SUM(amount) as total FROM " . $this->table . " WHERE payment_date BETWEEN :start_date AND :end_date AND status = 'paid'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            echo "Kesalahan: " . $e->getMessage();
            return 0;
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (customer_id, amount, date, due_date, status, notes) VALUES (:customer_id, :amount, :date, :due_date, :status, :notes)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $data['customer_id']);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':date', $data['date']);
            $stmt->bindParam(':due_date', $data['due_date']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            echo "Kesalahan: " . $e->getMessage();
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET amount = :amount, due_date = :due_date, status = :status, notes = :notes";
            
            // Jika status berubah menjadi 'paid' atau 'Sebagian', tambahkan payment_date
            if ($data['status'] == 'paid' || $data['status'] == 'Sebagian') {
                $query .= ", payment_date = :payment_date";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':due_date', $data['due_date']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if ($data['status'] == 'paid' || $data['status'] == 'Sebagian') {
                $paymentDate = date('Y-m-d H:i:s');
                $stmt->bindParam(':payment_date', $paymentDate);
            }
            
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Kesalahan: " . $e->getMessage();
            return false;
        }
    }

    public function updateStatus($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status";
        
            // Jika status berubah menjadi 'paid' atau 'Sebagian', tambahkan payment_date
            if ($data['status'] == 'paid' || $data['status'] == 'Sebagian') {
                $query .= ", payment_date = :payment_date";
            }
        
            $query .= " WHERE id = :id";
        
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $data['status']);
        
            if ($data['status'] == 'paid' || $data['status'] == 'Sebagian') {
                $paymentDate = date('Y-m-d H:i:s');
                $stmt->bindParam(':payment_date', $paymentDate);
            }
        
            $stmt->bindParam(':id', $id);
            
            // Log the query and parameters for debugging
            error_log("Query: " . $query);
            error_log("Status: " . $data['status']);
            error_log("ID: " . $id);
        
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo "Kesalahan: " . $e->getMessage();
            return false;
        }
    }
}
?>
