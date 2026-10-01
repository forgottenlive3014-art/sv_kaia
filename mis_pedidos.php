<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
requerir_login();

$uid = (int)$_SESSION['usuario_id'];

$pedidos = $pdo->prepare("
    SELECT p.*,
           (SELECT COUNT(*) FROM detalle_pedido d WHERE d.pedido_id = p.id) AS items
    FROM pedidos p
    WHERE p.usuario_id = :uid
    ORDER BY p.fecha_pedido DESC
");
$pedidos->execute([':uid' => $uid]);
$pedidos = $pedidos->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <h1 class="section-title">Mis pedidos</h1>
    <p class="section-sub">Historial de tus compras en KAIA_SV.</p>

    <?php if (empty($pedidos)): ?>
      <div class="form-card" style="text-align:center;">
        <p style="margin-bottom:20px;">Aún no tienes pedidos.</p>
        <a href="/kaia_sv/catalogo.php" class="btn btn-primary">Explorar catálogo</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>N° Pedido</th><th>Fecha</th><th>Items</th>
            <th>Total</th><th>Estado</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $p): ?>
            <tr>
              <td><strong><?= e($p['numero_pedido']) ?></strong></td>
              <td><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
              <td><?= (int)$p['items'] ?></td>
              <td>$<?= number_format($p['total'], 2) ?></td>
              <td>
                <span class="badge badge-<?= e(str_replace(' ', '.', $p['estado'])) ?>">
                  <?= e($p['estado']) ?>
                </span>
              </td>
              <td>
                <a href="/kaia_sv/mi_pedido.php?id=<?= $p['id'] ?>" class="btn-sm btn-ghost-sm"
                   style="display:inline-flex;">Ver detalle</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>