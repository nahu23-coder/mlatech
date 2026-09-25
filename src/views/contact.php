<?php
include('./_layouts/layout.php');

$enviado = isset($_GET['ok']);
?>

<h1 class="h3 mb-4">Contacto y soporte</h1>

<div class="row">
  <div class="col-lg-7 mb-4">
    <?php if ($enviado): ?>
      <div class="alert alert-success">¡Gracias! Recibimos tu mensaje, te vamos a responder a la brevedad.</div>
    <?php endif; ?>

    <form action="/src/controllers/contact/send.php" method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="subject" class="form-label">Asunto</label>
        <input type="text" class="form-control" id="subject" name="subject" required>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Mensaje</label>
        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
      </div>
      <button type="submit" class="btn btn-accent">Enviar mensaje</button>
    </form>
  </div>

  <div class="col-lg-5">
    <div class="card p-4">
      <h2 class="h5">¿Preferís una respuesta al instante?</h2>
      <p class="text-secondary">
        Usá el botón de chat 💬 en la esquina inferior derecha: nuestro asistente responde
        preguntas frecuentes sobre envíos, garantía, medios de pago y productos las 24hs.
      </p>
      <hr class="border-secondary-subtle">
      <p class="mb-1"><strong>Horario de atención humana:</strong></p>
      <p class="text-secondary mb-0">Lunes a viernes de 9 a 18hs</p>
    </div>
  </div>
</div>

<?php include('./_layouts/footer.php'); ?>
