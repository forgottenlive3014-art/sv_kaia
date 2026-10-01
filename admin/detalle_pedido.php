<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

$id = (int)($_GET['id'] ?? 0);
$pedido = $pdo->prepare("SELECT * FROM pedidos WHERE id = :id");
$pedido->execute([':id'=>$id]);
$p = $pedido->fetch();
if (!$p) redirect('/kaia_sv/admin/pedidos.php');

$items = $pdo->prepare("SELECT d.*, pr.nombre FROM detalle_pedido d JOIN productos pr ON d.producto_id = pr.id WHERE d.pedido_id = :id");
$items->execute([':id'=>$id]);
$items = $items->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php" class="active">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
  </aside>
  <div class="admin-content">
    <h1>Pedido <?= e($p['numero_pedido']) ?></h1>
    <div class="form-card" style="margin:0 0 24px;max-width:none;">
      <p><strong>Cliente:</strong> <?= e($p['nombre_cliente']) ?></p>
      <p><strong>Teléfono:</strong> <?= e($p['telefono']) ?></p>
      <p><strong>Correo:</strong> <?= e($p['correo']) ?></p>
      <p><strong>Dirección:</strong> <?= e($p['direccion']) ?></p>
      <p><strong>Método:</strong> <?= e(ucfirst(str_replace('_',' ',$p['metodo_entrega']))) ?></p>
      <p><strong>Observaciones:</strong> <?= e($p['observaciones']) ?></p>
      <p><strong>Estado:</strong> <span class="badge badge-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span></p>
      <p><strong>Total:</strong> $<?= number_format($p['total'],2) ?></p>
    </div>
    <h2>Productos</h2>
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
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>