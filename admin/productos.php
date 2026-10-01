<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

$msg = $_SESSION['admin_msg'] ?? null;
unset($_SESSION['admin_msg']);

$productos = $pdo->query("SELECT p.*, c.nombre AS categoria FROM productos p
                          LEFT JOIN categorias c ON p.categoria_id = c.id
                          ORDER BY p.id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php" class="active">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
  </aside>
  <div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h1>Productos</h1>
      <a href="/kaia_sv/admin/agregar_producto.php" class="btn btn-primary">+ Agregar producto</a>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msg['tipo'] === 'success' ? 'success' : 'error' ?>">
        <?= e($msg['texto']) ?>
      </div>
    <?php endif; ?>

    <table class="admin-table">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td>#<?= $p['id'] ?></td>
            <td><?= e($p['nombre']) ?></td>
            <td><?= e($p['categoria']) ?></td>
            <td>$<?= number_format($p['precio'], 2) ?></td>
            <td><?= (int)$p['stock'] ?></td>
            <td style="display:flex;gap:6px;">
              <a href="/kaia_sv/admin/editar_producto.php?id=<?= $p['id'] ?>"
                 class="btn-sm btn-ghost-sm" style="display:inline-flex;">Editar</a>

              <form method="POST" action="/kaia_sv/admin/eliminar_producto.php"
                    onsubmit="return confirm('¿Eliminar el producto &quot;<?= e($p['nombre']) ?>&quot;? Esta acción no se puede deshacer.');"
                    style="display:inline;">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit"
                        class="btn-sm"
                        style="background:rgba(255,80,80,.2);color:#ff8080;display:inline-flex;">
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>