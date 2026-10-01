<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entrega_id'])) {
    $pdo->prepare("UPDATE entregas SET servicio = :s, estado = :e, fecha_entrega = :f WHERE id = :id")
        ->execute([
            ':s'=>$_POST['servicio'],
            ':e'=>$_POST['estado'],
            ':f'=>$_POST['fecha_entrega'] ?: null,
            ':id'=>(int)$_POST['entrega_id']
        ]);
    redirect('/kaia_sv/admin/entregas.php');
}

$entregas = $pdo->query("SELECT e.*, p.numero_pedido, p.nombre_cliente FROM entregas e
                         JOIN pedidos p ON e.pedido_id = p.id ORDER BY e.id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php" class="active">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
  </aside>
  <div class="admin-content">
    <h1>Entregas</h1>
    <table class="admin-table">
      <thead><tr><th>Pedido</th><th>Cliente</th><th>Servicio</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($entregas as $e): ?>
          <tr>
            <form method="POST">
              <input type="hidden" name="entrega_id" value="<?= $e['id'] ?>">
              <td><?= e($e['numero_pedido']) ?></td>
              <td><?= e($e['nombre_cliente']) ?></td>
              <td><input name="servicio" value="<?= e($e['servicio']) ?>" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:6px;border-radius:6px;width:140px;"></td>
              <td>
                <select name="estado" style="background:var(--gris);color:#fff;border:1px solid rgba(255,255,255,.1);padding:6px;border-radius:6px;">
                  <?php foreach (['Pendiente','Asignado','En camino','Entregado','Cancelado'] as $st): ?>
                    <option <?= $e['estado']==$st?'selected':'' ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="date" name="fecha_entrega" value="<?= $e['fecha_entrega'] ? date('Y-m-d', strtotime($e['fecha_entrega'])) : '' ?>" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:6px;border-radius:6px;"></td>
              <td><button class="btn-sm btn-primary-sm" type="submit" style="display:inline-flex;">Guardar</button></td>
            </form>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>