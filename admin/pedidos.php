<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'], $_POST['estado'])) {
    $pdo->prepare("UPDATE pedidos SET estado = :e WHERE id = :id")
        ->execute([':e'=>$_POST['estado'], ':id'=>(int)$_POST['pedido_id']]);
    redirect('/kaia_sv/admin/pedidos.php');
}

$pedidos = $pdo->query("SELECT * FROM pedidos ORDER BY id DESC")->fetchAll();
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
    <h1>Pedidos</h1>
    <table class="admin-table">
      <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Método</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($pedidos as $p): ?>
          <tr>
            <td><?= e($p['numero_pedido']) ?></td>
            <td><?= e($p['nombre_cliente']) ?><br><small style="color:var(--texto-suave);"><?= e($p['telefono']) ?></small></td>
            <td>$<?= number_format($p['total'],2) ?></td>
            <td><?= e(ucfirst(str_replace('_',' ',$p['metodo_entrega']))) ?></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;">
                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                <select name="estado" onchange="this.form.submit()" style="background:var(--gris);color:#fff;border:1px solid rgba(255,255,255,.1);padding:6px;border-radius:8px;">
                  <?php foreach (['Pendiente','Confirmado','Preparando','En camino','Entregado','Cancelado'] as $e): ?>
                    <option <?= $p['estado']==$e?'selected':'' ?>><?= $e ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td><a href="/kaia_sv/admin/detalle_pedido.php?id=<?= $p['id'] ?>" class="btn-sm btn-ghost-sm" style="display:inline-flex;">Ver</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>