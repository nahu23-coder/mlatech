<?php
include('../_layouts/layout.php');

$error = $_SESSION['mp_error'] ?? null;
unset($_SESSION['mp_error']);
?>

<div class="text-center py-5">
  <div style="font-size:4rem;">❌</div>
  <h1 class="h3 mt-3">El pago no se pudo procesar</h1>
  <p class="text-secondary">
    Algo falló al intentar procesar el pago con Mercado Pago. Podés volver a intentarlo.
  </p>
  <?php if ($error): ?>
    <p class="text-danger small">Detalle técnico: <?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <a href="/src/views/cart.php" class="btn btn-accent mt-3">Volver al carrito</a>
</div>

<?php include('../_layouts/footer.php'); ?>
