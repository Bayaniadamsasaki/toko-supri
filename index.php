<?php
session_start();
require_once 'config/database.php';
require_once 'controllers/AuthController.php';


if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit();
}


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';


include_once 'includes/header.php';


switch ($page) {
    case 'dashboard':
        include_once 'views/dashboard.php';
        break;
    case 'products':
        include_once 'views/products.php';
        break;
    case 'sales':
        include_once 'views/sales.php';
        break;
    case 'purchases':
        include_once 'views/purchases.php';
        break;
    case 'receivables':
        include_once 'views/receivables.php';
        break;
    case 'reports':
        include_once 'views/reports.php';
        break;
    case 'journals':
        include_once 'views/journals.php';
        break;
    case 'ledgers':
        include_once 'views/ledgers.php';
        break;
    default:
        include_once 'views/dashboard.php';
        break;
}


include_once 'includes/footer.php';
?>
