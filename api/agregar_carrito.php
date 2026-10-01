<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');

$id = (int)($_POST['producto_id'] ?? 0);
$cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
$talla = trim($_POST['talla'] ?? '');

$stmt = $pdo->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = :id");
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p) {
    echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado']);
    exit;
}

// Verificar tallas disponibles
$stmtT = $pdo->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = :id");
$stmtT->execute([':id' => $id]);
$tallas = $stmtT->fetchAll();

if (!empty($tallas)) {
    if ($talla === '') {
        echo json_encode(['ok' => false, 'msg' => 'Selecciona una talla', 'requiere_talla' => true]);
        exit;
    }

    $tallaValida = null;
    foreach ($tallas as $t) {
        if ($t['talla'] === $talla) { $tallaValida = $t; break; }
    }
    if (!$tallaValida) {
        echo json_encode(['ok' => false, 'msg' => 'Talla no válida']);
        exit;
    }

    $stockDisponible = (int)$tallaValida['stock'];
} else {
    $talla = 'Única';
    $stockDisponible = (int)$p['stock'];
}

$clave = $id . '::' . $talla;
$actual = $_SESSION['carrito'][$clave]['cantidad'] ?? 0;
$nueva = $actual + $cantidad;

if ($nueva > $stockDisponible) {
    echo json_encode([
        'ok' => false,
        'msg' => "Solo hay $stockDisponible unidades en talla $talla"
    ]);
    exit;
}

$_SESSION['carrito'][$clave] = [
    'id' => (int)$p['id'],
    'nombre' => $p['nombre'],
    'precio' => (float)$p['precio'],
    'talla' => $talla,
    'cantidad' => $nueva
];

$total = 0;
foreach ($_SESSION['carrito'] as $i) $total += $i['cantidad'];

echo json_encode(['ok' => true, 'total_items' => $total]);