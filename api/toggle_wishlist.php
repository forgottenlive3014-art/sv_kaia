<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!esta_logueado()) {
    echo json_encode(['ok' => false, 'msg' => 'Debes iniciar sesión']);
    exit;
}

$uid = (int)$_SESSION['usuario_id'];
$pid = (int)($_POST['producto_id'] ?? 0);

if ($pid <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

// Verificar que el producto exista
$check = $pdo->prepare("SELECT id FROM productos WHERE id = :id");
$check->execute([':id' => $pid]);
if (!$check->fetch()) {
    echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado']);
    exit;
}

// ¿Ya está en wishlist?
$stmt = $pdo->prepare("SELECT id FROM wishlist WHERE usuario_id = :u AND producto_id = :p");
$stmt->execute([':u' => $uid, ':p' => $pid]);
$existe = $stmt->fetch();

if ($existe) {
    $pdo->prepare("DELETE FROM wishlist WHERE id = :id")->execute([':id' => $existe['id']]);
    $en = false;
} else {
    $pdo->prepare("INSERT INTO wishlist (usuario_id, producto_id) VALUES (:u, :p)")
        ->execute([':u' => $uid, ':p' => $pid]);
    $en = true;
}

// Contar total
$total = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE usuario_id = :u");
$total->execute([':u' => $uid]);
$total = (int)$total->fetchColumn();

echo json_encode(['ok' => true, 'en_wishlist' => $en, 'total' => $total]);