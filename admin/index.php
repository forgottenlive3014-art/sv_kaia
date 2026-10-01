<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

$totalProductos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$ingresos = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado != 'Cancelado'")->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="active">Dashboard</a>
    <a href="/kaia_sv/admin/productos.php">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
    <a href="/kaia_sv/logout.php">Salir</a>
  </aside>
  <div class="admin-content">
    <h1>Dashboard</h1>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
      <div class="form-card" style="margin:0;"><h3><?= $totalProductos ?></h3><p style="color:var(--texto-suave);">Productos</p></div>
      <div class="form-card" style="margin:0;"><h3><?= $totalPedidos ?></h3><p style="color:var(--texto-suave);">Pedidos</p></div>
      <div class="form-card" style="margin:0;"><h3><?= $totalUsuarios ?></h3><p style="color:var(--texto-suave);">Usuarios</p></div>
      <div class="form-card" style="margin:0;"><h3>$<?= number_format($ingresos,2) ?></h3><p style="color:var(--texto-suave);">Ingresos</p></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>