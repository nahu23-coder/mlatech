<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/mercadopago.php';

if (empty($_SESSION['cart'])) {
    header('Location: /src/views/cart.php');
    exit;
}

// Traemos los nombres de los productos del carrito para armar los ítems de la preferencia
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT id, name FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$productos = $stmt->fetchAll();

$items = [];
foreach ($productos as $p) {
    $items[] = [
        'title'    => $p['name'],
        'quantity' => (int) $_SESSION['cart'][$p['id']],
    ];
}

$resultado = mp_crear_preferencia($items);

if ($resultado['ok']) {
    // Vaciamos el carrito recién cuando Mercado Pago confirme el pago (ver success.php),
    // acá solo redirigimos al checkout.
    header('Location: ' . $resultado['init_point']);
    exit;
}

// Si algo falla (por ejemplo, falta el access token o Mercado Pago rechaza la preferencia)
$_SESSION['mp_error'] = $resultado['error'];
header('Location: /src/views/checkout/failure.php');
exit;
