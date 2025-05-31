<?php
require_once 'models/Receivable.php';
require_once 'models/Customer.php';
require_once 'models/Journal.php';

class ReceivableController {
    private $receivable;
    private $customer;
    
    public function __construct() {
        $this->receivable = new Receivable();
        $this->customer = new Customer();
    }
    
    public function getAllReceivables() {
        return $this->receivable->getAll();
    }
    
    public function getReceivableById($id) {
        return $this->receivable->getById($id);
    }
    
    public function getReceivablesByCustomer($customerId) {
        return $this->receivable->getByCustomerId($customerId);
    }
    
    public function createReceivable($data) {
        
        if (empty($data['customer_id']) || empty($data['amount']) || empty($data['due_date'])) {
            return [
                'success' => false,
                'message' => 'Semua field wajib harus diisi'
            ];
        }
        
        
        $result = $this->receivable->create($data);
        
        if ($result) {
            // === OTOMATISASI JURNAL ===
            $journal = new Journal();
            $customer = $this->customer->getById($data['customer_id']);
            $desc = 'Piutang baru #' . $result . ' - ' . ($customer ? $customer['name'] : '');
            $details = [];
            $details[] = [ 'account_code' => '102', 'position' => 'debit', 'amount' => $data['amount'] ]; // Piutang Usaha
            $details[] = [ 'account_code' => '401', 'position' => 'kredit', 'amount' => $data['amount'] ]; // Penjualan
            $journal->create(date('Y-m-d H:i:s'), $desc, $details);
            // === END JURNAL ===
            return [
                'success' => true,
                'message' => 'Piutang berhasil ditambahkan'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan piutang'
            ];
        }
    }
    
    public function updateReceivable($id, $data) {
        
        if (empty($data['customer_id']) || empty($data['amount']) || empty($data['due_date'])) {
            return [
                'success' => false,
                'message' => 'Semua field wajib harus diisi'
            ];
        }
        
        
        $result = $this->receivable->update($id, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Piutang berhasil diupdate'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate piutang'
            ];
        }
    }
    
    public function payReceivable($id) {
        $data = [
            'status' => 'Sebagian',
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->receivable->updateStatus($id, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Piutang berhasil ditandai sebagai sebagian dibayar'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate status piutang'
            ];
        }
    }
    
    public function getOverdueReceivables() {
        $receivables = $this->receivable->getAll();
        $overdue = [];
        
        foreach ($receivables as $receivable) {
            if ($receivable['status'] != 'paid' && strtotime($receivable['due_date']) < time()) {
                $overdue[] = $receivable;
            }
        }
        
        return $overdue;
    }
}
?>
