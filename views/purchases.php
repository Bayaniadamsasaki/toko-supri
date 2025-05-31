<?php
require_once 'controllers/ProductController.php';
require_once 'controllers/SupplierController.php';
require_once 'controllers/PurchaseController.php';
require_once 'models/Purchase.php';

$productController = new ProductController();
$supplierController = new SupplierController();
$purchaseController = new PurchaseController();

$products = $productController->getAllProducts();
$suppliers = $supplierController->getAllSuppliers();
$purchases = $purchaseController->getAllPurchases();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_purchase') {
        $purchaseData = [
            'supplier_id' => $_POST['supplier_id'],
            'status' => $_POST['status'],
            'items' => []
        ];
        
        $productIds = $_POST['product_id'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];
        
        for ($i = 0; $i < count($productIds); $i++) {
            if (!empty($productIds[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
                $purchaseData['items'][] = [
                    'product_id' => $productIds[$i],
                    'quantity' => $quantities[$i],
                    'price' => $prices[$i]
                ];
            }
        }
        
        $result = $purchaseController->createPurchase($purchaseData);
        
        if ($result['success']) {
            $message = $result['message'];  
            $messageType = 'success';
            $purchases = $purchaseController->getAllPurchases();
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $purchaseId = $_GET['id'];
    $purchaseDetail = $purchaseController->getPurchaseById($purchaseId);
}
?>

<!-- Alert Message -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Tombol Tambah Pembelian -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
        <i class="fas fa-plus"></i> Tambah Pembelian
    </button>
</div>

<!-- Tabel Pembelian -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Pembelian</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><?= $purchase['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($purchase['date'])) ?></td>
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
                            <td>
                                <a href="index.php?page=purchases&action=view&id=<?= $purchase['id'] ?>" class="btn btn-sm btn-info">
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

<!-- Modal Tambah Pembelian -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseModalLabel">Tambah Pembelian Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_purchase">
                    
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="completed">Selesai</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <h6>Item Pembelian</h6>
                    <div id="purchase_items">
                        <div class="row mb-3 purchase-item">
                            <div class="col-md-4">
                                <label class="form-label">Produk</label>
                                <select class="form-select product-select" name="product_id[]" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>">
                                            <?= $product['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Harga Beli</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control price-input" name="price[]" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control quantity-input" name="quantity[]" min="1" required>
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
                                <label class="form-label">Total Pembelian</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
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

<!-- Modal Detail Pembelian -->
<?php if (isset($purchaseDetail)): ?>
<div class="modal fade" id="viewPurchaseModal" tabindex="-1" aria-labelledby="viewPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPurchaseModalLabel">Detail Pembelian #<?= $purchaseDetail['id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($purchaseDetail['date'])) ?></p>
                        <p><strong>Supplier:</strong> <?= $purchaseDetail['supplier_name'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php if ($purchaseDetail['status'] == 'completed'): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php elseif ($purchaseDetail['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= $purchaseDetail['status'] ?></span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Total:</strong> Rp <?= number_format($purchaseDetail['total'], 0, ',', '.') ?></p>
                    </div>
                </div>
                
                <h6>Item Pembelian</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga Beli</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchaseDetail['details'] as $item): ?>
                                <tr>
                                    <td><?= $item['product_name'] ?></td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th>Rp <?= number_format($purchaseDetail['total'], 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printPurchaseReceipt()">
                    <i class="fas fa-print"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Cetak Struk Pembelian -->
<?php if (isset($purchaseDetail)): ?>
<style>
/* Style untuk preview struk di modal */
#purchase-receipt {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.3;
    max-width: 350px;
    margin: 0 auto;
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
}

.purchase-receipt-header {
    text-align: center;
    margin-bottom: 15px;
    border-bottom: 1px dashed #000;
    padding-bottom: 10px;
}

.purchase-receipt-header h2 {
    font-size: 16px;
    margin: 0 0 5px 0;
    font-weight: bold;
}

.purchase-receipt-header div {
    font-size: 11px;
    margin-bottom: 2px;
}

.purchase-receipt-info {
    margin-bottom: 15px;
    font-size: 12px;
}

.purchase-receipt-info div {
    margin-bottom: 3px;
    display: flex;
    justify-content: space-between;
}

.purchase-receipt-divider {
    border-bottom: 1px dashed #000;
    margin: 10px 0;
}

.purchase-receipt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-bottom: 10px;
}

.purchase-receipt-table th {
    text-align: left;
    padding: 5px 2px;
    border-bottom: 1px dashed #000;
    font-weight: bold;
}

.purchase-receipt-table td {
    padding: 3px 2px;
    vertical-align: top;
}

.purchase-receipt-table .text-right {
    text-align: right;
}

.purchase-receipt-table .text-center {
    text-align: center;
}

.purchase-receipt-total {
    margin-top: 10px;
    padding-top: 5px;
    border-top: 1px dashed #000;
}

.purchase-receipt-total div {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 13px;
}

.purchase-receipt-footer {
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
    
    #purchase-receipt, #purchase-receipt * { 
        visibility: visible !important; 
    }
    
    #purchase-receipt {
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
    
    .purchase-receipt-header h2 {
        font-size: 14px !important;
    }
    
    .purchase-receipt-table {
        font-size: 9px !important;
    }
    
    .purchase-receipt-table th,
    .purchase-receipt-table td {
        padding: 2px 1px !important;
    }
}
</style>

<div class="modal fade" id="printPurchaseModal" tabindex="-1" aria-labelledby="printPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printPurchaseModalLabel">Struk Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="purchase-receipt">
                    <!-- Header Toko -->
                    <div class="purchase-receipt-header">
                        <h2>Toko Sembako Supri</h2>
                        <div>Jalan Tanjung Baru, RT3/RW1</div>
                        <div>Desa Tambusai Barat</div>
                        <div>-</div>
                    </div>
                    
                    <!-- Info Transaksi -->
                    <div class="purchase-receipt-info">
                        <div>
                            <span><strong>No:</strong></span>
                            <span><?= $purchaseDetail['id'] ?></span>
                        </div>
                        <div>
                            <span><strong>Tanggal:</strong></span>
                            <span><?= date('d/m/Y H:i', strtotime($purchaseDetail['date'])) ?></span>
                        </div>
                        <div>
                            <span><strong>Supplier:</strong></span>
                            <span><?= $purchaseDetail['supplier_name'] ?></span>
                        </div>
                        <div>
                            <span><strong>Status:</strong></span>
                            <span>
                                <?php if ($purchaseDetail['status'] == 'completed'): ?>
                                    Selesai
                                <?php elseif ($purchaseDetail['status'] == 'pending'): ?>
                                    Pending
                                <?php else: ?>
                                    <?= $purchaseDetail['status'] ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="purchase-receipt-divider"></div>
                    
                    <!-- Tabel Item -->
                    <table class="purchase-receipt-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item</th>
                                <th class="text-center" style="width: 15%;">Qty</th>
                                <th class="text-right" style="width: 22%;">Harga</th>
                                <th class="text-right" style="width: 23%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchaseDetail['details'] as $item): ?>
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
                    <div class="purchase-receipt-total">
                        <div>
                            <span>TOTAL:</span>
                            <span>Rp <?= number_format($purchaseDetail['total'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="purchase-receipt-footer">
                        <p>Nota Pembelian<br>Toko Sembako Supri</p>
                        <p>Barang sudah diterima<br>dalam keadaan baik</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printPurchaseReceipt()">Cetak</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    <?php if (isset($purchaseDetail)): ?>
    $('#viewPurchaseModal').modal('show');
    <?php endif; ?>
    
    $('#add_item').click(function() {
        const template = $('.purchase-item:first').clone();
        template.find('input, select').val('');
        template.find('.subtotal').val('0');
        
        if (!template.find('.remove-item').length) {
            template.append('<div class="col-12 mt-2"><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i> Hapus</button></div>');
        }
        $('#purchase_items').append(template);
    });
    
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.purchase-item').remove();
        calculateTotal();
    });
    
    $(document).on('change', '.product-select', function() {
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price');
        const row = $(this).closest('.purchase-item');
        row.find('.price-input').val(price);
        calculateSubtotal(row);
    });
    
    $(document).on('input', '.quantity-input, .price-input', function() {
        const row = $(this).closest('.purchase-item');
        calculateSubtotal(row);
    });
    
    function calculateSubtotal(row) {
        const price = parseFloat(row.find('.price-input').val()) || 0;
        const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
        const subtotal = price * quantity;
        row.find('.subtotal').val(formatRupiah(subtotal.toString()));
        calculateTotal();
    }
    
    function calculateTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            const value = $(this).val().replace(/[^\d]/g, '');
            total += parseFloat(value) || 0;
        });
        $('#total_amount').val(formatRupiah(total.toString()));
    }
    
    function formatRupiah(angka) {
        const number_string = angka.replace(/[^\d]/g, '').toString();
        const split = number_string.split(',');
        const sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            const separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    window.printPurchaseReceipt = function() {
        // Tampilkan modal cetak terlebih dahulu
        $('#viewPurchaseModal').modal('hide');
        setTimeout(function() {
            $('#printPurchaseModal').modal('show');
        }, 300);
    };

    window.printPurchaseReceipt = function() {
        // Simpan konten asli halaman
        var originalContents = document.body.innerHTML;
        
        // Ganti konten halaman dengan struk saja
        var printContents = document.getElementById('purchase-receipt').outerHTML;
        document.body.innerHTML = printContents;
        
        // Cetak halaman
        window.print();
        
        // Kembalikan konten asli setelah mencetak
        document.body.innerHTML = originalContents;
        
        // Redirect kembali ke halaman pembelian setelah mencetak
        setTimeout(function() {
            window.location.href = 'index.php?page=purchases';
        }, 1000);
    };

    $('#supplier_id').on('change', function() {
        if ($(this).val() === 'new') {
            $('#new_supplier_name').show().attr('required', true);
        } else {
            $('#new_supplier_name').hide().val('').attr('required', false);
        }
    });
});
</script>