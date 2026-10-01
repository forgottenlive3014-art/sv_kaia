<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

$cats = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $cuidados = trim($_POST['cuidados'] ?? '');
    $medidas = trim($_POST['medidas'] ?? '');
    $marca = trim($_POST['marca'] ?? 'KAIA_SV');
    $origen = trim($_POST['origen'] ?? 'El Salvador');
    $cat = (int)($_POST['categoria_id'] ?? 0);
    $precio = (float)($_POST['precio'] ?? 0);
    $talla = trim($_POST['talla'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $condicion = $_POST['condicion'] ?? 'nueva';
    $imagen = null;

    $tallasInput = $_POST['tallas'] ?? []; // ['S'=>3, 'M'=>5]

    if (!$nombre || !$precio) {
        $error = 'Nombre y precio son obligatorios.';
    } else {
        if (!empty($_FILES['imagen']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $imagen = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../assets/img/productos/' . $imagen);
            }
        }

        $pdo->prepare("INSERT INTO productos 
            (nombre, descripcion, material, cuidados, medidas, marca, origen,
             categoria_id, precio, talla, stock, imagen, destacado, condicion)
            VALUES (:n,:d,:mat,:cu,:me,:ma,:or,:c,:p,:t,:s,:i,:de,:co)")
            ->execute([
                ':n'=>$nombre, ':d'=>$desc, ':mat'=>$material, ':cu'=>$cuidados,
                ':me'=>$medidas, ':ma'=>$marca, ':or'=>$origen,
                ':c'=>$cat, ':p'=>$precio, ':t'=>$talla, ':s'=>$stock,
                ':i'=>$imagen, ':de'=>$destacado, ':co'=>$condicion
            ]);
        $pid = $pdo->lastInsertId();

        // Insertar tallas
        foreach ($tallasInput as $t => $s) {
            $t = trim($t);
            $s = (int)$s;
            if ($t !== '' && $s >= 0) {
                $pdo->prepare("INSERT INTO producto_tallas (producto_id, talla, stock) VALUES (:p, :t, :s)")
                    ->execute([':p'=>$pid, ':t'=>$t, ':s'=>$s]);
            }
        }

        redirect('/kaia_sv/admin/productos.php');
    }
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
    <h1>Agregar producto</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <div class="form-card" style="margin:0;max-width:720px;">
      <form method="POST" enctype="multipart/form-data" class="form-grid">
        <div class="field"><label>Nombre *</label><input name="nombre" required></div>
        <div class="field"><label>Descripción</label><textarea name="descripcion" rows="3"></textarea></div>
        <div class="field"><label>Material</label><input name="material" placeholder="Ej: Algodón 100%"></div>
        <div class="field"><label>Medidas</label><input name="medidas" placeholder="Ej: Tiro alto, corte holgado"></div>
        <div class="field"><label>Cuidados</label><input name="cuidados" placeholder="Ej: Lavar a mano, no secadora"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Marca</label><input name="marca" value="KAIA_SV"></div>
          <div class="field"><label>Origen</label><input name="origen" value="El Salvador"></div>
        </div>
        <div class="field"><label>Categoría</label>
          <select name="categoria_id">
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Precio *</label><input type="number" step="0.01" name="precio" required></div>
          <div class="field"><label>Stock total</label><input type="number" name="stock" value="0"></div>
        </div>
        <div class="field"><label>Tallas (texto descriptivo)</label><input name="talla" placeholder="Ej: S,M,L,XL"></div>

        <div class="field" style="background:rgba(255,255,255,.03);padding:16px;border-radius:12px;">
          <label style="margin-bottom:10px;">Stock por talla (opcional)</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
            <?php foreach (['XS','S','M','L','XL','XXL','Única'] as $t): ?>
              <div style="display:flex;flex-direction:column;gap:4px;">
                <small style="color:var(--texto-suave);"><?= $t ?></small>
                <input type="number" name="tallas[<?= $t ?>]" placeholder="0" min="0"
                       style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:8px;border-radius:8px;">
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field"><label>Imagen</label><input type="file" name="imagen" accept="image/*"></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Condición</label>
            <select name="condicion">
              <option value="nueva">Nueva</option>
              <option value="usada">Usada / Vintage</option>
            </select>
          </div>
          <div class="field"><label><input type="checkbox" name="destacado"> Destacado</label></div>
        </div>

        <button class="btn btn-primary" type="submit">Guardar producto</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>