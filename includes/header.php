<?php
require_once __DIR__ . '/auth.php';
$carrito_count = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) $carrito_count += $item['cantidad'];
}

$wish_count = 0;
if (esta_logueado()) {
    $stmtW = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE usuario_id = :uid");
    $stmtW->execute([':uid' => (int)$_SESSION['usuario_id']]);
    $wish_count = (int)$stmtW->fetchColumn();
}

$pagina = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KAIA_SV — Moda Y2K, Urbana y Casual</title>
<link rel="icon" href="/kaia_sv/assets/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Archivo+Black&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/kaia_sv/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="/kaia_sv/index.php" class="logo" id="logo">
      <img src="/kaia_sv/assets/img/logo.png" alt="KAIA_SV">
    </a>

    <nav class="main-nav" id="mainNav">
      <a href="/kaia_sv/index.php" class="<?= $pagina === 'index.php' ? 'active' : '' ?>">Inicio</a>
      <a href="/kaia_sv/catalogo.php" class="<?= $pagina === 'catalogo.php' ? 'active' : '' ?>">Catálogo</a>
      <a href="/kaia_sv/nosotros.php" class="<?= $pagina === 'nosotros.php' ? 'active' : '' ?>">Nosotros</a>
      <a href="/kaia_sv/contacto.php" class="<?= $pagina === 'contacto.php' ? 'active' : '' ?>">Contacto</a>
      <?php if (esta_logueado()): ?>
        <a href="/kaia_sv/mis_pedidos.php" class="<?= $pagina === 'mis_pedidos.php' ? 'active' : '' ?>">Mis pedidos</a>
        <a href="/kaia_sv/wishlist.php" class="<?= $pagina === 'wishlist.php' ? 'active' : '' ?>">Favoritos</a>
      <?php endif; ?>
      <?php if (es_admin()): ?>
        <a href="/kaia_sv/admin/index.php">Admin</a>
      <?php endif; ?>
    </nav>

    <div class="header-actions">
      <?php if (esta_logueado()): ?>
        <a href="/kaia_sv/wishlist.php" class="wishlist-btn" aria-label="Favoritos">
          <i class="fa-solid fa-heart"></i>
          <span class="count" id="wishCount"><?= $wish_count ?></span>
        </a>
      <?php endif; ?>

      <a href="/kaia_sv/carrito.php" class="cart-btn" aria-label="Carrito">
        <i class="fa-solid fa-bag-shopping"></i>
        <span class="cart-count" id="cartCount"><?= $carrito_count ?></span>
      </a>

      <?php if (esta_logueado()): ?>
        <a href="/kaia_sv/logout.php" class="btn-auth btn-auth-outline">
          <i class="fa-solid fa-right-from-bracket"></i>
          <?= e(explode(' ', $_SESSION['nombre'])[0]) ?>
        </a>
      <?php else: ?>
        <a href="/kaia_sv/login.php" class="btn-auth btn-auth-outline">Iniciar sesión</a>
        <a href="/kaia_sv/registro.php" class="btn-auth btn-auth-primary">Registrarse</a>
      <?php endif; ?>

      <button class="menu-toggle" id="menuToggle" aria-label="Menú">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>
</header>
<main>