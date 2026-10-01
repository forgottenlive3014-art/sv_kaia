<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$aviso = ($_GET['msg'] ?? '') === 'login_required'
    ? 'Debes iniciar sesión para continuar.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :c");
    $stmt->execute([':c' => $correo]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['usuario_id'] = $u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['rol'] = $u['rol'];

        // Respetar redirect pendiente
        $redirect = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);

        if ($redirect) redirect($redirect);
        redirect($u['rol'] === 'admin' ? '/kaia_sv/admin/index.php' : '/kaia_sv/index.php');
    } else {
        $error = 'Credenciales inválidas.';
    }
}

include __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="form-card">
      <h2>Iniciar sesión</h2>
      <?php if ($aviso): ?><div class="alert alert-error"><?= e($aviso) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="POST" class="form-grid">
        <div class="field"><label>Correo</label><input type="email" name="correo" required></div>
        <div class="field"><label>Contraseña</label><input type="password" name="password" required></div>
        <button type="submit" class="btn btn-primary">Entrar</button>
      </form>
      <p class="form-link">¿No tienes cuenta? <a href="/kaia_sv/registro.php">Regístrate</a></p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>