<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php';

$items = $_SESSION['carrito'] ?? [];
$total = 0;
foreach ($items as $i) $total += $i['precio'] * $i['cantidad'];
?>

<section>
  <div class="container">
    <a href="/kaia_sv/catalogo.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al catálogo</a>
    <h1 class="section-title">Tu carrito</h1>
    <p class="section-sub">Revisa tus productos antes de confirmar.</p>

    <?php if (empty($items)): ?>
      <div class="form-card" style="text-align:center;">
        <p style="margin-bottom:20px;">Tu carrito está vacío.</p>
        <a href="/kaia_sv/catalogo.php" class="btn btn-primary">Explorar catálogo</a>
      </div>
    <?php else: ?>
      <div class="cart-table-wrap">
      <table class="cart-table">
        <thead>
          <tr><th>Producto</th><th>Talla</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $clave => $it): ?>
            <tr>
              <td><?= e($it['nombre']) ?></td>
              <td><span class="badge badge-Confirmado"><?= e($it['talla']) ?></span></td>
              <td>$<?= number_format($it['precio'], 2) ?></td>
              <td>
                  <input type="number" class="qty-input" aria-label="Cantidad de <?= e($it['nombre']) ?>"
                       data-clave="<?= e($clave) ?>"
                       value="<?= $it['cantidad'] ?>" min="1">
              </td>
              <td>$<?= number_format($it['precio'] * $it['cantidad'], 2) ?></td>
              <td>
                <button type="button" data-remove-cart="<?= e($clave) ?>"
                  data-product-name="<?= e($it['nombre']) ?>"
                  aria-label="Eliminar <?= e($it['nombre']) ?> del carrito"
                        style="background:none;color:#ff8080;font-size:1.1rem;">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>

      <div class="cart-confirm-backdrop" id="cartDeleteModal" hidden>
        <section class="cart-confirm-dialog" role="dialog" aria-modal="true"
                 aria-labelledby="cartDeleteTitle" aria-describedby="cartDeleteMessage">
          <button type="button" class="cart-confirm-close" data-close-cart-modal aria-label="Cerrar">
            <i class="fa-solid fa-xmark"></i>
          </button>
          <div class="cart-confirm-icon"><i class="fa-solid fa-trash"></i></div>
          <h2 id="cartDeleteTitle">Eliminar producto</h2>
          <p id="cartDeleteMessage">¿Deseas eliminar este producto del carrito?</p>
          <div class="cart-confirm-actions">
            <button type="button" class="btn btn-outline" data-close-cart-modal>Cancelar</button>
            <button type="button" class="btn cart-confirm-delete" id="confirmCartDelete">Eliminar</button>
          </div>
        </section>
      </div>

      <div class="cart-total-box">
        <div class="cart-total-row total">
          <span>Total</span>
          <span>$<?= number_format($total, 2) ?></span>
        </div>
        <div style="display:flex;gap:14px;margin-top:20px;flex-wrap:wrap;">
          <a href="/kaia_sv/catalogo.php" class="btn btn-outline">Seguir comprando</a>
          <a href="/kaia_sv/pedido.php" class="btn btn-primary">Realizar pedido <svg class="inline-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-right"></use></svg></a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>