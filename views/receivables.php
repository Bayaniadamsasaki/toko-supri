<?php
require_once 'controllers/ReceivableController.php';
require_once 'models/Customer.php';

$customerModel = new Customer();
$customers = $customerModel->getAll();

$receivableController = new ReceivableController();

$receivables = $receivableController->getAllReceivables();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $receivableData = [
            'customer_id' => $_POST['customer_id'],
            'amount' => $_POST['amount'],
            'date' => $_POST['date'],
            'due_date' => $_POST['due_date'],
            'status' => $_POST['status'],
            'notes' => $_POST['notes']
        ];
        
        if ($_POST['action'] == 'add') {
            $result = $receivableController->createReceivable($receivableData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                $receivables = $receivableController->getAllReceivables();
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] == 'edit') {
            $receivableId = $_POST['receivable_id'];
            $result = $receivableController->updateReceivable($receivableId, $receivableData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                $receivables = $receivableController->getAllReceivables();
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'pay' && isset($_GET['id'])) {
    $receivableId = $_GET['id'];
    $result = $receivableController->payReceivable($receivableId);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        $receivables = $receivableController->getAllReceivables();
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}
?>

<!-- Alert Message -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Tombol Tambah Piutang -->
<div class="mb-3 d-flex justify-content-between">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReceivableModal">
        <i class="fas fa-plus"></i> Tambah Piutang
    </button>
</div>

<!-- Tabel Piutang -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Piutang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receivables as $receivable): ?>
                        <tr>
                            <td><?= $receivable['id'] ?></td>
                            <td><?= $receivable['customer_name'] ?></td>
                            <td>Rp <?= number_format($receivable['amount'], 0, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($receivable['date'])) ?></td>
                            <td>
                                <?php 
                                $dueDate = strtotime($receivable['due_date']);
                                $today = strtotime(date('Y-m-d'));
                                $daysLeft = ceil(($dueDate - $today) / (60 * 60 * 24));
                                
                                if ($receivable['status'] == 'paid') {
                                    echo date('d/m/Y', strtotime($receivable['due_date']));
                                } elseif ($daysLeft < 0) {
                                    echo '<span class="text-danger">' . date('d/m/Y', $dueDate) . ' (Terlambat ' . abs($daysLeft) . ' hari)</span>';
                                } elseif ($daysLeft <= 3) {
                                    echo '<span class="text-warning">' . date('d/m/Y', $dueDate) . ' (' . $daysLeft . ' hari lagi)</span>';
                                } else {
                                    echo date('d/m/Y', $dueDate);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($receivable['status'] == 'paid'): ?>
                                    <span class="badge bg-success">Lunas</span>
                                <?php elseif ($receivable['status'] == 'Sebagian'): ?>
                                    <span class="badge bg-warning">Sebagian</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="no-print">
                                <button type="button" class="btn btn-sm btn-info edit-receivable" 
                                        data-id="<?= $receivable['id'] ?>"
                                        data-customer="<?= $receivable['customer_id'] ?>"
                                        data-amount="<?= $receivable['amount'] ?>"
                                        data-date="<?= $receivable['date'] ?>"
                                        data-due-date="<?= $receivable['due_date'] ?>"
                                        data-status="<?= $receivable['status'] ?>"
                                        data-notes="<?= $receivable['notes'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light print-receivable" 
                                        data-id="<?= $receivable['id'] ?>"
                                        data-customer="<?= $receivable['customer_name'] ?>"
                                        data-amount="<?= $receivable['amount'] ?>"
                                        data-date="<?= $receivable['date'] ?>"
                                        data-due-date="<?= $receivable['due_date'] ?>"
                                        data-status="<?= $receivable['status'] ?>"
                                        data-notes="<?= $receivable['notes'] ?>"
                                        title="Cetak Struk">
                                    <i class="fas fa-print"></i>
                                </button>
                                <?php if ($receivable['status'] != 'paid'): ?>
                                    <a href="index.php?page=receivables&action=pay&id=<?= $receivable['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin ingin menandai piutang ini sebagai sebagian dibayar?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Piutang -->
<div class="modal fade" id="addReceivableModal" tabindex="-1" aria-labelledby="addReceivableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReceivableModalLabel">Tambah Piutang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Pelanggan</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= $customer['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Jatuh Tempo</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="unpaid">Belum Lunas</option>
                            <option value="Sebagian">Sebagian</option>
                            <option value="paid">Lunas</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
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

<!-- Modal Edit Piutang -->
<div class="modal fade" id="editReceivableModal" tabindex="-1" aria-labelledby="editReceivableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReceivableModalLabel">Edit Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="receivable_id" id="edit_receivable_id">
                    
                    <div class="mb-3">
                        <label for="edit_customer_id" class="form-label">Pelanggan</label>
                        <select class="form-select" id="edit_customer_id" name="customer_id" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>"><?= $customer['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit_amount" name="amount" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_due_date" class="form-label">Jatuh Tempo</label>
                        <input type="date" class="form-control" id="edit_due_date" name="due_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="unpaid">Belum Lunas</option>
                            <option value="Sebagian">Sebagian</option>
                            <option value="paid">Lunas</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Struk Piutang -->
<div class="modal fade" id="printReceivableModal" tabindex="-1" aria-labelledby="printReceivableModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printReceivableModalLabel">Struk Piutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="receivable-receipt">
                    <!-- Header Toko -->
                    <div class="receivable-receipt-header">
                        <h2>Toko Sembako Supri</h2>
                        <div>Jalan Tanjung Baru, RT3/RW1</div>
                        <div>Desa Tambusai Barat</div>
                        <div>-</div>
                    </div>
                    
                    <!-- Info Transaksi -->
                    <div class="receivable-receipt-info">
                        <div>
                            <span><strong>No Piutang:</strong></span>
                            <span id="r-id"></span>
                        </div>
                        <div>
                            <span><strong>Tanggal:</strong></span>
                            <span id="r-date"></span>
                        </div>
                        <div>
                            <span><strong>Pelanggan:</strong></span>
                            <span id="r-customer"></span>
                        </div>
                        <div>
                            <span><strong>Jumlah:</strong></span>
                            <span id="r-amount"></span>
                        </div>
                        <div>
                            <span><strong>Jatuh Tempo:</strong></span>
                            <span id="r-due-date"></span>
                        </div>
                        <div>
                            <span><strong>Status:</strong></span>
                            <span id="r-status"></span>
                        </div>
                    </div>
                    
                    <div class="receivable-receipt-divider"></div>
                    
                    <!-- Catatan -->
                    <div class="receivable-receipt-notes">
                        <div><strong>Catatan:</strong></div>
                        <div id="r-notes"></div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="receivable-receipt-footer">
                        <p>Nota Piutang<br>Toko Sembako Supri</p>
                        <p>Mohon segera melunasi<br>sebelum jatuh tempo</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printSingleReceivable()">Cetak</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Style untuk preview struk di modal */
#receivable-receipt {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.3;
    max-width: 350px;
    margin: 0 auto;
    padding: 15px;
    background: white;
    border: 1px solid #ddd;
}

.receivable-receipt-header {
    text-align: center;
    margin-bottom: 15px;
    border-bottom: 1px dashed #000;
    padding-bottom: 10px;
}

.receivable-receipt-header h2 {
    font-size: 16px;
    margin: 0 0 5px 0;
    font-weight: bold;
}

.receivable-receipt-header div {
    font-size: 11px;
    margin-bottom: 2px;
}

.receivable-receipt-info {
    margin-bottom: 15px;
    font-size: 12px;
}

.receivable-receipt-info div {
    margin-bottom: 3px;
    display: flex;
    justify-content: space-between;
}

.receivable-receipt-divider {
    border-bottom: 1px dashed #000;
    margin: 10px 0;
}

.receivable-receipt-notes {
    margin-bottom: 15px;
    font-size: 12px;
}

.receivable-receipt-notes div:first-child {
    margin-bottom: 5px;
    font-weight: bold;
}

.receivable-receipt-notes div:last-child {
    padding: 5px;
    background-color: #f8f9fa;
    border: 1px dashed #ccc;
    min-height: 30px;
}

.receivable-receipt-footer {
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
    
    #receivable-receipt, #receivable-receipt * { 
        visibility: visible !important; 
    }
    
    #receivable-receipt {
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
    
    .receivable-receipt-header h2 {
        font-size: 14px !important;
    }
    
    .receivable-receipt-notes div:last-child {
        background-color: white !important;
        border: 1px dashed #000 !important;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    
    $('.edit-receivable').click(function() {
        var id = $(this).data('id');
        var customer = $(this).data('customer');
        var amount = $(this).data('amount');
        var date = $(this).data('date');
        var dueDate = $(this).data('due-date');
        var status = $(this).data('status');
        var notes = $(this).data('notes');
        
        $('#edit_receivable_id').val(id);
        $('#edit_customer_id').val(customer);
        $('#edit_amount').val(amount);
        $('#edit_date').val(date);
        $('#edit_due_date').val(dueDate);
        $('#edit_status').val(status);
        $('#edit_notes').val(notes);
        
        $('#editReceivableModal').modal('show');
    });

    $('.print-receivable').click(function() {
        $('#r-id').text($(this).data('id'));
        $('#r-customer').text($(this).data('customer'));
        $('#r-amount').text('Rp ' + parseInt($(this).data('amount')).toLocaleString('id-ID'));
        $('#r-date').text(new Date($(this).data('date')).toLocaleDateString('id-ID'));
        $('#r-due-date').text(new Date($(this).data('due-date')).toLocaleDateString('id-ID'));
        var status = $(this).data('status');
        var statusText = status === 'paid' ? 'Lunas' : (status === 'Sebagian' ? 'Sebagian' : 'Belum Lunas');
        $('#r-status').text(statusText);
        $('#r-notes').text($(this).data('notes') || '-');
        $('#printReceivableModal').modal('show');
    });
});

function printSingleReceivable() {
    // Simpan konten asli halaman
    var originalContents = document.body.innerHTML;
    
    // Ganti konten halaman dengan struk saja
    var printContents = document.getElementById('receivable-receipt').outerHTML;
    document.body.innerHTML = printContents;
    
    // Cetak halaman
    window.print();
    
    // Kembalikan konten asli setelah mencetak
    document.body.innerHTML = originalContents;
    
    // Redirect kembali ke halaman piutang setelah mencetak
    setTimeout(function() {
        window.location.href = 'index.php?page=receivables';
    }, 1000);
}
</script>