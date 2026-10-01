<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/auth.php';

if (!esta_logueado()) {
    $_SESSION['redirect_after_login'] = '/kaia_sv/pedido.php';
    redirect('/kaia_sv/login.php?msg=login_required');
}

$items = $_SESSION['carrito'] ?? [];
if (empty($items)) redirect('/kaia_sv/carrito.php');

$total = 0;
foreach ($items as $i) $total += $i['precio'] * $i['cantidad'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $metodo = $_POST['metodo_entrega'] ?? '';
    $obs = trim($_POST['observaciones'] ?? '');

    if (!$nombre || !$telefono || !$direccion || !in_array($metodo, ['domicilio','punto_encuentro','mensajeria'])) {
        $error = 'Completa todos los campos obligatorios.';
    } else {
        try {
            $pdo->beginTransaction();

            // Verificar stock por talla
            foreach ($items as $clave => $it) {
                $id = (int)$it['id'];
                $talla = $it['talla'];

                if ($talla !== 'Única') {
                    $s = $pdo->prepare("SELECT stock FROM producto_tallas 
                                        WHERE producto_id = :id AND talla = :t FOR UPDATE");
                    $s->execute([':id' => $id, ':t' => $talla]);
                } else {
                    $s = $pdo->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
                    $s->execute([':id' => $id]);
                }
                $prod = $s->fetch();
                if (!$prod || $prod['stock'] < $it['cantidad']) {
                    throw new Exception("Stock insuficiente: " . $it['nombre'] . " (Talla " . $talla . ")");
                }
            }

            $numero = 'KAIA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $stmt = $pdo->prepare("INSERT INTO pedidos
                (numero_pedido, usuario_id, nombre_cliente, telefono, correo, direccion, metodo_entrega, observaciones, total, estado)
                VALUES (:num, :uid, :nom, :tel, :cor, :dir, :met, :obs, :tot, 'Pendiente')");
            $stmt->execute([
                ':num' => $numero,
                ':uid' => (int)$_SESSION['usuario_id'],
                ':nom' => $nombre,
                ':tel' => $telefono,
                ':cor' => $correo,
                ':dir' => $direccion,
                ':met' => $metodo,
                ':obs' => $obs,
                ':tot' => $total
            ]);
            $pedido_id = $pdo->lastInsertId();

            foreach ($items as $clave => $it) {
                $id = (int)$it['id'];
                $talla = $it['talla'];

                $pdo->prepare("INSERT INTO detalle_pedido 
                    (pedido_id, producto_id, talla, cantidad, precio_unitario, subtotal)
                    VALUES (:pid, :proid, :talla, :cant, :pre, :sub)")
                    ->execute([
                        ':pid' => $pedido_id,
                        ':proid' => $id,
                        ':talla' => $talla,
                        ':cant' => $it['cantidad'],
                        ':pre' => $it['precio'],
                        ':sub' => $it['precio'] * $it['cantidad']
                    ]);

                if ($talla !== 'Única') {
                    $pdo->prepare("UPDATE producto_tallas SET stock = stock - :c
                                   WHERE producto_id = :id AND talla = :t")
                        ->execute([':c' => $it['cantidad'], ':id' => $id, ':t' => $talla]);
                }

                $pdo->prepare("UPDATE productos SET stock = stock - :c WHERE id = :id")
                    ->execute([':c' => $it['cantidad'], ':id' => $id]);
            }

            $pdo->prepare("INSERT INTO entregas (pedido_id, servicio, estado) VALUES (:pid, :serv, 'Pendiente')")
                ->execute([':pid' => $pedido_id, ':serv' => $metodo]);

            $pdo->commit();
            unset($_SESSION['carrito']);
            $_SESSION['ultimo_pedido'] = ['numero' => $numero, 'total' => $total, 'metodo' => $metodo];
            redirect('/kaia_sv/confirmacion.php');
        } catch (Exception $ex) {
            $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section>
  <div class="container">
        <a href="/kaia_sv/carrito.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al carrito</a>
    <h1 class="section-title">Finalizar pedido</h1>
    <p class="section-sub">Completa tus datos de entrega.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:640px;">
      <form method="POST" class="form-grid">
        <div class="field"><label>Nombre completo *</label><input type="text" name="nombre" required value="<?= e($_SESSION['nombre'] ?? '') ?>"></div>
        <div class="field"><label>Teléfono *</label><input type="text" name="telefono" required></div>
        <div class="field"><label>Correo</label><input type="email" name="correo"></div>
        <div class="field"><label>Dirección o punto de encuentro *</label><input type="text" name="direccion" required></div>
        <div class="field">
          <label>Método de entrega *</label>
          <select name="metodo_entrega" required>
            <option value="">-- Selecciona --</option>
            <option value="domicilio">Servicio a domicilio</option>
            <option value="punto_encuentro">Punto de encuentro</option>
            <option value="mensajeria">Empresa de mensajería</option>
          </select>
        </div>
        <div class="field"><label>Observaciones</label><textarea name="observaciones" rows="3"></textarea></div>
        <button type="submit" class="btn btn-primary">Confirmar pedido — $<?= number_format($total, 2) ?></button>
      </form>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>