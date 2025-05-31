<?php
require_once 'models/Sale.php';
require_once 'models/SaleDetail.php';
require_once 'models/Product.php';
require_once 'models/Journal.php';

class SalesController {
    private $sale;
    private $saleDetail;
    private $product;
    
    public function __construct() {
        $this->sale = new Sale();
        $this->saleDetail = new SaleDetail();
        $this->product = new Product();
    }
    
    public function getAllSales() {
        return $this->sale->getAll();
    }
    
    public function getSaleById($id) {
        $sale = $this->sale->getById($id);
        $sale['details'] = $this->saleDetail->getBySaleId($id);
        
        return $sale;
    }
    
    public function createSale($data) {
        
        if (empty($data['customer_name']) || empty($data['items']) || !is_array($data['items'])) {
            return [
                'success' => false,
                'message' => 'Data penjualan tidak valid'
            ];
        }
        
        
        foreach ($data['items'] as $item) {
            $product = $this->product->getById($item['product_id']);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan'
                ];
            }
            if ($product['stock'] < $item['quantity']) {
                return [
                    'success' => false,
                    'message' => 'Stok produk ' . $product['name'] . ' tidak mencukupi. Stok tersedia: ' . $product['stock']
                ];
            }
        }
        
        try {
            
            if (!$this->sale->beginTransaction()) {
                throw new Exception('Gagal memulai transaksi');
            }
            
            try {
                
                $total = 0;
                foreach ($data['items'] as $item) {
                    $product = $this->product->getById($item['product_id']);
                    $total += $product['price'] * $item['quantity'];
                }
                
                
                $saleData = [
                    'date' => date('Y-m-d H:i:s'),
                    'customer_name' => $data['customer_name'],
                    'total' => $total,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'status' => $data['status'] ?? 'completed'
                ];
                
                
                $saleId = $this->sale->create($saleData);
                
                if (!$saleId) {
                    throw new Exception('Gagal menyimpan data penjualan');
                }
                
                
                foreach ($data['items'] as $item) {
                    $product = $this->product->getById($item['product_id']);
                    
                    
                    $detailData = [
                        'sale_id' => $saleId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product['price']
                    ];
                    
                    $detailId = $this->saleDetail->create($detailData);
                    
                    if (!$detailId) {
                        throw new Exception('Gagal menyimpan detail penjualan untuk produk: ' . $product['name']);
                    }
                    
                    
                    $quantity_change = -($item['quantity']);
                    if (!$this->product->updateStock($item['product_id'], $quantity_change)) {
                        throw new Exception('Gagal mengupdate stok produk: ' . $product['name']);
                    }
                }
                
                
                if (!$this->sale->commit()) {
                    throw new Exception('Gagal menyelesaikan transaksi');
                }
                
                // === OTOMATISASI JURNAL ===
                $journal = new Journal();
                $desc = 'Penjualan #' . $saleId . ' - ' . $data['customer_name'];
                $details = [];
                if (($data['payment_method'] ?? 'cash') == 'cash') {
                    $details[] = [ 'account_code' => '101', 'position' => 'debit', 'amount' => $total ]; // Kas
                } else {
                    $details[] = [ 'account_code' => '102', 'position' => 'debit', 'amount' => $total ]; // Piutang Usaha
                }
                $details[] = [ 'account_code' => '401', 'position' => 'kredit', 'amount' => $total ]; // Penjualan
                $journal->create(date('Y-m-d H:i:s'), $desc, $details);
                // === END JURNAL ===
                
                return [
                    'success' => true,
                    'message' => 'Penjualan berhasil disimpan',
                    'sale_id' => $saleId
                ];
                
            } catch (Exception $e) {
                
                $this->sale->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function generateReceipt($saleId) {
        $sale = $this->getSaleById($saleId);
        
        if (!$sale) {
            return [
                'success' => false,
                'message' => 'Data penjualan tidak ditemukan'
            ];
        }
        
        $date = new DateTime($sale['date']);
        $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
        
        $receipt = [
            'sale' => $sale,
            'details' => $sale['details'],
            'date' => $date->format('d/m/Y H:i'),
            'store_name' => 'Toko Sembako Supri',
            'address' => 'Jalan Tanjung Baru, RT3/RW1, Desa Tambusai Barat',
            'phone' => '-'
        ];
        
        return [
            'success' => true,
            'data' => $receipt
        ];
    }

    public function getSalesReport($startDate, $endDate) {
        try {
            $query = "SELECT s.*, 
                        DATE_FORMAT(s.date, '%Y-%m-%d %H:%i:%s') as formatted_date
                      FROM sales s
                      WHERE s.date BETWEEN :start_date AND :end_date
                      ORDER BY s.date DESC";
            
            $stmt = $this->sale->getConnection()->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting sales report: " . $e->getMessage());
            return [];
        }
    }
}
?>
