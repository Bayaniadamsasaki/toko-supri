<?php
session_start();
require_once 'controllers/AuthController.php';

$auth = new AuthController();
$result = $auth->logout();

header("Location: login.php?message=logout_success");
exit();
?>
