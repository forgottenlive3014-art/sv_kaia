<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
requerir_login();

$uid = (int)$_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria
    FROM wishlist w
    INNER JOIN productos p ON w.producto_id = p.id
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE w.usuario_id = :uid
    ORDER BY w.fecha_agregado DESC
");
$stmt->execute([':uid' => $uid]);
$productos = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section>
  <div class="container">
    <a href="/kaia_sv/catalogo.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al catálogo</a>
    <h1 class="section-title">Mis favoritos <svg class="inline-icon title-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#heart"></use></svg></h1>
    <p class="section-sub">Los productos que guardaste para después.</p>

    <?php if (empty($productos)): ?>
      <div class="form-card" style="text-align:center;">
        <p style="margin-bottom:20px;">Aún no tienes favoritos guardados.</p>
        <a href="/kaia_sv/catalogo.php" class="btn btn-primary">Explorar catálogo</a>
      </div>
    <?php else: ?>
      <div class="prod-grid">
        <?php foreach ($productos as $p): ?>
          <div class="prod-card">
            <div class="prod-img">
              <?php if ($p['imagen'] && file_exists(__DIR__ . '/assets/img/productos/' . $p['imagen'])): ?>
                <img src="/kaia_sv/assets/img/productos/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
              <?php else: ?>
                <i class="fa-solid fa-shirt"></i>
              <?php endif; ?>
              <?php if ($p['condicion'] === 'usada'): ?>
                <span class="prod-badge usada-badge">VINTAGE</span>
              <?php endif; ?>
              <button class="wish-btn active" data-wish="<?= $p['id'] ?>" data-guest="0">
                <i class="fa-solid fa-heart"></i>
              </button>
            </div>
            <div class="prod-body">
              <span class="prod-cat"><?= e($p['categoria']) ?></span>
              <h3 class="prod-name"><?= e($p['nombre']) ?></h3>
              <span class="prod-price">$<?= number_format($p['precio'], 2) ?></span>
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
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>