<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
requerir_login();

$uid = (int)$_SESSION['usuario_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = :id AND usuario_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $uid]);
$p = $stmt->fetch();

if (!$p) redirect('/kaia_sv/mis_pedidos.php');

$items = $pdo->prepare("
    SELECT d.*, pr.nombre, pr.imagen
    FROM detalle_pedido d
    INNER JOIN productos pr ON d.producto_id = pr.id
    WHERE d.pedido_id = :id
");
$items->execute([':id' => $id]);
$items = $items->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <a href="/kaia_sv/mis_pedidos.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Mis pedidos</a>
    <h1 class="section-title" style="margin-top:14px;">Pedido <?= e($p['numero_pedido']) ?></h1>

    <div class="form-card" style="margin:0 0 24px;max-width:none;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></p>
        <p><strong>Estado:</strong>
          <span class="badge badge-<?= e(str_replace(' ', '.', $p['estado'])) ?>">
            <?= e($p['estado']) ?>
          </span>
        </p>
        <p><strong>Método de entrega:</strong> <?= e(ucfirst(str_replace('_', ' ', $p['metodo_entrega']))) ?></p>
        <p><strong>Dirección:</strong> <?= e($p['direccion']) ?></p>
        <p><strong>Teléfono:</strong> <?= e($p['telefono']) ?></p>
        <p><strong>Total:</strong>
          <span style="color:var(--lima);font-weight:700;">$<?= number_format($p['total'], 2) ?></span>
        </p>
      </div>
      <?php if ($p['observaciones']): ?>
        <p style="margin-top:14px;"><strong>Observaciones:</strong> <?= e($p['observaciones']) ?></p>
      <?php endif; ?>
    </div>

    <h2 style="margin-bottom:14px;">Productos</h2>
    <table class="admin-table">
  <thead><tr><th>Producto</th><th>Talla</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
  <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= e($it['nombre']) ?></td>
        <td><span class="badge badge-Confirmado"><?= e($it['talla']) ?></span></td>
        <td><?= (int)$it['cantidad'] ?></td>
        <td>$<?= number_format($it['precio_unitario'], 2) ?></td>
        <td>$<?= number_format($it['subtotal'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>