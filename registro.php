<?php
require_once __DIR__ . '/config/conexion.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$nombre || !$correo || !$pass) {
        $error = 'Completa todos los campos.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo inválido.';
    } else {
        $existe = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :c");
        $existe->execute([':c' => $correo]);
        if ($existe->fetch()) {
            $error = 'Ese correo ya está registrado.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, password) VALUES (:n,:c,:t,:p)")
                ->execute([':n'=>$nombre, ':c'=>$correo, ':t'=>$telefono, ':p'=>$hash]);
            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['nombre'] = $nombre;
            $_SESSION['rol'] = 'cliente';
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            if ($redirect) redirect($redirect);
            redirect('/kaia_sv/index.php');
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="form-card">
      <h2>Crear cuenta</h2>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="POST" class="form-grid">
        <div class="field"><label>Nombre completo</label><input type="text" name="nombre" required></div>
        <div class="field"><label>Correo</label><input type="email" name="correo" required></div>
        <div class="field"><label>Teléfono</label><input type="text" name="telefono"></div>
        <div class="field"><label>Contraseña</label><input type="password" name="password" required minlength="6"></div>
        <button type="submit" class="btn btn-primary">Registrarme</button>
      </form>
      <p class="form-link">¿Ya tienes cuenta? <a href="/kaia_sv/login.php">Inicia sesión</a></p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>