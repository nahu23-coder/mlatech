<?php
require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$productId = $_POST['product_id'] ?? '';
$qty       = max(1, (int) ($_POST['qty'] ?? 1));

if ($productId === '') {
    echo json_encode(['success' => false, 'error' => 'Falta el producto']);
    exit;
}

// Verificamos que el producto exista de verdad antes de agregarlo
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $productId]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'El producto no existe']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;

$cartCount = array_sum($_SESSION['cart']);

echo json_encode(['success' => true, 'cart_count' => $cartCount]);
