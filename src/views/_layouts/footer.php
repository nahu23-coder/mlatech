  </main>

  <footer class="site-footer">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <div>
        <strong>MLA <span class="brand-accent">TECH</span></strong>
        <span class="text-secondary small ms-2">Componentes, periféricos y accesorios para tu PC</span>
      </div>
      <div class="text-secondary small">
        <a href="/src/views/contact.php" class="text-secondary">Contacto</a>
        ·
        &copy; <?= date('Y') ?> MLA Tech · Proyecto PDISC 7° Año 4° División
      </div>
    </div>
  </footer>

  <!-- Modal de detalle de producto (se llena por JS con los data-* de cada card) -->
  <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-secondary-subtle">
          <h5 class="modal-title" id="pmName">Producto</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="product-thumb mb-3" id="pmIcon" style="font-size:4rem; border-radius:.5rem;">📦</div>
          <span class="badge badge-stock mb-2" id="pmCategory">Categoría</span>
          <p id="pmDescription" class="text-secondary"></p>
          <p class="price fs-4" id="pmPrice">$ 0</p>
          <div class="d-flex align-items-center gap-2">
            <label for="pmQty" class="form-label mb-0">Cantidad</label>
            <input type="number" id="pmQty" class="form-control form-control-sm" style="width:80px" value="1" min="1">
          </div>
        </div>
        <div class="modal-footer border-secondary-subtle">
          <button type="button" class="btn btn-accent w-100" onclick="addToCart(window.currentProductId, document.getElementById('pmQty').value)">
            Agregar al carrito
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // ---------- Modal de detalle de producto ----------
    function openProductModal(card) {
      window.currentProductId = card.dataset.id;
      document.getElementById('pmName').textContent = card.dataset.name;
      document.getElementById('pmCategory').textContent = card.dataset.category;
      document.getElementById('pmDescription').textContent = card.dataset.description;
      document.getElementById('pmPrice').textContent = '$ ' + Number(card.dataset.price).toLocaleString('es-AR');
      document.getElementById('pmIcon').textContent = card.dataset.icon || '📦';
      document.getElementById('pmQty').value = 1;

      const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('productModal'));
      modal.show();
    }

    // ---------- Agregar al carrito (session-based, vía fetch) ----------
    async function addToCart(productId, qty) {
      try {
        const res = await fetch('/src/controllers/cart/add.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `product_id=${encodeURIComponent(productId)}&qty=${encodeURIComponent(qty)}`
        });
        const data = await res.json();

        if (data.success) {
          const badge = document.querySelector('.navbar .badge');
          if (badge) badge.textContent = data.cart_count;

          const modalEl = document.getElementById('productModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        } else {
          alert(data.error || 'No se pudo agregar el producto al carrito.');
        }
      } catch (err) {
        alert('Error de conexión al agregar el producto.');
      }
    }
  </script>

  <!-- Widget de soporte con IA -->
  <button id="ai-chat-toggle" type="button" aria-label="Abrir soporte">💬</button>
  <div id="ai-chat-window">
    <div id="ai-chat-header">Soporte MLA Tech</div>
    <div id="ai-chat-body">
      <div class="chat-msg bot">¡Hola! Soy el asistente de MLA Tech. Preguntame sobre envíos, garantía, medios de pago o cualquier producto.</div>
    </div>
    <form id="ai-chat-form">
      <input type="text" id="ai-chat-input" placeholder="Escribí tu consulta..." autocomplete="off" required>
      <button type="submit">➤</button>
    </form>
  </div>

  <script>
    const chatToggle = document.getElementById('ai-chat-toggle');
    const chatWindow = document.getElementById('ai-chat-window');
    const chatBody   = document.getElementById('ai-chat-body');
    const chatForm   = document.getElementById('ai-chat-form');
    const chatInput  = document.getElementById('ai-chat-input');

    chatToggle.addEventListener('click', () => chatWindow.classList.toggle('open'));

    function appendMsg(texto, autor) {
      const div = document.createElement('div');
      div.className = 'chat-msg ' + autor;
      div.textContent = texto;
      chatBody.appendChild(div);
      chatBody.scrollTop = chatBody.scrollHeight;
    }

    chatForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const mensaje = chatInput.value.trim();
      if (!mensaje) return;

      appendMsg(mensaje, 'user');
      chatInput.value = '';
      appendMsg('Escribiendo...', 'bot');

      try {
        const res = await fetch('/src/controllers/ai/chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'message=' + encodeURIComponent(mensaje)
        });
        const data = await res.json();
        chatBody.lastChild.remove(); // saca el "Escribiendo..."
        appendMsg(data.reply, 'bot');
      } catch (err) {
        chatBody.lastChild.remove();
        appendMsg('No pude conectarme con el soporte, intentá de nuevo en unos segundos.', 'bot');
      }
    });
  </script>
</body>
</html>
