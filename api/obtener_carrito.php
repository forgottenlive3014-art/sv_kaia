<?php
// ============================================
// KAIA_SV - Obtener estado del carrito
// Devuelve JSON con items, subtotales y total
// ============================================
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$items = $_SESSION['carrito'] ?? [];

$detalle = [];
$total = 0.0;
$total_items = 0;

if (!empty($items)) {
    // Validar stock real contra la BD
    $ids = array_keys($items);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, nombre, precio, stock, imagen, talla
        FROM productos
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $productos_db = [];
    foreach ($stmt->fetchAll() as $p) {
        $productos_db[$p['id']] = $p;
    }

    foreach ($items as $id => $it) {
        // Si el producto fue eliminado, lo quitamos del carrito
        if (!isset($productos_db[$id])) {
            unset($_SESSION['carrito'][$id]);
            continue;
        }

        $prod = $productos_db[$id];

        // Ajustar cantidad si excede el stock actual
        $cantidad = min((int)$it['cantidad'], (int)$prod['stock']);
        if ($cantidad < 1) {
            unset($_SESSION['carrito'][$id]);
            continue;
        }
        $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
        $_SESSION['carrito'][$id]['precio']   = (float)$prod['precio'];

        $subtotal = $cantidad * (float)$prod['precio'];
        $total += $subtotal;
        $total_items += $cantidad;

        $detalle[] = [
            'id'        => (int)$id,
            'nombre'    => $prod['nombre'],
            'precio'    => (float)$prod['precio'],
            'cantidad'  => $cantidad,
            'stock'     => (int)$prod['stock'],
            'talla'     => $prod['talla'],
            'imagen'    => $prod['imagen'],
            'subtotal'  => round($subtotal, 2),
            'url'       => '/kaia_sv/producto.php?id=' . (int)$id,
            'img_url'   => $prod['imagen']
                            ? '/kaia_sv/assets/img/productos/' . $prod['imagen']
                            : null,
        ];
    }
}

// Ordenar por id para que sea consistente
usort($detalle, fn($a, $b) => $a['id'] <=> $b['id']);

echo json_encode([
    'ok'          => true,
    'items'       => $detalle,
    'total_items' => $total_items,
    'total'       => round($total, 2),
    'total_fmt'   => '$' . number_format($total, 2),
    'vacio'       => empty($detalle),
], JSON_UNESCAPED_UNICODE);