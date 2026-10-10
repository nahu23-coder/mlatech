/**
 * Búsqueda de productos (página Productos).
 *
 * Hay UNA sola barra de búsqueda: la de la navbar (#searchInput), que está en todas las páginas.
 *   - En las otras páginas, al apretar Enter navega a products.php?q=...
 *   - Acá, busca en vivo sin recargar y mantiene la URL igual a lo que dice la barra.
 *
 * Flujo: input / Enter / chip -> fetch a search.php (PHP hace la query y responde JSON) -> JS dibuja las cards.
 */
(() => {
  const input     = document.getElementById('searchInput');           // barra de la navbar
  const form      = input ? input.closest('form') : null;
  const chips     = document.querySelectorAll('#filterChips .filter-chip');
  const grid      = document.getElementById('productsGrid');
  const noResults = document.getElementById('noResults');

  // Estado inicial = lo que dice la URL (la barra ya viene con el valor puesto por PHP)
  const urlParams = new URLSearchParams(location.search);
  let categoriaActiva = urlParams.get('categoria') || '';
  if (input) input.value = urlParams.get('q') || '';

  let debounceTimer = null;
  let controller    = null; // para cancelar la búsqueda anterior si el usuario sigue escribiendo

  // ---------- Dibujo ----------
  function formatPrecio(n) {
    return '$ ' + Number(n).toLocaleString('es-AR', { maximumFractionDigits: 0 });
  }

  function crearCard(p) {
    const col = document.createElement('div');
    col.className = 'col-6 col-md-4 col-lg-3 product-item';

    const card = document.createElement('div');
    card.className = 'card product-card';
    // El modal (footer.php) lee estos data-*; dataset escapa los valores solo
    card.dataset.id          = p.id;
    card.dataset.name        = p.name;
    card.dataset.category    = p.category_name;
    card.dataset.price       = p.price;
    card.dataset.description = p.description || '';
    card.dataset.icon        = p.category_icon || '';
    card.dataset.image       = p.image_url || '';
    card.addEventListener('click', () => openProductModal(card));

    const thumb = document.createElement('div');
    thumb.className = 'product-thumb';
    thumb.textContent = p.category_icon || '';       // emoji de la categoría (por defecto)
    if (p.image_url) {
      const img = document.createElement('img');
      img.alt = p.name;
      // OJO: sin loading='lazy'. Esta <img> todavía no está en la página (se inserta recién al cargar)
      // y el navegador no carga imágenes lazy que no están en el documento.
      img.addEventListener('load', () => {            // recién cuando carga, reemplaza el emoji
        thumb.classList.add('has-image');
        thumb.replaceChildren(img);
      });
      img.src = p.image_url;
      // si la imagen no existe, queda el emoji y no se ve el ícono roto
    }

    const body = document.createElement('div');
    body.className = 'card-body';

    const badge = document.createElement('span');
    badge.className = 'badge badge-stock mb-2';
    badge.textContent = p.stock > 0 ? 'En stock' : 'Sin stock';

    const title = document.createElement('h3');
    title.className = 'h6 card-title mb-1';
    title.textContent = p.name;

    const price = document.createElement('p');
    price.className = 'price mb-2';
    price.textContent = formatPrecio(p.price);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-accent btn-sm w-100';
    btn.textContent = 'Agregar al carrito';
    btn.disabled = p.stock <= 0;
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      addToCart(p.id, 1);
    });

    body.append(badge, title, price, btn);
    card.append(thumb, body);
    col.append(card);
    return col;
  }

  function dibujar(productos) {
    grid.replaceChildren(...productos.map(crearCard));
    noResults.textContent = 'No se encontraron productos.';
    noResults.classList.toggle('d-none', productos.length > 0);
  }

  // ---------- URL: siempre refleja lo que dice la barra ----------
  function actualizarURL(texto, categoria) {
    const params = new URLSearchParams();
    if (texto)     params.set('q', texto);
    if (categoria) params.set('categoria', categoria);
    const qs = params.toString();
    history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
  }

  // ---------- Búsqueda ----------
  async function buscar() {
    const texto     = input ? input.value.trim() : '';
    const categoria = categoriaActiva;

    actualizarURL(texto, categoria);

    if (controller) controller.abort();               // cancela la búsqueda anterior
    controller = new AbortController();
    const miController = controller;

    try {
      // PHP hace la query y responde el JSON directamente (no se guarda en disco)
      const params = new URLSearchParams({ q: texto, categoria });
      const res    = await fetch('/src/controllers/products/search.php?' + params, { signal: miController.signal });
      const raw    = await res.text();

      let data;
      try { data = JSON.parse(raw); }
      catch { throw new Error('search.php devolvió HTTP ' + res.status + ' y no es JSON: ' + raw.slice(0, 200)); }
      if (!data.success) throw new Error(data.error || 'Error en la búsqueda');

      dibujar(data.products);
    } catch (err) {
      if (err.name === 'AbortError') return;          // fue reemplazada por una búsqueda más nueva
      console.error('[búsqueda]', err);
      grid.replaceChildren();
      noResults.textContent = 'No se pudieron cargar los productos: ' + err.message;
      noResults.classList.remove('d-none');
    }
  }

  // ---------- Eventos ----------
  if (input) {
    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(buscar, 250);        // espera a que termine de tipear
    });
    input.addEventListener('search', () => {          // la "x" de borrar del input type=search
      clearTimeout(debounceTimer);
      buscar();
    });
  }

  if (form) {
    form.addEventListener('submit', (e) => {          // Enter: busca acá, sin recargar la página
      e.preventDefault();
      clearTimeout(debounceTimer);
      buscar();
    });
  }

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      categoriaActiva = chip.dataset.categoria;
      chips.forEach(c => c.classList.toggle('active', c === chip));
      buscar();
    });
  });

  // Chip activo inicial (si venimos de un link de categoría)
  chips.forEach(c => c.classList.toggle('active', c.dataset.categoria === categoriaActiva));

  buscar(); // carga inicial
})();