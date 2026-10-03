/**
 * Buscador de productos.
 *
 * Flujo:
 *   input / chip de categoría
 *     -> fetch a search.php (PHP hace la query y escribe el .json)
 *     -> fetch del .json
 *     -> JS dibuja las cards
 */
(() => {
  const input      = document.getElementById('searchInput');
  const chips      = document.querySelectorAll('#filterChips .filter-chip');
  const grid       = document.getElementById('productsGrid');
  const noResults  = document.getElementById('noResults');

  let categoriaActiva = grid.dataset.categoriaInicial || '';
  let debounceTimer   = null;
  let requestId       = 0; // para ignorar respuestas viejas si el usuario sigue escribiendo

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

  // ---------- Búsqueda ----------
  async function buscar() {
    const miId      = ++requestId;
    const texto     = input.value.trim();
    const categoria = categoriaActiva;

    try {
      // 1) PHP hace la query y genera el .json
      const params = new URLSearchParams({ q: texto, categoria });
      const res    = await fetch('/src/controllers/products/search.php?' + params);
      const raw    = await res.text();
      let meta;
      try { meta = JSON.parse(raw); }
      catch { throw new Error('search.php devolvió HTTP ' + res.status + ' y no es JSON: ' + raw.slice(0, 200)); }
      if (!meta.success) throw new Error(meta.error || 'Error en la búsqueda');
      if (miId !== requestId) return; // ya hay una búsqueda más nueva

      // 2) Leemos el .json (el ?t= evita que el navegador use una copia cacheada)
      const jsonRes = await fetch(meta.file + '?t=' + Date.now(), { cache: 'no-store' });
      if (!jsonRes.ok) throw new Error('No se pudo leer ' + meta.file + ' (HTTP ' + jsonRes.status + ')');
      const data    = await jsonRes.json();

      // El archivo es compartido por la sesión: si es de otra búsqueda, esa se encarga de dibujar
      if (miId !== requestId || data.query !== texto || data.categoria !== categoria) return;

      // 3) Dibujamos
      dibujar(data.products);
    } catch (err) {
      if (miId !== requestId) return;
      console.error('[búsqueda]', err);
      grid.replaceChildren();
      noResults.textContent = 'No se pudieron cargar los productos: ' + err.message;
      noResults.classList.remove('d-none');
    }
  }

  // ---------- Eventos ----------
  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(buscar, 250); // espera a que termine de tipear
  });

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      categoriaActiva = chip.dataset.categoria;
      buscar();
    });
  });

  // Chip activo inicial (si venimos de un link de categoría)
  chips.forEach(c => c.classList.toggle('active', c.dataset.categoria === categoriaActiva));

  buscar(); // carga inicial
})();