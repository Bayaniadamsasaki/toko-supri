<?php
require_once 'controllers/ProductController.php';
require_once 'controllers/SalesController.php';
require_once 'controllers/PurchaseController.php';
require_once 'controllers/ReportController.php';

$productController = new ProductController();
$salesController = new SalesController();
$purchaseController = new PurchaseController();
$reportController = new ReportController();


error_log("Loading dashboard data...");


$products = $productController->getAllProducts();
error_log("Total products: " . count($products));

$lowStockProducts = array_filter($products, function($product) {
    return $product['stock'] <= 5; 
});
error_log("Low stock products: " . count($lowStockProducts));


$today = date('Y-m-d');
error_log("Getting sales for date: " . $today);
$todaySales = $salesController->getSalesReport($today . ' 00:00:00', $today . ' 23:59:59');
error_log("Today's sales count: " . count($todaySales));
$todaySalesTotal = array_sum(array_column($todaySales, 'total'));
error_log("Today's sales total: " . $todaySalesTotal);


error_log("Getting purchases for date: " . $today);
$todayPurchases = $purchaseController->getPurchasesReport($today . ' 00:00:00', $today . ' 23:59:59');
error_log("Today's purchases count: " . count($todayPurchases));
$todayPurchasesTotal = array_sum(array_column($todayPurchases, 'total'));
error_log("Today's purchases total: " . $todayPurchasesTotal);
?>

<div class="row">
    <!-- Card Penjualan Hari Ini -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Penjualan Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($todaySalesTotal, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Pembelian Hari Ini -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Pembelian Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($todayPurchasesTotal, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Jumlah Produk -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Jumlah Produk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($products) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Produk dengan Stok Rendah -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Produk dengan Stok Rendah</h6>
            </div>
            <div class="card-body">
                <?php if (count($lowStockProducts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?= $product['name'] ?></td>
                                        <td>
                                            <span class="badge bg-danger"><?= $product['stock'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">Tidak ada produk dengan stok rendah.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Penjualan Terbaru</h6>
            </div>
            <div class="card-body">
                <?php if (count($todaySales) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($todaySales, 0, 5) as $sale): ?>
                                    <tr>
                                        <td><?= $sale['id'] ?></td>
                                        <td><?= $sale['customer_name'] ?></td>
                                        <td>Rp <?= number_format($sale['total'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($sale['status'] == 'completed'): ?>
                                                <span class="badge bg-success">Selesai</span>
                                            <?php elseif ($sale['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= $sale['status'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">Tidak ada penjualan hari ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pembelian Terbaru -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pembelian Terbaru</h6>
            </div>
            <div class="card-body">
                <?php if (count($todayPurchases) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($todayPurchases, 0, 5) as $purchase): ?>
                                    <tr>
                                        <td><?= $purchase['id'] ?></td>
                                        <td><?= $purchase['supplier_name'] ?></td>
                                        <td>Rp <?= number_format($purchase['total'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($purchase['status'] == 'completed'): ?>
                                                <span class="badge bg-success">Selesai</span>
                                            <?php elseif ($purchase['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= $purchase['status'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">Tidak ada pembelian hari ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
