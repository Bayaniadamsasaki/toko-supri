<?php
class Product {
    private $conn;
    private $table = 'products';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT p.*, s.name as supplier_name 
                      FROM " . $this->table . " p
                      LEFT JOIN suppliers s ON p.supplier_id = s.id
                      ORDER BY p.id DESC";
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
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (name, price, stock, supplier_id, code, category) VALUES (:name, :price, :stock, :supplier_id, :code, :category)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':stock', $data['stock']);
            $stmt->bindParam(':supplier_id', $data['supplier_id']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':category', $data['category']);
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET name = :name, price = :price, stock = :stock, supplier_id = :supplier_id, code = :code, category = :category WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':stock', $data['stock']);
            $stmt->bindParam(':supplier_id', $data['supplier_id']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':category', $data['category']);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    public function updateStock($id, $quantity_change) {
        try {
            
            
            
            $query = "UPDATE " . $this->table . " SET stock = stock + :quantity_change WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity_change', $quantity_change);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error updating stock: " . $e->getMessage();
            return false;
        }
    }
    
    public function delete($id) {
        try {
            // Cek apakah produk masih digunakan di sale_details
            $checkQuery = "SELECT COUNT(*) as count FROM sale_details WHERE product_id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                throw new Exception("Produk tidak dapat dihapus karena masih digunakan dalam data penjualan. Silakan hapus data penjualan terkait terlebih dahulu.");
            }
            // Cek apakah produk masih digunakan di purchase_details
            $checkQuery2 = "SELECT COUNT(*) as count FROM purchase_details WHERE product_id = :id";
            $checkStmt2 = $this->conn->prepare($checkQuery2);
            $checkStmt2->bindParam(':id', $id);
            $checkStmt2->execute();
            $result2 = $checkStmt2->fetch(PDO::FETCH_ASSOC);
            if ($result2['count'] > 0) {
                throw new Exception("Produk tidak dapat dihapus karena masih digunakan dalam data pembelian. Silakan hapus data pembelian terkait terlebih dahulu.");
            }
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error menghapus produk: " . $e->getMessage());
        }
    }
    
    public function getTotalInventoryValue($date) {
        try {
            $query = "SELECT SUM(stock * price) as total_value FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_value'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Error getting total inventory value: " . $e->getMessage());
        }
    }
}
?>
