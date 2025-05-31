<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Akuntansi - Toko Sembako Supri</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
        }
        .nav-link.active {
            background-color: #495057 !important;
        }
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        /* CSS untuk dropdown menu */
        .dropdown-content {
            display: none;
            background-color: #343a40;
            padding-left: 2rem;
        }
        .dropdown-content a {
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #495057;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown:hover .nav-link {
            background-color: #495057;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Toko Sembako Supri</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'products' ? 'active' : '' ?>" href="index.php?page=products">
                                <i class="fas fa-box me-2"></i>
                                Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'sales' ? 'active' : '' ?>" href="index.php?page=sales">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'purchases' ? 'active' : '' ?>" href="index.php?page=purchases">
                                <i class="fas fa-truck me-2"></i>
                                Pembelian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'receivables' ? 'active' : '' ?>" href="index.php?page=receivables">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Piutang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?= $page == 'reports' ? 'active' : '' ?>" href="index.php?page=reports">
                                <i class="fas fa-chart-bar me-2"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white <?= in_array($page, ['journals','ledgers']) ? 'active' : '' ?>" href="#">
                                <i class="fas fa-book me-2"></i>
                                Akuntan
                            </a>
                            <div class="dropdown-content">
                                <a href="index.php?page=journals" class="<?= $page == 'journals' ? 'active' : '' ?>">
                                    <i class="fas fa-file-invoice me-2"></i>Jurnal Umum
                                </a>
                                <a href="index.php?page=ledgers" class="<?= $page == 'ledgers' ? 'active' : '' ?>">
                                    <i class="fas fa-book-open me-2"></i>Buku Besar
                                </a>
                            </div>
                        </li>
                    </ul>
                    
                    <hr class="text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php
                        switch ($page) {
                            case 'dashboard':
                                echo 'Dashboard';
                                break;
                            case 'products':
                                echo 'Manajemen Produk';
                                break;
                            case 'sales':
                                echo 'Transaksi Penjualan';
                                break;
                            case 'purchases':
                                echo 'Transaksi Pembelian';
                                break;
                            case 'receivables':
                                echo 'Manajemen Piutang';
                                break;
                            case 'reports':
                                echo 'Laporan Keuangan';
                                break;
                            case 'journals':
                                echo 'Jurnal Umum';
                                break;
                            case 'ledgers':
                                echo 'Buku Besar';
                                break;
                            default:
                                echo 'Dashboard';
                                break;
                        }
                        ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-user me-1"></i> <?= $_SESSION['username'] ?? 'User' ?>
                            </span>
                        </div>
                    </div>
                </div>
