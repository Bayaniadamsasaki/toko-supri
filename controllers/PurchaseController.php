<?php
require_once 'models/Purchase.php';
require_once 'models/PurchaseDetail.php';
require_once 'models/Product.php';
require_once 'models/Supplier.php';
require_once 'models/Journal.php';

class PurchaseController {
    private $purchase;
    private $purchaseDetail;
    private $product;
    private $supplier;
    
    public function __construct() {
        $this->purchase = new Purchase();
        $this->purchaseDetail = new PurchaseDetail();
        $this->product = new Product();
        $this->supplier = new Supplier();
    }
    
    public function getAllPurchases() {
        return $this->purchase->getAll();
    }
    
    public function getPurchaseById($id) {
        $purchase = $this->purchase->getById($id);
        $purchase['details'] = $this->purchaseDetail->getByPurchaseId($id);
        
        return $purchase;
    }
    
    public function createPurchase($data) {
        try {
            // Validasi data
            if (empty($data['supplier_id']) || empty($data['items']) || !is_array($data['items'])) {
                return [
                    'success' => false,
                    'message' => 'Data pembelian tidak valid'
                ];
            }
            
            // Validasi supplier dan produk terlebih dahulu
            $supplier = $this->supplier->getById($data['supplier_id']);
            if (!$supplier) {
                return [
                    'success' => false,
                    'message' => 'Supplier tidak ditemukan'
                ];
            }
            
            foreach ($data['items'] as $item) {
                if (empty($item['product_id']) || empty($item['quantity']) || empty($item['price'])) {
                    return [
                        'success' => false,
                        'message' => 'Data item pembelian tidak lengkap'
                    ];
                }
                
                $product = $this->product->getById($item['product_id']);
                if (!$product) {
                    return [
                        'success' => false,
                        'message' => 'Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan'
                    ];
                }
            }
            
            // Mulai transaksi
            if (!$this->purchase->beginTransaction()) {
                throw new Exception('Gagal memulai transaksi');
            }
            
            try {
                // Hitung total
                $total = 0;
                foreach ($data['items'] as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                // Buat data pembelian
                $purchaseData = [
                    'date' => date('Y-m-d H:i:s'),
                    'supplier_id' => $data['supplier_id'],
                    'total' => $total,
                    'status' => $data['status'] ?? 'completed'
                ];
                
                // Simpan pembelian
                $purchaseId = $this->purchase->create($purchaseData);
                
                if (!$purchaseId) {
                    throw new Exception('Gagal menyimpan data pembelian');
                }
                
                // Simpan detail pembelian dan update stok
                foreach ($data['items'] as $item) {
                    // Simpan detail pembelian
                    $detailData = [
                        'purchase_id' => $purchaseId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ];
                    
                    $detailId = $this->purchaseDetail->create($detailData);
                    
                    if (!$detailId) {
                        throw new Exception('Gagal menyimpan detail pembelian');
                    }
                    
                    // Update stok produk
                    if (!$this->product->updateStock($item['product_id'], $item['quantity'])) {
                        throw new Exception('Gagal mengupdate stok produk');
                    }
                }
                
                // Commit transaksi
                if (!$this->purchase->commit()) {
                    throw new Exception('Gagal menyelesaikan transaksi');
                }
                
                // === OTOMATISASI JURNAL ===
                $journal = new Journal();
                $desc = 'Pembelian #' . $purchaseId . ' - ' . $supplier['name'];
                $details = [];
                $details[] = [ 'account_code' => '501', 'position' => 'debit', 'amount' => $total ]; // Pembelian
                $details[] = [ 'account_code' => '101', 'position' => 'kredit', 'amount' => $total ]; // Kas
                $journal->create(date('Y-m-d H:i:s'), $desc, $details);
                // === END JURNAL ===
                
                return [
                    'success' => true,
                    'message' => 'Pembelian berhasil disimpan',
                    'purchase_id' => $purchaseId
                ];
                
            } catch (Exception $e) {
                // Rollback transaksi jika terjadi error
                $this->purchase->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getPurchasesReport($startDate, $endDate) {
        try {
            $query = "SELECT p.*, s.name as supplier_name,
                        DATE_FORMAT(p.date, '%Y-%m-%d %H:%i:%s') as formatted_date
                      FROM purchases p
                      JOIN suppliers s ON p.supplier_id = s.id
                      WHERE p.date BETWEEN :start_date AND :end_date
                      ORDER BY p.date DESC";
            
            $stmt = $this->purchase->getConnection()->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting purchases report: " . $e->getMessage());
            return [];
        }
    }
}
?>
