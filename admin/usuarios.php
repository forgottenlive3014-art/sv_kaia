<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();
$usuarios = $pdo->query("SELECT id, nombre, correo, telefono, rol, fecha_registro FROM usuarios ORDER BY id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php" class="active">Usuarios</a>
  </aside>
  <div class="admin-content">
    <h1>Usuarios</h1>
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Registro</th></tr></thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['nombre']) ?></td>
            <td><?= e($u['correo']) ?></td>
            <td><?= e($u['telefono']) ?></td>
            <td><?= e($u['rol']) ?></td>
            <td><?= e($u['fecha_registro']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>