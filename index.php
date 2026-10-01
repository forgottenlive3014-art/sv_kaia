<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';

$destacados = $pdo->query("
  SELECT p.*, c.nombre AS categoria
  FROM productos p
  LEFT JOIN categorias c ON p.categoria_id = c.id
  WHERE p.destacado = 1 AND p.stock > 0
  ORDER BY p.fecha_creacion DESC LIMIT 8
")->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id LIMIT 8")->fetchAll();
$iconosCategoria = [
  'anillos' => 'ring',
  'bolsos' => 'bag',
  'camisetas' => 'shirt',
  'cinturones' => 'belt',
  'collares' => 'necklace',
  'gafas' => 'glasses',
  'gorras' => 'cap',
  'llaveros' => 'keychain',
  'pantalones' => 'trousers',
  'pulseras' => 'bracelet',
  'shorts' => 'shorts',
  'sudaderas' => 'hoodie',
  'tops' => 'top',
];

$nuevos = $pdo->query("SELECT COUNT(*) FROM productos WHERE condicion='nueva' AND stock>0")->fetchColumn();
$usados = $pdo->query("SELECT COUNT(*) FROM productos WHERE condicion='usada' AND stock>0")->fetchColumn();

// Wishlist del usuario para marcar corazones
$misWish = [];
if (esta_logueado()) {
    $stmt = $pdo->prepare("SELECT producto_id FROM wishlist WHERE usuario_id = :uid");
    $stmt->execute([':uid' => (int)$_SESSION['usuario_id']]);
    $misWish = array_column($stmt->fetchAll(), 'producto_id');
}

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="hero-eyebrow"><svg class="inline-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkle"></use></svg> NUEVA COLECCIÓN Y2K 2025</span>
      <h1 class="hero-brand">
        <svg class="brand-doodle brand-doodle-left" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkles"></use></svg>
        KAIA<span class="outline">_SV</span>
        <svg class="brand-doodle brand-doodle-right" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkle"></use></svg>
      </h1>
      <p class="lead">Moda Y2K, urbana y casual. <strong>Tu estilo, tu esencia.</strong></p>

      <div class="hero-actions">
        <a href="/kaia_sv/catalogo.php" class="btn btn-primary">
          Ver catálogo <i class="fa-solid fa-arrow-right"></i>
        </a>
        <?php if (!esta_logueado()): ?>
          <a href="/kaia_sv/registro.php" class="btn btn-outline">
            <i class="fa-solid fa-user-plus"></i> Registrarse
          </a>
          <a href="/kaia_sv/login.php" class="btn btn-outline">
            <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
          </a>
        <?php else: ?>
          <a href="/kaia_sv/mis_pedidos.php" class="btn btn-outline">
            <i class="fa-solid fa-box"></i> Mis pedidos
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-blob"></div>
      <div class="hero-card">
        <img src="/kaia_sv/assets/img/logo.png" alt="KAIA_SV">
      </div>
      <span class="floating-sticker s1"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkles"></use></svg></span>
      <span class="floating-sticker s2"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#trousers"></use></svg></span>
      <span class="floating-sticker s3"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#headphones"></use></svg></span>
      <span class="floating-sticker s4"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#hanger"></use></svg></span>
      <span class="floating-sticker s5"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkle"></use></svg></span>
      <span class="floating-sticker s6"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#bag"></use></svg></span>
      <span class="floating-sticker s7"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#glasses"></use></svg></span>
      <span class="floating-sticker s8"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#necklace"></use></svg></span>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <h2 class="section-title">Elige tu vibe</h2>
    <p class="section-sub">Ropa nueva recién llegada o piezas vintage únicas.</p>
    <div class="condicion-grid">
      <a href="/kaia_sv/catalogo.php?condicion=nueva" class="condicion-card nueva">
        <div class="condicion-icon"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#hanger"></use></svg></div>
        <h3>Ropa Nueva</h3>
        <p><?= (int)$nuevos ?> productos disponibles</p>
        <span class="condicion-cta">Ver colección <i class="fa-solid fa-arrow-right"></i></span>
      </a>
      <a href="/kaia_sv/catalogo.php?condicion=usada" class="condicion-card usada">
        <div class="condicion-icon"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#recycle"></use></svg></div>
        <h3>Ropa Usada / Vintage</h3>
        <p><?= (int)$usados ?> piezas únicas</p>
        <span class="condicion-cta">Ver colección <i class="fa-solid fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <h2 class="section-title">Explora por categoría</h2>
    <p class="section-sub">Encuentra tu vibe entre nuestras colecciones.</p>
    <div class="cat-grid">
      <?php foreach ($categorias as $cat): ?>
        <?php $iconoCategoria = $iconosCategoria[strtolower($cat['nombre'])] ?? 'hanger'; ?>
        <a href="/kaia_sv/catalogo.php?categoria=<?= $cat['id'] ?>" class="cat-card">
          <div class="icon"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#<?= e($iconoCategoria) ?>"></use></svg></div>
          <h3><?= e($cat['nombre']) ?></h3>
          <span class="cat-arrow"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-right"></use></svg></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <h2 class="section-title">Productos destacados</h2>
    <p class="section-sub">Los favoritos de la comunidad KAIA.</p>
    <div class="prod-grid">
      <?php foreach ($destacados as $p): ?>
        <div class="prod-card">
          <div class="prod-img">
            <?php if ($p['imagen'] && file_exists(__DIR__ . '/assets/img/productos/' . $p['imagen'])): ?>
              <img src="/kaia_sv/assets/img/productos/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>">
            <?php else: ?>
              <i class="fa-solid fa-shirt"></i>
            <?php endif; ?>
            <span class="prod-badge">HOT</span>
            <?php if ($p['condicion'] === 'usada'): ?>
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
            <div class="prod-actions">
              <a href="/kaia_sv/producto.php?id=<?= $p['id'] ?>" class="btn-sm btn-ghost-sm">Ver</a>
              <button class="btn-sm btn-primary-sm"
        data-add-cart="<?= $p['id'] ?>"
        data-nombre="<?= e($p['nombre']) ?>">
  <i class="fa-solid fa-cart-plus"></i>
</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="promo-section">
      <h2>Y2K WEEK <svg class="inline-icon promo-icon" aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#sparkle"></use></svg></h2>
      <p>15% de descuento en toda la colección Y2K. Válido por tiempo limitado.</p>
      <a href="/kaia_sv/catalogo.php" class="btn">Aprovechar ahora</a>
    </div>
  </div>
</section>

<section>
  <div class="container" style="text-align:center;">
    <h2 class="section-title">Síguenos</h2>
    <p class="section-sub" style="margin: 0 auto 30px;">Únete a la comunidad KAIA_SV en redes.</p>
    <div class="social-links" style="justify-content:center;">
      <a href="https://instagram.com/kaia_sv" target="_blank"><i class="fa-brands fa-instagram"></i></a>
      <a href="https://tiktok.com/@kaia_sv" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
      <a href="https://facebook.com/kaia_sv" target="_blank"><i class="fa-brands fa-facebook"></i></a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>