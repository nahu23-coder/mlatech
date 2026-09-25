<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? '';
    $qty       = max(1, (int) ($_POST['qty'] ?? 1));

    if ($productId !== '' && isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = $qty;
    }
}

header('Location: /src/views/cart.php');
exit;
