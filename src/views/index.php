<?php
include('./_layouts/layout.php');

// Categorías (para las cards de categorías)
$categorias = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Productos destacados: los últimos 4 cargados
$destacados = $pdo->query(
  "SELECT p.*, c.name AS category_name, c.icon AS category_icon
   FROM products p
   JOIN categories c ON c.id = p.category_id
   ORDER BY p.created_at DESC
   LIMIT 4"
)->fetchAll();
?>

<!-- Hero / Banner -->
<section class="hero mb-5">
  <img src="/assets/img/banner.jpg" alt="MLA Tech - Tu tienda de hardware">
  <div class="hero-caption">
    <h1 class="display-6 text-white">Armá tu PC con los mejores componentes</h1>
    <p class="lead text-secondary">
      Procesadores, placas de video, memorias, almacenamiento y periféricos al mejor precio, con envíos a todo el país.
    </p>
    <a href="/src/views/products.php" class="btn btn-accent btn-lg mt-2">Ver productos</a>
  </div>
</section>

<!-- Categorías -->
<section id="categorias" class="mb-5">
  <h2 class="h4 mb-3">Categorías</h2>
  <div class="row g-3">
    <?php foreach ($categorias as $cat): ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="/src/views/products.php?categoria=<?= urlencode($cat['name']) ?>" class="card category-card text-decoration-none text-body">
          <span class="category-icon"><?= htmlspecialchars($cat['icon']) ?></span>
          <span><?= htmlspecialchars($cat['name']) ?></span>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Productos destacados -->
<section id="productos" class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Productos destacados</h2>
    <a href="/src/views/products.php" class="btn btn-outline-accent btn-sm">Ver todos</a>
  </div>
  <div class="row g-3">
    <?php foreach ($destacados as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card product-card"
             onclick="openProductModal(this)"
             data-id="<?= htmlspecialchars($p['id']) ?>"
             data-name="<?= htmlspecialchars($p['name']) ?>"
             data-category="<?= htmlspecialchars($p['category_name']) ?>"
             data-price="<?= htmlspecialchars($p['price']) ?>"
             data-description="<?= htmlspecialchars($p['description']) ?>"
             data-icon="<?= htmlspecialchars($p['category_icon']) ?>">
          <div class="product-thumb"><?= htmlspecialchars($p['category_icon']) ?></div>
          <div class="card-body">
            <span class="badge badge-stock mb-2"><?= $p['stock'] > 0 ? 'En stock' : 'Sin stock' ?></span>
            <h3 class="h6 card-title mb-1"><?= htmlspecialchars($p['name']) ?></h3>
            <p class="price mb-2">$ <?= number_format($p['price'], 0, ',', '.') ?></p>
            <button type="button" class="btn btn-accent btn-sm w-100"
                    onclick="event.stopPropagation(); addToCart('<?= htmlspecialchars($p['id']) ?>', 1)">
              Agregar al carrito
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Por qué elegirnos -->
<section class="mb-5">
  <div class="row g-3 text-center">
    <div class="col-md-4">
      <div class="feature-icon mb-2">🚚</div>
      <h3 class="h6">Envíos a todo el país</h3>
      <p class="text-secondary small">Recibí tus componentes donde estés.</p>
    </div>
    <div class="col-md-4">
      <div class="feature-icon mb-2">🛡️</div>
      <h3 class="h6">Garantía oficial</h3>
      <p class="text-secondary small">Todos los productos con garantía.</p>
    </div>
    <div class="col-md-4">
      <div class="feature-icon mb-2">💳</div>
      <h3 class="h6">Medios de pago</h3>
      <p class="text-secondary small">Pagá con Mercado Pago de forma simple y segura.</p>
    </div>
  </div>
</section>

<?php include('./_layouts/footer.php'); ?>
