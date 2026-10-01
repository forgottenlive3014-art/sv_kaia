<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí podrías guardar en una tabla contacto. Simplificado:
    $ok = true;
}
include __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <h1 class="section-title">Contacto</h1>
    <p class="section-sub">Escríbenos, te responderemos pronto.</p>
    <div class="form-card">
      <?php if ($ok): ?><div class="alert alert-success">¡Mensaje enviado! Gracias por contactarnos.</div><?php endif; ?>
      <form method="POST" class="form-grid">
        <div class="field"><label>Nombre</label><input type="text" name="nombre" required></div>
        <div class="field"><label>Correo</label><input type="email" name="correo" required></div>
        <div class="field"><label>Mensaje</label><textarea name="mensaje" rows="4" required></textarea></div>
        <button type="submit" class="btn btn-primary">Enviar mensaje</button>
      </form>
      <div style="margin-top:24px;color:var(--texto-suave);font-size:.9rem;">
        <p><i class="fa-solid fa-envelope"></i> hola@kaia.sv</p>
        <p><i class="fa-solid fa-phone"></i> +503 7777-0000</p>
        <p><i class="fa-solid fa-location-dot"></i> San Salvador, El Salvador</p>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>