<?php
include('./_layouts/layout.php');

$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    // Traemos de la DB los datos actuales de cada producto que está en el carrito
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT p.*, c.name AS category_name, c.icon AS category_icon
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $productos = $stmt->fetchAll();

    foreach ($productos as $p) {
        $qty = (int) $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $items[] = ['producto' => $p, 'qty' => $qty, 'subtotal' => $subtotal];
    }
}
?>

<h1 class="h3 mb-4">Tu carrito</h1>

<?php if (empty($items)): ?>
  <p class="text-secondary">Todavía no agregaste productos. <a href="/src/views/products.php">Ver productos</a></p>
<?php else: ?>
  <div class="table-responsive mb-4">
    <table class="table table-dark align-middle">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): $p = $item['producto']; ?>
          <tr>
            <td>
              <span class="me-2"><?= htmlspecialchars($p['category_icon']) ?></span>
              <?= htmlspecialchars($p['name']) ?>
            </td>
            <td><?= htmlspecialchars($p['category_name']) ?></td>
            <td>$ <?= number_format($p['price'], 0, ',', '.') ?></td>
            <td>
              <form action="/src/controllers/cart/update.php" method="POST" class="d-flex gap-1">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" class="form-control form-control-sm" style="width:70px">
                <button type="submit" class="btn btn-outline-accent btn-sm">Actualizar</button>
              </form>
            </td>
            <td class="price">$ <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
            <td>
              <a href="/src/controllers/cart/remove.php?product_id=<?= urlencode($p['id']) ?>" class="btn btn-outline-danger btn-sm">Quitar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-between align-items-center">
    <h2 class="h5 mb-0">Total: <span class="price"><?= '$ ' . number_format($total, 0, ',', '.') ?></span></h2>
    <a href="/src/controllers/checkout/create_preference.php" class="btn btn-accent btn-lg">
      Pagar con Mercado Pago
    </a>
  </div>

  <p class="text-secondary small mt-3">
    * Modo prueba: el pago se procesa por $0 con Mercado Pago (sandbox), solo para simular el flujo de compra.
  </p>
<?php endif; ?>

<?php include('./_layouts/footer.php'); ?>
