<?php
require_once 'controllers/ProductController.php';
require_once 'models/Supplier.php';

$productController = new ProductController();
$supplierModel = new Supplier();

$products = $productController->getAllProducts();
$suppliers = $supplierModel->getAll();


$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $supplier_id = $_POST['supplier_id'];
        
        if ($supplier_id === 'new' && !empty($_POST['new_supplier_name'])) {
            $newSupplierName = trim($_POST['new_supplier_name']);
            
            $newSupplierId = $supplierModel->create([
                'name' => $newSupplierName
            ]);
            if ($newSupplierId) {
                $supplier_id = $newSupplierId;
            } else {
                $message = 'Gagal menambah supplier baru';
                $messageType = 'danger';
                return;
            }
        }
        
        $code = isset($_POST['code']) && trim($_POST['code']) !== '' ? trim($_POST['code']) : null;
        if (!$code) {
            
            $allProducts = $productController->getAllProducts();
            $usedCodes = [];
            foreach ($allProducts as $p) {
                if ($p['code'] && preg_match('/PRD(\d+)/', $p['code'], $m)) {
                    $usedCodes[] = intval($m[1]);
                }
            }
            
            $num = 1;
            while (in_array($num, $usedCodes)) {
                $num++;
            }
            $code = 'PRD' . str_pad($num, 3, '0', STR_PAD_LEFT);
        }
        
        $category = isset($_POST['category']) && trim($_POST['category']) !== '' ? trim($_POST['category']) : null;
        if (!$category) {
            $nameLower = strtolower($_POST['name']);
            if (preg_match('/aqua|minum|galon|mineral|teh|kopi|susu|fanta|sprite|coca|pocari|mizone|ultramilk|yakult|sariwangi|goodday|marimas|nutrisari/', $nameLower)) {
                $category = 'Minuman';
            } elseif (preg_match('/beras|gula|tepung|mie|telur|kecap|minyak|garam|sagu|jagung|roti|margarin|mentega|sarden|abon|saos|sambal/', $nameLower)) {
                $category = 'Sembako';
            } elseif (preg_match('/snack|ciki|chiki|wafer|biskuit|keripik|kacang|coklat|permen|astor|oreo|tango|roma|nabati|bengbeng|taro|lays|doritos/', $nameLower)) {
                $category = 'Makanan Ringan';
            } elseif (preg_match('/sabun|shampo|shampoo|deterjen|sunlight|pel|sapu|ember|gayung|sikat|pewangi|pelicin|pembersih|wipol|so klin|rinso|molto|baygon|kapur barus/', $nameLower)) {
                $category = 'Kebutuhan Rumah Tangga';
            } elseif (preg_match('/pasta|gigi|odol|sikat gigi|deodorant|lotion|minyak angin|balsem|minyak kayu putih|minyak telon|bedak|tissue|cotton bud|kapas/', $nameLower)) {
                $category = 'Perawatan Tubuh';
            } elseif (preg_match('/rokok|sampoerna|gudang garam|djarum|surya|filter|mild|marlboro|la|magnum/', $nameLower)) {
                $category = 'Rokok';
            } elseif (preg_match('/gas|elpiji|lpg|minyak tanah|solar|bensin/', $nameLower)) {
                $category = 'Gas & Energi';
            } elseif (preg_match('/pensil|pulpen|buku|penghapus|spidol|penggaris|tip-ex|lem|kertas|map|amplop/', $nameLower)) {
                $category = 'Alat Tulis';
            } elseif (preg_match('/baterai|lampu|charger|kabel|colokan|stop kontak/', $nameLower)) {
                $category = 'Elektronik Kecil';
            } else {
                $category = 'Lainnya';
            }
        }
        $productData = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'stock' => $_POST['stock'],
            'supplier_id' => $supplier_id,
            'code' => $code,
            'category' => $category
        ];
        
        if ($_POST['action'] == 'add') {
            $result = $productController->createProduct($productData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                
                $products = $productController->getAllProducts();
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] == 'edit') {
            $productId = $_POST['product_id'];
            $result = $productController->updateProduct($productId, $productData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                
                $products = $productController->getAllProducts();
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $productId = $_GET['id'];
    $result = $productController->deleteProduct($productId);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
        
        $products = $productController->getAllProducts();
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

<!-- Tombol Tambah Produk -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Tambah Produk
    </button>
</div>

<!-- Tabel Produk -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Produk</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Supplier</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= $product['name'] ?></td>
                            <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($product['stock'] <= 5): ?>
                                    <span class="badge bg-danger"><?= $product['stock'] ?></span>
                                <?php else: ?>
                                    <?= $product['stock'] ?>
                                <?php endif; ?>
                            </td>
                            <td><?= $product['supplier_name'] ?? '-' ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info edit-product" 
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= $product['name'] ?>"
                                        data-price="<?= $product['price'] ?>"
                                        data-stock="<?= $product['stock'] ?>"
                                        data-supplier="<?= $product['supplier_id'] ?>"
                                        data-code="<?= $product['code'] ?? '-' ?>"
                                        data-category="<?= $product['category'] ?? 'Umum' ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="index.php?page=products&action=delete&id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="price" name="price" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                            <?php endforeach; ?>
                            <option value="new">Tambah Supplier Baru...</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="new_supplier_name" name="new_supplier_name" placeholder="Nama Supplier Baru" style="display:none;">
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

<!-- Modal Edit Produk -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit_price" name="price" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_stock" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="edit_supplier_id" name="supplier_id">
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_code" class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" id="edit_code" name="code" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="edit_category" name="category" required>
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

<!-- Modal Tambah Supplier Baru -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formAddSupplier">
          <div class="mb-3">
            <label for="new_supplier_name" class="form-label">Nama Supplier</label>
            <input type="text" class="form-control" id="new_supplier_name" name="new_supplier_name" required>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Supplier</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
    
    $(document).on('click', '.edit-product', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var price = $(this).data('price');
        var stock = $(this).data('stock');
        var supplier = $(this).data('supplier');
        var code = $(this).data('code') || '-';
        var category = $(this).data('category') || 'Umum';
        $('#edit_product_id').val(id);
        $('#edit_name').val(name);
        $('#edit_price').val(price);
        $('#edit_stock').val(stock);
        $('#edit_supplier_id').val(supplier);
        $('#edit_code').val(code);
        $('#edit_category').val(category);
        $('#editProductModal').modal('show');
    });

    $('#formAddSupplier').on('submit', function(e) {
        e.preventDefault();
        var data = {
            name: $('#new_supplier_name').val()
        };
        $.ajax({
            url: 'controllers/SupplierController.php',
            type: 'POST',
            data: { action: 'add_supplier', ...data },
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if (res.success) {
                        
                        var newOption = $('<option>').val(res.supplier_id).text(data.name);
                        $('#supplier_id').append(newOption);
                        $('#supplier_id').val(res.supplier_id);
                        $('#addSupplierModal').modal('hide');
                        $('#formAddSupplier')[0].reset();
                        alert('Supplier berhasil ditambahkan!');
                    } else {
                        alert(res.message || 'Gagal menambah supplier');
                    }
                } catch (e) {
                    alert('Gagal menambah supplier');
                }
            },
            error: function() {
                alert('Gagal menambah supplier');
            }
        });
    });

    $('#supplier_id').on('change', function() {
        if ($(this).val() === 'new') {
            $('#new_supplier_name').show().attr('required', true);
        } else {
            $('#new_supplier_name').hide().val('').attr('required', false);
        }
    });
});
</script>
