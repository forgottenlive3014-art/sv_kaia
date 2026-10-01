<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute([':id'=>$id]);
$p = $stmt->fetch();
if (!$p) redirect('/kaia_sv/admin/productos.php');

$cats = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

// Tallas actuales
$stmtT = $pdo->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = :id");
$stmtT->execute([':id' => $id]);
$tallasActuales = [];
foreach ($stmtT->fetchAll() as $t) $tallasActuales[$t['talla']] = $t['stock'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion']);
    $material = trim($_POST['material'] ?? '');
    $cuidados = trim($_POST['cuidados'] ?? '');
    $medidas = trim($_POST['medidas'] ?? '');
    $marca = trim($_POST['marca'] ?? 'KAIA_SV');
    $origen = trim($_POST['origen'] ?? 'El Salvador');
    $cat = (int)$_POST['categoria_id'];
    $precio = (float)$_POST['precio'];
    $talla = trim($_POST['talla']);
    $stock = (int)$_POST['stock'];
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $condicion = $_POST['condicion'] ?? 'nueva';
    $imagen = $p['imagen'];

    if (!empty($_FILES['imagen']['name'])) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $imagen = uniqid('prod_') . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../assets/img/productos/' . $imagen);
        }
    }

    $pdo->prepare("UPDATE productos SET 
        nombre=:n, descripcion=:d, material=:mat, cuidados=:cu, medidas=:me,
        marca=:ma, origen=:or, categoria_id=:c, precio=:p, talla=:t, stock=:s,
        imagen=:i, destacado=:de, condicion=:co WHERE id=:id")
        ->execute([
            ':n'=>$nombre, ':d'=>$desc, ':mat'=>$material, ':cu'=>$cuidados,
            ':me'=>$medidas, ':ma'=>$marca, ':or'=>$origen,
            ':c'=>$cat, ':p'=>$precio, ':t'=>$talla, ':s'=>$stock,
            ':i'=>$imagen, ':de'=>$destacado, ':co'=>$condicion, ':id'=>$id
        ]);

    // Actualizar tallas
    $tallasInput = $_POST['tallas'] ?? [];
    $pdo->prepare("DELETE FROM producto_tallas WHERE producto_id = :id")->execute([':id' => $id]);
    foreach ($tallasInput as $t => $s) {
        $t = trim($t);
        $s = (int)$s;
        if ($t !== '' && $s >= 0) {
            $pdo->prepare("INSERT INTO producto_tallas (producto_id, talla, stock) VALUES (:p, :t, :s)")
                ->execute([':p'=>$id, ':t'=>$t, ':s'=>$s]);
        }
    }

    redirect('/kaia_sv/admin/productos.php');
}
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>KAIA Admin</h2>
    <a href="/kaia_sv/admin/index.php" class="back-link"><svg aria-hidden="true"><use href="/kaia_sv/assets/img/icons.svg#arrow-left"></use></svg> Volver al panel</a>
    <a href="/kaia_sv/admin/productos.php" class="active">Productos</a>
    <a href="/kaia_sv/admin/pedidos.php">Pedidos</a>
    <a href="/kaia_sv/admin/entregas.php">Entregas</a>
    <a href="/kaia_sv/admin/promociones.php">Promociones</a>
    <a href="/kaia_sv/admin/usuarios.php">Usuarios</a>
  </aside>
  <div class="admin-content">
    <h1>Editar producto #<?= $p['id'] ?></h1>
    <div class="form-card" style="margin:0;max-width:720px;">
      <form method="POST" enctype="multipart/form-data" class="form-grid">
        <div class="field"><label>Nombre</label><input name="nombre" value="<?= e($p['nombre']) ?>" required></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion" rows="3"><?= e($p['descripcion']) ?></textarea></div>
        <div class="field"><label>Material</label><input name="material" value="<?= e($p['material']) ?>"></div>
        <div class="field"><label>Medidas</label><input name="medidas" value="<?= e($p['medidas']) ?>"></div>
        <div class="field"><label>Cuidados</label><input name="cuidados" value="<?= e($p['cuidados']) ?>"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Marca</label><input name="marca" value="<?= e($p['marca']) ?>"></div>
          <div class="field"><label>Origen</label><input name="origen" value="<?= e($p['origen']) ?>"></div>
        </div>
        <div class="field"><label>Categoría</label>
          <select name="categoria_id">
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $c['id']==$p['categoria_id']?'selected':'' ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Precio</label><input type="number" step="0.01" name="precio" value="<?= $p['precio'] ?>" required></div>
          <div class="field"><label>Stock total</label><input type="number" name="stock" value="<?= $p['stock'] ?>"></div>
        </div>
        <div class="field"><label>Tallas (texto descriptivo)</label><input name="talla" value="<?= e($p['talla']) ?>"></div>

        <div class="field" style="background:rgba(255,255,255,.03);padding:16px;border-radius:12px;">
          <label style="margin-bottom:10px;">Stock por talla</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
            <?php foreach (['XS','S','M','L','XL','XXL','Única'] as $t): ?>
              <div style="display:flex;flex-direction:column;gap:4px;">
                <small style="color:var(--texto-suave);"><?= $t ?></small>
                <input type="number" name="tallas[<?= $t ?>]" min="0"
                       value="<?= $tallasActuales[$t] ?? '' ?>"
                       placeholder="0"
                       style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:8px;">
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field"><label>Imagen (dejar vacío para mantener)</label><input type="file" name="imagen" accept="image/*"></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Condición</label>
            <select name="condicion">
              <option value="nueva" <?= $p['condicion']==='nueva'?'selected':'' ?>>Nueva</option>
              <option value="usada" <?= $p['condicion']==='usada'?'selected':'' ?>>Usada / Vintage</option>
            </select>
          </div>
          <div class="field"><label><input type="checkbox" name="destacado" <?= $p['destacado']?'checked':'' ?>> Destacado</label></div>
        </div>

        <button class="btn btn-primary" type="submit">Guardar cambios</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>