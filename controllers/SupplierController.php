<?php
require_once 'models/Supplier.php';

class SupplierController {
    private $supplier;
    
    public function __construct() {
        $this->supplier = new Supplier();
    }
    
    public function getAllSuppliers() {
        return $this->supplier->getAll();
    }
    
    public function getSupplierById($id) {
        return $this->supplier->getById($id);
    }
    
    public function createSupplier($data) {
        
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'Nama supplier harus diisi'
            ];
        }
        
        
        $result = $this->supplier->create($data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan supplier'
            ];
        }
    }
    
    public function updateSupplier($id, $data) {
        
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'Nama supplier harus diisi'
            ];
        }
        
        
        $result = $this->supplier->update($id, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Supplier berhasil diupdate'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate supplier'
            ];
        }
    }
    
    public function deleteSupplier($id) {
        $result = $this->supplier->delete($id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Supplier berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus supplier'
            ];
        }
    }
}
?>
