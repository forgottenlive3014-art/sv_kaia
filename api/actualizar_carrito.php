<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');

$clave = (string)($_POST['clave'] ?? '');
$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
$item = $_SESSION['carrito'][$clave] ?? null;

if (!$item || $cantidad === false || $cantidad < 1) {
    echo json_encode(['ok' => false, 'msg' => 'Cantidad o producto inválido']);
    exit;
}

$id = (int)$item['id'];
$talla = (string)$item['talla'];
if ($talla === 'Única') {
    $stock = $pdo->prepare("SELECT stock FROM productos WHERE id = :id");
    $stock->execute([':id' => $id]);
} else {
    $stock = $pdo->prepare("SELECT stock FROM producto_tallas WHERE producto_id = :id AND talla = :talla");
    $stock->execute([':id' => $id, ':talla' => $talla]);
}
$disponible = $stock->fetchColumn();

if ($disponible === false || $cantidad > (int)$disponible) {
    echo json_encode(['ok' => false, 'msg' => 'La cantidad supera el stock disponible']);
    exit;
}

$_SESSION['carrito'][$clave]['cantidad'] = $cantidad;
$total = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

echo json_encode(['ok' => true, 'total_items' => $total]);