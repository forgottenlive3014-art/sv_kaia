<?php
require_once __DIR__ . '/config/conexion.php';
$info = $_SESSION['ultimo_pedido'] ?? null;
if (!$info) redirect('/kaia_sv/index.php');
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
?>
<section>
  <div class="container">
    <div class="form-card" style="text-align:center;max-width:520px;">
      <svg class="confirmation-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#check"></use></svg>
      <h2>¡Pedido realizado correctamente!</h2>
      <p style="margin:20px 0;color:var(--texto-suave);">Guarda tu número de pedido:</p>
      <div style="background:rgba(255,255,255,.05);padding:20px;border-radius:14px;margin-bottom:20px;">
        <p style="font-size:1.4rem;font-weight:700;color:var(--rosa);"><?= e($info['numero']) ?></p>
      </div>
      <p><strong>Total:</strong> $<?= number_format($info['total'], 2) ?></p>
      <p><strong>Método:</strong> <?= e(ucfirst(str_replace('_',' ', $info['metodo']))) ?></p>
      <p><strong>Estado:</strong> <span class="badge badge-Pendiente">Pendiente</span></p>
      <a href="/kaia_sv/catalogo.php" class="btn btn-primary" style="margin-top:24px;"><svg class="inline-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al catálogo</a>
    </div>
  </div>
</section>
<?php unset($_SESSION['ultimo_pedido']); include __DIR__ . '/includes/footer.php'; ?>