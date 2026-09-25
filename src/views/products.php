<?php
include('./_layouts/layout.php');

$categorias = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$productos = $pdo->query(
  "SELECT p.*, c.name AS category_name, c.icon AS category_icon
   FROM products p
   JOIN categories c ON c.id = p.category_id
   ORDER BY c.name, p.name"
)->fetchAll();

// Si venimos de un link de categoría (ej: desde el home) o de una búsqueda por navbar,
// los tomamos para dejarlos precargados; el filtrado real se hace en el navegador con JS.
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

<div class="row g-3" id="productsGrid">
  <?php foreach ($productos as $p): ?>
    <div class="col-6 col-md-4 col-lg-3 product-item"
         data-categoria="<?= htmlspecialchars($p['category_name']) ?>"
         data-search="<?= htmlspecialchars(mb_strtolower($p['name'] . ' ' . $p['description'])) ?>">
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
                  onclick="event.stopPropagation(); addToCart('<?= htmlspecialchars($p['id']) ?>', 1)"
                  <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
            Agregar al carrito
          </button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<p id="noResults" class="text-secondary text-center mt-4 d-none">No se encontraron productos.</p>

<script>
  const searchInput = document.getElementById('searchInput');
  const filterChips = document.querySelectorAll('#filterChips .filter-chip');
  const productItems = document.querySelectorAll('.product-item');
  const noResults = document.getElementById('noResults');

  let categoriaActiva = <?= json_encode($categoriaInicial) ?>;

  function aplicarFiltros() {
    const texto = searchInput.value.trim().toLowerCase();
    let visibles = 0;

    productItems.forEach(item => {
      const coincideCategoria = !categoriaActiva || item.dataset.categoria === categoriaActiva;
      const coincideTexto = !texto || item.dataset.search.includes(texto);
      const mostrar = coincideCategoria && coincideTexto;
      item.classList.toggle('d-none', !mostrar);
      if (mostrar) visibles++;
    });

    noResults.classList.toggle('d-none', visibles > 0);
  }

  searchInput.addEventListener('input', aplicarFiltros);

  filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
      filterChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      categoriaActiva = chip.dataset.categoria;
      aplicarFiltros();
    });
  });

  // Precarga del filtro de categoría si venimos de un link (ej: desde el home)
  if (categoriaActiva) {
    filterChips.forEach(c => {
      c.classList.toggle('active', c.dataset.categoria === categoriaActiva);
    });
  }
  aplicarFiltros();
</script>

<?php include('./_layouts/footer.php'); ?>
