<?php
class Customer {
    private $conn;
    private $table = 'customers';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
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
            $query = "INSERT INTO " . $this->table . " (name, phone, address) VALUES (:name, :phone, :address)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            
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
            $query = "UPDATE " . $this->table . " SET name = :name, phone = :phone, address = :address WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
