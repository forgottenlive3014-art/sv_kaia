<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';

$buscar = trim($_GET['buscar'] ?? '');
$categoria = (int)($_GET['categoria'] ?? 0);
$precio_max = (float)($_GET['precio_max'] ?? 0);
$disponible = $_GET['disponible'] ?? '';
$condicion = $_GET['condicion'] ?? '';

$sql = "SELECT p.*, c.nombre AS categoria FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND p.nombre LIKE :buscar";
    $params[':buscar'] = "%$buscar%";
}
if ($categoria > 0) {
    $sql .= " AND p.categoria_id = :cat";
    $params[':cat'] = $categoria;
}
if ($precio_max > 0) {
    $sql .= " AND p.precio <= :pmax";
    $params[':pmax'] = $precio_max;
}
if ($disponible === '1') {
    $sql .= " AND p.stock > 0";
}
if ($condicion === 'nueva' || $condicion === 'usada') {
    $sql .= " AND p.condicion = :cond";
    $params[':cond'] = $condicion;
}
$sql .= " ORDER BY p.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

$misWish = [];
if (esta_logueado()) {
    $stmtW = $pdo->prepare("SELECT producto_id FROM wishlist WHERE usuario_id = :uid");
    $stmtW->execute([':uid' => (int)$_SESSION['usuario_id']]);
    $misWish = array_column($stmtW->fetchAll(), 'producto_id');
}

include __DIR__ . '/includes/header.php';
?>

<section>
  <div class="container">
    <h1 class="section-title">Catálogo</h1>
    <p class="section-sub">
      Explora toda la colección KAIA_SV.
      <?php if ($condicion === 'nueva'): ?>
        <br><strong>Filtrando: Ropa Nueva 🆕</strong>
      <?php elseif ($condicion === 'usada'): ?>
        <br><strong>Filtrando: Ropa Usada / Vintage</strong>
      <?php endif; ?>
    </p>

    <form class="filters-bar" method="GET" action="/kaia_sv/catalogo.php">
      <div class="field">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Nombre del producto..." value="<?= e($buscar) ?>">
      </div>
      <div class="field">
        <label>Categoría</label>
        <select name="categoria">
          <option value="0">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $categoria == $c['id'] ? 'selected' : '' ?>>
              <?= e($c['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Condición</label>
        <select name="condicion">
          <option value="">Todas</option>
          <option value="nueva" <?= $condicion === 'nueva' ? 'selected' : '' ?>>Nueva</option>
          <option value="usada" <?= $condicion === 'usada' ? 'selected' : '' ?>>Usada / Vintage</option>
        </select>
      </div>
      <div class="field">
        <label>Precio máx.</label>
        <input type="number" name="precio_max" min="0" step="0.01" placeholder="Ej: 30"
               value="<?= $precio_max ?: '' ?>">
      </div>
      <div class="field">
        <label>Disponible</label>
        <select name="disponible">
          <option value="">Todos</option>
          <option value="1" <?= $disponible === '1' ? 'selected' : '' ?>>Solo con stock</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <div class="prod-grid">
      <?php if (empty($productos)): ?>
        <p style="color: var(--texto-suave);">No se encontraron productos con esos filtros.</p>
      <?php else: foreach ($productos as $p): ?>
        <div class="prod-card">
          <div class="prod-img">
            <?php if ($p['imagen'] && file_exists(__DIR__ . '/assets/img/productos/' . $p['imagen'])): ?>
              <img src="/kaia_sv/assets/img/productos/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
            <?php else: ?>
              <i class="fa-solid fa-shirt"></i>
            <?php endif; ?>

            <?php if ($p['stock'] <= 0): ?>
              <span class="prod-badge" style="background:#ff5f5f;color:#fff;">AGOTADO</span>
            <?php elseif ($p['condicion'] === 'usada'): ?>
              <span class="prod-badge usada-badge">VINTAGE</span>
            <?php endif; ?>

            <button class="wish-btn <?= in_array($p['id'], $misWish) ? 'active' : '' ?>"
                    data-wish="<?= $p['id'] ?>"
                    data-guest="<?= esta_logueado() ? '0' : '1' ?>"
                    aria-label="Favorito">
              <i class="<?= in_array($p['id'], $misWish) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            </button>
          </div>
          <div class="prod-body">
            <span class="prod-cat"><?= e($p['categoria']) ?></span>
            <h3 class="prod-name"><?= e($p['nombre']) ?></h3>
            <span class="prod-price">$<?= number_format($p['precio'], 2) ?></span>
            <span style="font-size:.82rem;color:var(--texto-suave);">
              Talla: <?= e($p['talla']) ?> · Stock: <?= (int)$p['stock'] ?>
            </span>
            <div class="prod-actions">
              <a href="/kaia_sv/producto.php?id=<?= $p['id'] ?>" class="btn-sm btn-ghost-sm">Ver</a>
              <?php if ($p['stock'] > 0): ?>
                <button class="btn-sm btn-primary-sm"
        data-add-cart="<?= $p['id'] ?>"
        data-nombre="<?= e($p['nombre']) ?>">
  <i class="fa-solid fa-cart-plus"></i>
</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>