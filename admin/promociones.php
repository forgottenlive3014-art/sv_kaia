<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar'])) {
        $pdo->prepare("DELETE FROM promociones WHERE id = :id")->execute([':id'=>(int)$_POST['eliminar']]);
    } else {
        $pdo->prepare("INSERT INTO promociones (nombre, descripcion, descuento, fecha_inicio, fecha_fin, estado)
                       VALUES (:n,:d,:de,:fi,:ff,:e)")
            ->execute([
                ':n'=>$_POST['nombre'],':d'=>$_POST['descripcion'],
                ':de'=>(int)$_POST['descuento'],':fi'=>$_POST['fecha_inicio'],
                ':ff'=>$_POST['fecha_fin'],':e'=>$_POST['estado']
            ]);
    }
    redirect('/kaia_sv/admin/promociones.php');
}

$promos = $pdo->query("SELECT * FROM promociones ORDER BY id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php" class="active">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
  </aside>
  <div class="admin-content">
    <h1>Promociones</h1>
    <div class="form-card" style="margin:0 0 24px;max-width:none;">
      <h3 style="margin-bottom:16px;">Nueva promoción</h3>
      <form method="POST" class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
        <div class="field"><label>Nombre</label><input name="nombre" required></div>
        <div class="field"><label>Descripción</label><input name="descripcion"></div>
        <div class="field"><label>% Descuento</label><input type="number" name="descuento" value="10"></div>
        <div class="field"><label>Inicio</label><input type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>"></div>
        <div class="field"><label>Fin</label><input type="date" name="fecha_fin" value="<?= date('Y-m-d', strtotime('+30 days')) ?>"></div>
        <div class="field"><label>Estado</label>
          <select name="estado"><option value="activa">Activa</option><option value="inactiva">Inactiva</option></select>
        </div>
        <button class="btn btn-primary" type="submit">Crear</button>
      </form>
    </div>
    <table class="admin-table">
      <thead><tr><th>Nombre</th><th>Descuento</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($promos as $pr): ?>
          <tr>
            <td><?= e($pr['nombre']) ?></td>
            <td><?= $pr['descuento'] ?>%</td>
            <td><?= e($pr['fecha_inicio']) ?> → <?= e($pr['fecha_fin']) ?></td>
            <td><?= e($pr['estado']) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                <input type="hidden" name="eliminar" value="<?= $pr['id'] ?>">
                <button class="btn-sm" style="background:rgba(255,80,80,.2);color:#ff8080;">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>