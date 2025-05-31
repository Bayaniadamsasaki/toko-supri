<?php
require_once 'models/Product.php';

class ProductController {
    private $product;
    
    public function __construct() {
        $this->product = new Product();
    }
    
    public function getAllProducts() {
        return $this->product->getAll();
    }
    
    public function getProductById($id) {
        return $this->product->getById($id);
    }
    
    public function createProduct($data) {
        // Validasi data
        if (empty($data['name']) || empty($data['price']) || !isset($data['stock'])) {
            return [
                'success' => false,
                'message' => 'Semua field harus diisi'
            ];
        }
        
        // Buat produk baru
        $result = $this->product->create($data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Produk berhasil ditambahkan'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan produk'
            ];
        }
    }
    
    public function updateProduct($id, $data) {
        // Validasi data
        if (empty($data['name']) || empty($data['price']) || !isset($data['stock'])) {
            return [
                'success' => false,
                'message' => 'Semua field harus diisi'
            ];
        }
        
        // Update produk
        $result = $this->product->update($id, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Produk berhasil diupdate'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate produk'
            ];
        }
    }
    
    public function deleteProduct($id) {
        try {
            $result = $this->product->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Produk berhasil dihapus'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus produk'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
