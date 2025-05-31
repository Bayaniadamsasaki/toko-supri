<?php


class SaleDetail {
    private $conn;
    private $table = 'sale_details';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getBySaleId($saleId) {
        try {
            $query = "SELECT sd.*, p.name as product_name 
                      FROM " . $this->table . " sd
                      JOIN products p ON sd.product_id = p.id
                      WHERE sd.sale_id = :sale_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sale_id', $saleId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting sale details: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            
            
            
            
            $query = "INSERT INTO " . $this->table . " (sale_id, product_id, quantity, price) VALUES (:sale_id, :product_id, :quantity, :price)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sale_id', $data['sale_id']);
            $stmt->bindParam(':product_id', $data['product_id']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $stmt->bindParam(':price', $data['price']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create sale detail record");
            }
            
            $detailId = $this->conn->lastInsertId();
            
            
            if (!$detailId) {
                throw new Exception("Failed to get valid sale detail ID");
            }
            
            return $detailId;
        } catch (PDOException $e) {
            throw new Exception("Error creating sale detail: " . $e->getMessage());
        }
    }
}
?>
