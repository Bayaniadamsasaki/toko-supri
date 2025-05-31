<?php
class PurchaseDetail {
    private $conn;
    private $table = 'purchase_details';
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
    
    public function getByPurchaseId($purchaseId) {
        try {
            if (!$this->conn) {
                $this->reconnect();
            }
            
            $query = "SELECT pd.*, p.name as product_name 
                      FROM " . $this->table . " pd
                      JOIN products p ON pd.product_id = p.id
                      WHERE pd.purchase_id = :purchase_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':purchase_id', $purchaseId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting purchase details: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            if (!$this->conn) {
                $this->reconnect();
            }
            
            $query = "INSERT INTO " . $this->table . " (purchase_id, product_id, quantity, price) 
                     VALUES (:purchase_id, :product_id, :quantity, :price)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':purchase_id', $data['purchase_id']);
            $stmt->bindParam(':product_id', $data['product_id']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $stmt->bindParam(':price', $data['price']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error creating purchase detail: " . $e->getMessage());
        }
    }
}
?>
