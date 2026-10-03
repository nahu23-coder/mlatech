<?php
include('./_layouts/layout.php');

$categorias = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Si venimos de un link de categoría (ej: desde el home) o de una búsqueda por navbar,
// los tomamos para dejarlos precargados; la búsqueda real la hace search.php y JS dibuja el resultado.
$categoriaInicial = $_GET['categoria'] ?? '';
$busquedaInicial  = $_GET['q'] ?? '';
?>

<h1 class="h3 mb-4">Productos</h1>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o descripción..."
           value="<?= htmlspecialchars($busquedaInicial) ?>">
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4" id="filterChips">
  <button type="button" class="btn btn-sm filter-chip active" data-categoria="">Todas</button>
  <?php foreach ($categorias as $cat): ?>
    <button type="button" class="btn btn-sm filter-chip" data-categoria="<?= htmlspecialchars($cat['name']) ?>">
      <?= htmlspecialchars($cat['icon']) ?> <?= htmlspecialchars($cat['name']) ?>
    </button>
  <?php endforeach; ?>
</div>

<div class="row g-3" id="productsGrid" data-categoria-inicial="<?= htmlspecialchars($categoriaInicial) ?>"></div>

<p id="noResults" class="text-secondary text-center mt-4 d-none">No se encontraron productos.</p>

<script src="/assets/js/products-search.js"></script>

<?php include('./_layouts/footer.php'); ?> 