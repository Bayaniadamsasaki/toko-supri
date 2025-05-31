<?php
require_once 'controllers/ProductController.php';
require_once 'controllers/SalesController.php';
require_once 'models/Sale.php';

$productController = new ProductController();
$salesController = new SalesController();

$products = $productController->getAllProducts();
$sales = $salesController->getAllSales();


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_sale') {
        $saleData = [
            'customer_name' => $_POST['customer_name'],
            'payment_method' => $_POST['payment_method'],
            'status' => $_POST['status'],
            'items' => []
        ];
        
        
        $productIds = $_POST['product_id'];
        $quantities = $_POST['quantity'];
        
        for ($i = 0; $i < count($productIds); $i++) {
            if (!empty($productIds[$i]) && !empty($quantities[$i])) {
                $saleData['items'][] = [
                    'product_id' => $productIds[$i],
                    'quantity' => $quantities[$i]
                ];
            }
        }
        
        $result = $salesController->createSale($saleData);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            
            $sales = $salesController->getAllSales();
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'print' && isset($_GET['id'])) {
    $saleId = $_GET['id'];
    $receipt = $salesController->generateReceipt($saleId);
    
    if ($receipt['success']) {
        
        $receiptData = $receipt['data'];
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $saleId = $_GET['id'];
    $saleDetail = $salesController->getSaleById($saleId);
}
?>

<!-- Alert Message -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Tombol Tambah Penjualan -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
        <i class="fas fa-plus"></i> Tambah Penjualan
    </button>
</div>

<!-- Tabel Penjualan -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Penjualan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Metode Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= $sale['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($sale['date'])) ?></td>
                            <td><?= $sale['customer_name'] ?></td>
                            <td>Rp <?= number_format($sale['total'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($sale['payment_method'] == 'cash'): ?>
                                    <span class="badge bg-success">Tunai</span>
                                <?php elseif ($sale['payment_method'] == 'credit'): ?>
                                    <span class="badge bg-warning">Kredit</span>
                                <?php else: ?>
                                    <span class="badge bg-info"><?= $sale['payment_method'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sale['status'] == 'completed'): ?>
                                    <span class="badge bg-success">Selesai</span>
                                <?php elseif ($sale['status'] == 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $sale['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=sales&action=view&id=<?= $sale['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Penjualan -->
<div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSaleModalLabel">Tambah Penjualan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_sale">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Nama Pelanggan</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="cash">Tunai</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <h6>Item Penjualan</h6>
                    <div id="sale_items">
                        <div class="row mb-3 sale-item">
                            <div class="col-md-6">
                                <label class="form-label">Produk</label>
                                <select class="form-select product-select" name="product_id[]" required onchange="updateSubtotal(this)">
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>" data-stock="<?= $product['stock'] ?>">
                                            <?= $product['name'] ?> (Stok: <?= $product['stock'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control quantity-input" name="quantity[]" min="1" required onchange="updateSubtotal(this)" onkeyup="updateSubtotal(this)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control subtotal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-success" id="add_item">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="total_amount" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cetak Struk -->
<?php if (isset($receiptData)): ?>
<style>
/* Style untuk preview struk di modal */
#receipt {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.3;
    max-width: 350px;
    margin: 0 auto;
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
}

.receipt-header {
    text-align: center;
    margin-bottom: 15px;
    border-bottom: 1px dashed #000;
    padding-bottom: 10px;
}

.receipt-header h2 {
    font-size: 16px;
    margin: 0 0 5px 0;
    font-weight: bold;
}

.receipt-header div {
    font-size: 11px;
    margin-bottom: 2px;
}

.receipt-info {
    margin-bottom: 15px;
    font-size: 12px;
}

.receipt-info div {
    margin-bottom: 3px;
    display: flex;
    justify-content: space-between;
}

.receipt-divider {
    border-bottom: 1px dashed #000;
    margin: 10px 0;
}

.receipt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-bottom: 10px;
}

.receipt-table th {
    text-align: left;
    padding: 5px 2px;
    border-bottom: 1px dashed #000;
    font-weight: bold;
}

.receipt-table td {
    padding: 3px 2px;
    vertical-align: top;
}

.receipt-table .text-right {
    text-align: right;
}

.receipt-table .text-center {
    text-align: center;
}

.receipt-total {
    margin-top: 10px;
    padding-top: 5px;
    border-top: 1px dashed #000;
}

.receipt-total div {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 13px;
}

.receipt-footer {
    margin-top: 15px;
    text-align: center;
    font-size: 11px;
    border-top: 1px dashed #000;
    padding-top: 10px;
}

/* Style untuk cetak */
@media print {
    @page { 
        margin: 0; 
        size: 80mm auto; 
    }
    
    body * { 
        visibility: hidden !important; 
    }
    
    #receipt, #receipt * { 
        visibility: visible !important; 
    }
    
    #receipt {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 80mm !important;
        max-width: 80mm !important;
        margin: 0 !important;
        padding: 5mm !important;
        font-family: 'Courier New', monospace !important;
        font-size: 10px !important;
        line-height: 1.2 !important;
        background: white !important;
        border: none !important;
    }
    
    .modal, .modal-dialog, .modal-content, .modal-header, .modal-footer, .btn {
        display: none !important;
    }
    
    .receipt-header h2 {
        font-size: 14px !important;
    }
    
    .receipt-table {
        font-size: 9px !important;
    }
    
    .receipt-table th,
    .receipt-table td {
        padding: 2px 1px !important;
    }
}
</style>

<div class="modal fade" id="printReceiptModal" tabindex="-1" aria-labelledby="printReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printReceiptModalLabel">Struk Penjualan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="receipt">
                    <!-- Header Toko -->
                    <div class="receipt-header">
                        <h2><?= $receiptData['store_name'] ?></h2>
                        <div><?= $receiptData['address'] ?></div>
                        <div><?= $receiptData['phone'] ?></div>
                    </div>
                    
                    <!-- Info Transaksi -->
                    <div class="receipt-info">
                        <div>
                            <span><strong>No:</strong></span>
                            <span><?= $receiptData['sale']['id'] ?></span>
                        </div>
                        <div>
                            <span><strong>Tanggal:</strong></span>
                            <span id="receipt-date"><?= $receiptData['date'] ?></span>
                        </div>
                        <div>
                            <span><strong>Pelanggan:</strong></span>
                            <span><?= $receiptData['sale']['customer_name'] ?></span>
                        </div>
                    </div>
                    
                    <div class="receipt-divider"></div>
                    
                    <!-- Tabel Item -->
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th class="text-center" style="width: 15%;">Qty</th>
                                <th class="text-right" style="width: 22%;">Harga</th>
                                <th class="text-right" style="width: 23%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receiptData['details'] as $item): ?>
                            <tr>
                                <td><?= $item['product_name'] ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-right"><?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Total -->
                    <div class="receipt-total">
                        <div>
                            <span>TOTAL:</span>
                            <span>Rp <?= number_format($receiptData['sale']['total'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="receipt-footer">
                        <p>Terima kasih telah berbelanja<br>di <?= $receiptData['store_name'] ?></p>
                        <p>Barang yang sudah dibeli<br>tidak dapat dikembalikan</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Cetak</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Detail Penjualan -->
<?php if (isset($saleDetail)): ?>
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSaleModalLabel">Detail Penjualan #<?= $saleDetail['id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($saleDetail['date'])) ?></p>
                        <p><strong>Pelanggan:</strong> <?= $saleDetail['customer_name'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php if ($saleDetail['status'] == 'completed'): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php elseif ($saleDetail['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= $saleDetail['status'] ?></span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Metode Pembayaran:</strong> 
                            <?php if ($saleDetail['payment_method'] == 'cash'): ?>
                                <span class="badge bg-success">Tunai</span>
                            <?php elseif ($saleDetail['payment_method'] == 'credit'): ?>
                                <span class="badge bg-warning">Kredit</span>
                            <?php else: ?>
                                <span class="badge bg-info"><?= $saleDetail['payment_method'] ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <h6>Item Penjualan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($saleDetail['details'] as $item): ?>
                                <tr>
                                    <td><?= $item['product_name'] ?></td>
                                    <td class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= $item['quantity'] ?></td>
                                    <td class="text-end">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">Rp <?= number_format($saleDetail['total'], 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="index.php?page=sales&action=print&id=<?= $saleDetail['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak Struk
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateSubtotal(element) {
    const row = element.closest('.sale-item');
    const select = row.querySelector('.product-select');
    const quantity = row.querySelector('.quantity-input');
    const subtotal = row.querySelector('.subtotal');
    
    if (select.value && quantity.value) {
        const price = select.options[select.selectedIndex].dataset.price;
        const stock = parseInt(select.options[select.selectedIndex].dataset.stock);
        const qty = parseInt(quantity.value);
        
        if (qty > stock) {
            alert('Jumlah melebihi stok yang tersedia!');
            quantity.value = stock;
            return;
        }
        
        const total = price * qty;
        subtotal.value = total.toLocaleString('id-ID');
        calculateTotal();
    }
}

function calculateTotal() {
    const subtotals = document.querySelectorAll('.subtotal');
    let total = 0;
    
    subtotals.forEach(subtotal => {
        if (subtotal.value) {
            total += parseInt(subtotal.value.replace(/\./g, ''));
        }
    });
    
    document.getElementById('total_amount').value = total.toLocaleString('id-ID');
}

document.getElementById('add_item').addEventListener('click', function() {
    const saleItems = document.getElementById('sale_items');
    const newItem = saleItems.querySelector('.sale-item').cloneNode(true);
    
    newItem.querySelector('.product-select').value = '';
    newItem.querySelector('.quantity-input').value = '';
    newItem.querySelector('.subtotal').value = '';
    
    saleItems.appendChild(newItem);
});

document.querySelector('form').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    console.log("Form data:");
    for (let pair of formData.entries()) {
        console.log(pair[0]+ ': ' + pair[1]); 
    }

    const items = document.querySelectorAll('.sale-item');
    let isValid = true;
    
    items.forEach(item => {
        const select = item.querySelector('.product-select');
        const quantity = item.querySelector('.quantity-input');
        const stock = parseInt(select.options[select.selectedIndex]?.dataset.stock || 0);
        const qty = parseInt(quantity.value || 0);
        
        if (qty > stock) {
            alert('Jumlah melebihi stok yang tersedia!');
            isValid = false;
            e.preventDefault(); 
            return; 
        }
    });
    
    if (!isValid) {
        e.preventDefault();
    } else {
        console.log("Form is valid, proceeding with submission.");
    }
});

function printReceipt() {
    // Simpan konten asli halaman
    var originalContents = document.body.innerHTML;
    
    // Ganti konten halaman dengan struk saja
    var printContents = document.getElementById('receipt').outerHTML;
    document.body.innerHTML = printContents;
    
    // Cetak halaman
    window.print();
    
    // Kembalikan konten asli setelah mencetak
    document.body.innerHTML = originalContents;
    
    // Redirect kembali ke halaman penjualan setelah mencetak
    setTimeout(function() {
        window.location.href = 'index.php?page=sales';
    }, 1000);
}

$(document).ready(function() {
    <?php if (isset($saleDetail)): ?>
    $('#viewSaleModal').modal('show');
    <?php endif; ?>
    
    <?php if (isset($receiptData)): ?>
    $('#printReceiptModal').modal('show');
    $('#viewSaleModal').modal('hide');
    <?php endif; ?>
});

$(document).on('click', '#btnShowPrintReceipt', function() {
    $('#viewSaleModal').modal('hide');
    setTimeout(function() {
        $('#printReceiptModal').modal('show');
    }, 400);
});
</script>