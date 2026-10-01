<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria FROM productos p
                       LEFT JOIN categorias c ON p.categoria_id = c.id
                       WHERE p.id = :id");
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p) redirect('/kaia_sv/catalogo.php');

// Tallas
$stmtT = $pdo->prepare("
    SELECT talla, stock FROM producto_tallas
    WHERE producto_id = :id
    ORDER BY FIELD(talla,'XS','S','M','L','XL','XXL','Única')
");
$stmtT->execute([':id' => $id]);
$tallas = $stmtT->fetchAll();

$enWish = false;
if (esta_logueado()) {
    $w = $pdo->prepare("SELECT id FROM wishlist WHERE usuario_id = :u AND producto_id = :p");
    $w->execute([':u' => (int)$_SESSION['usuario_id'], ':p' => $id]);
    $enWish = (bool)$w->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<section>
  <div class="container">
    <a href="/kaia_sv/catalogo.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al catálogo</a>

    <div class="producto-detalle">
      <?php if ($p['imagen'] && file_exists(__DIR__ . '/assets/img/productos/' . $p['imagen'])): ?>
        <button type="button" class="producto-img-grande imagen-zoom-trigger"
                data-image-modal-open aria-label="Ampliar imagen de <?= e($p['nombre']) ?>">
          <img src="/kaia_sv/assets/img/productos/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
        </button>
      <?php else: ?>
        <div class="producto-img-grande">
          <i class="fa-solid fa-shirt"></i>
        </div>
      <?php endif; ?>

      <div class="producto-info">
        <span class="prod-cat"><?= e($p['categoria']) ?></span>
        <?php if ($p['condicion'] === 'usada'): ?>
          <span class="badge badge-Pendiente">VINTAGE</span>
        <?php endif; ?>

        <h1><?= e($p['nombre']) ?></h1>
        <p class="producto-precio">$<?= number_format($p['precio'], 2) ?></p>

        <p class="producto-desc"><?= nl2br(e($p['descripcion'])) ?></p>

        <div class="info-tabla">
          <h3>Detalles del producto</h3>
          <div class="info-row"><span class="label">Material</span><span class="value"><?= e($p['material'] ?: 'No especificado') ?></span></div>
          <div class="info-row"><span class="label">Medidas</span><span class="value"><?= e($p['medidas'] ?: 'No especificadas') ?></span></div>
          <div class="info-row"><span class="label">Cuidados</span><span class="value"><?= e($p['cuidados'] ?: 'No especificados') ?></span></div>
          <div class="info-row"><span class="label">Marca</span><span class="value"><?= e($p['marca']) ?></span></div>
          <div class="info-row"><span class="label">Origen</span><span class="value"><?= e($p['origen']) ?></span></div>
          <div class="info-row"><span class="label">Condición</span><span class="value"><?= e(ucfirst($p['condicion'])) ?></span></div>
        </div>

      </div>

      <?php if ($p['stock'] > 0): ?>
        <div class="producto-acciones">
          <div class="acciones-der">
            <p class="acciones-label">Selecciona tu talla</p>
            <div class="talla-selector" id="tallaSelector">
            <?php if (empty($tallas)): ?>
              <button type="button" class="talla-btn selected" data-talla="Única" data-stock="<?= (int)$p['stock'] ?>">Única</button>
            <?php else: foreach ($tallas as $t): ?>
              <button type="button" class="talla-btn"
                      data-talla="<?= e($t['talla']) ?>"
                      data-stock="<?= (int)$t['stock'] ?>"
                      <?= (int)$t['stock'] === 0 ? 'disabled' : '' ?>>
                <?= e($t['talla']) ?>
              </button>
            <?php endforeach; endif; ?>
            </div>
          </div>

          <div class="acciones-izq">
            <div class="field field-cantidad">
              <label for="cantidadProducto">Cantidad</label>
              <input type="number" id="cantidadProducto" value="1" min="1" max="1">
            </div>
            <button class="btn btn-primary" id="btnAgregarProd"
                    data-add-cart="<?= (int)$p['id'] ?>"
                    data-nombre="<?= e($p['nombre']) ?>">
              <i class="fa-solid fa-cart-plus"></i> Agregar al carrito
            </button>
            <button type="button" class="btn btn-outline btn-wish"
                    data-wish="<?= $p['id'] ?>"
                    data-guest="<?= esta_logueado() ? '0' : '1' ?>"
                    aria-label="<?= $enWish ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>"
                    title="<?= $enWish ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>">
              <i class="<?= $enWish ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            </button>
          </div>
        </div>
      <?php else: ?>
        <p class="producto-acciones agotado-msg">Producto agotado</p>
      <?php endif; ?>
    </div>

    <?php if ($p['imagen'] && file_exists(__DIR__ . '/assets/img/productos/' . $p['imagen'])): ?>
      <div class="producto-imagen-modal" id="productoImagenModal" role="dialog"
           aria-modal="true" aria-label="Imagen ampliada de <?= e($p['nombre']) ?>" hidden>
        <button type="button" class="producto-imagen-cerrar" aria-label="Cerrar imagen">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <img src="/kaia_sv/assets/img/productos/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function() {
  const selector = document.getElementById('tallaSelector');
  const qtyInput = document.getElementById('cantidadProducto');
  const btnAgregar = document.getElementById('btnAgregarProd');
  let tallaSel = null;

  if (selector) {
    selector.querySelectorAll('.talla-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.disabled) return;
        selector.querySelectorAll('.talla-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        tallaSel = btn.dataset.talla;
        const max = parseInt(btn.dataset.stock) || 1;
        qtyInput.max = max;
        qtyInput.value = Math.min(parseInt(qtyInput.value) || 1, max);
        btnAgregar.dataset.talla = tallaSel;
        btnAgregar.dataset.cantidad = qtyInput.value;
      });
    });
    const tallaInicial = selector.querySelector('.talla-btn.selected:not(:disabled)')
      || selector.querySelector('.talla-btn:not(:disabled)');
    if (tallaInicial) tallaInicial.click();
  }

  qtyInput.addEventListener('change', () => {
    const max = parseInt(qtyInput.max, 10) || 1;
    qtyInput.value = Math.min(Math.max(parseInt(qtyInput.value, 10) || 1, 1), max);
    btnAgregar.dataset.cantidad = qtyInput.value;
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>