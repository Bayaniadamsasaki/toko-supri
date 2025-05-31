<?php
require_once 'controllers/ReportController.php';

$reportController = new ReportController();

// Default date range (bulan ini)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Ambil data laporan
$salesReport = $reportController->getSalesReport($startDate . ' 00:00:00', $endDate . ' 23:59:59');
$purchasesReport = $reportController->getPurchasesReport($startDate . ' 00:00:00', $endDate . ' 23:59:59');
$inventoryReport = $reportController->getInventoryReport();
$receivablesReport = $reportController->getReceivablesReport();
$profitLossReport = $reportController->getProfitLossReport($startDate, $endDate);
$cashFlowReport = $reportController->getCashFlowReport($startDate, $endDate);

// Hitung total
$totalSales = array_sum(array_column($salesReport, 'total'));
$totalPurchases = array_sum(array_column($purchasesReport, 'total'));
$totalReceivables = array_sum(array_column($receivablesReport, 'amount'));
?>

<!-- Filter Tanggal -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="reports">
            <div class="row">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <button type="button" class="btn btn-success" onclick="printReport()">Cetak</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan Keuangan -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Penjualan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($totalSales, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Pembelian</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($totalPurchases, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Laba Kotor</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($profitLossReport['gross_profit'], 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Piutang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($totalReceivables, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Detail -->
<div class="row">
    <!-- Laporan Penjualan -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Laporan Penjualan</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($salesReport, 0, 10) as $sale): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($sale['date'])) ?></td>
                                    <td><?= $sale['customer_name'] ?></td>
                                    <td>Rp <?= number_format($sale['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th>Rp <?= number_format($totalSales, 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Pembelian -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Laporan Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($purchasesReport, 0, 10) as $purchase): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($purchase['date'])) ?></td>
                                    <td><?= $purchase['supplier_name'] ?></td>
                                    <td>Rp <?= number_format($purchase['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total</th>
                                <th>Rp <?= number_format($totalPurchases, 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Laporan Stok -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Laporan Stok Produk</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Nilai Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryReport as $product): ?>
                                <tr>
                                    <td><?= $product['name'] ?></td>
                                    <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td>Rp <?= number_format($product['price'] * $product['stock'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($product['stock'] <= 5): ?>
                                            <span class="badge bg-danger">Stok Rendah</span>
                                        <?php elseif ($product['stock'] <= 10): ?>
                                            <span class="badge bg-warning">Perlu Restock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Stok Aman</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#inventoryTable').DataTable();
});

function printReport() {
    window.print();
}
</script>
