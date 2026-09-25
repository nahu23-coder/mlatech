<?php
include('../_layouts/layout.php');

// El pago (simulado) se aprobó: vaciamos el carrito
$_SESSION['cart'] = [];
?>

<div class="text-center py-5">
  <div style="font-size:4rem;">✅</div>
  <h1 class="h3 mt-3">¡Pago aprobado!</h1>
  <p class="text-secondary">
    Tu compra de prueba se procesó correctamente con Mercado Pago.
    Payment ID: <?= htmlspecialchars($_GET['payment_id'] ?? '—') ?>
  </p>
  <a href="/src/views/products.php" class="btn btn-accent mt-3">Seguir comprando</a>
</div>

<?php include('../_layouts/footer.php'); ?>
