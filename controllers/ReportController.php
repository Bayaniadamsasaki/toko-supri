<?php
require_once 'models/Sale.php';
require_once 'models/Purchase.php';
require_once 'models/Product.php';
require_once 'models/Receivable.php';
require_once 'models/Customer.php';

class ReportController {
    private $conn;
    private $salesModel;
    private $purchaseModel;
    private $productModel;
    private $customerModel;
    private $receivable;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->salesModel = new Sale();
        $this->purchaseModel = new Purchase();
        $this->productModel = new Product();
        $this->customerModel = new Customer();
        $this->receivable = new Receivable();
    }
    
    public function getTodaySalesTotal() {
        $today = date('Y-m-d');
        $sales = $this->salesModel->getSalesReport($today . ' 00:00:00', $today . ' 23:59:59');
        return array_sum(array_column($sales, 'total'));
    }
    
    public function getTodayPurchasesTotal() {
        $today = date('Y-m-d');
        $purchases = $this->purchaseModel->getPurchasesReport($today . ' 00:00:00', $today . ' 23:59:59');
        return array_sum(array_column($purchases, 'total'));
    }
    
    public function getTotalProducts() {
        $products = $this->productModel->getAll();
        return count($products);
    }
    
    public function getTotalCustomers() {
        $customers = $this->customerModel->getAll();
        return count($customers);
    }
    
    public function getMonthlySalesReport($startDate, $endDate) {
        $sales = $this->salesModel->getSalesReport($startDate, $endDate);
        $monthlyData = [];
        
        foreach ($sales as $sale) {
            $month = date('M Y', strtotime($sale['date']));
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = 0;
            }
            $monthlyData[$month] += $sale['total'];
        }
        
        $result = [];
        foreach ($monthlyData as $month => $total) {
            $result[] = [
                'month' => $month,
                'total' => $total
            ];
        }
        
        return $result;
    }
    
    public function getLowStockProducts() {
        $products = $this->productModel->getAll();
        return array_filter($products, function($product) {
            return $product['stock'] <= 5;
        });
    }
    
    public function getRecentSales($limit = 5) {
        $sales = $this->salesModel->getSalesReport(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
        return array_slice($sales, 0, $limit);
    }
    
    public function getRecentPurchases($limit = 5) {
        $purchases = $this->purchaseModel->getPurchasesReport(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
        return array_slice($purchases, 0, $limit);
    }
    
    public function getSalesReport($startDate, $endDate) {
        try {
            return $this->salesModel->getByDateRange($startDate, $endDate);
        } catch (Exception $e) {
            error_log("Error getting sales report: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPurchasesReport($startDate, $endDate) {
        try {
            return $this->purchaseModel->getByDateRange($startDate, $endDate);
        } catch (Exception $e) {
            error_log("Error getting purchases report: " . $e->getMessage());
            return [];
        }
    }
    
    public function getInventoryReport() {
        return $this->productModel->getAll();
    }
    
    public function getReceivablesReport() {
        return $this->receivable->getAll();
    }
    
    public function getProfitLossReport($startDate, $endDate) {
        
        $sales = $this->salesModel->getTotalByDateRange($startDate, $endDate);
        
        
        $purchases = $this->purchaseModel->getTotalByDateRange($startDate, $endDate);
        
        
        $beginningInventory = $this->productModel->getTotalInventoryValue($startDate);
        
        
        $endingInventory = $this->productModel->getTotalInventoryValue($endDate);
        
        
        $cogs = ($beginningInventory + $purchases) - $endingInventory;
        
        
        $grossProfit = $sales - $cogs;
        
        
        $operationalCost = 0; 
        
        $netProfit = $grossProfit - $operationalCost;
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_sales' => $sales,
            'total_purchases' => $purchases,
            'beginning_inventory' => $beginningInventory,
            'ending_inventory' => $endingInventory,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'operational_cost' => $operationalCost,
            'net_profit' => $netProfit
        ];
    }
    
    public function getCashFlowReport($startDate, $endDate) {
        $salesCash = $this->salesModel->getTotalCashByDateRange($startDate, $endDate);
        $purchasesCash = $this->purchaseModel->getTotalCashByDateRange($startDate, $endDate);
        $receivablesPaid = $this->receivable->getTotalPaidByDateRange($startDate, $endDate);
        
        $cashIn = $salesCash + $receivablesPaid;
        $cashOut = $purchasesCash;
        
        $netCashFlow = $cashIn - $cashOut;
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cash_in' => [
                'sales' => $salesCash,
                'receivables_paid' => $receivablesPaid,
                'total' => $cashIn
            ],
            'cash_out' => [
                'purchases' => $purchasesCash,
                'total' => $cashOut
            ],
            'net_cash_flow' => $netCashFlow
        ];
    }
}
?>
