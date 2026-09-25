<?php
require_once __DIR__ . '/../../config/bootstrap.php';

$productId = $_GET['product_id'] ?? ($_POST['product_id'] ?? '');

if ($productId !== '' && isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
}

header('Location: /src/views/cart.php');
exit;
