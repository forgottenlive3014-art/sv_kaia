<?php
// ============================================
// KAIA_SV - Eliminar producto (solo admin)
// ============================================
require_once __DIR__ . '/../includes/auth.php';
requerir_admin();

// Aceptar GET (link directo) o POST (formulario)
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['admin_msg'] = ['tipo' => 'error', 'texto' => 'ID de producto inválido.'];
    redirect('/kaia_sv/admin/productos.php');
}

try {
    // 1) Verificar que exista
    $stmt = $pdo->prepare("SELECT id, nombre, imagen FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        $_SESSION['admin_msg'] = ['tipo' => 'error', 'texto' => 'El producto no existe.'];
        redirect('/kaia_sv/admin/productos.php');
    }

    // 2) Verificar que no tenga pedidos activos (no entregados ni cancelados)
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM detalle_pedido d
        INNER JOIN pedidos p ON d.pedido_id = p.id
        WHERE d.producto_id = :id
          AND p.estado NOT IN ('Entregado', 'Cancelado')
    ");
    $check->execute([':id' => $id]);
    $pedidos_activos = (int)$check->fetchColumn();

    if ($pedidos_activos > 0) {
        $_SESSION['admin_msg'] = [
            'tipo'  => 'error',
            'texto' => "No se puede eliminar '{$producto['nombre']}': tiene {$pedidos_activos} pedido(s) activo(s). Cambia su stock a 0 en su lugar."
        ];
        redirect('/kaia_sv/admin/productos.php');
    }

    // 3) Eliminar imagen física si existe
    if (!empty($producto['imagen'])) {
        $ruta = __DIR__ . '/../assets/img/productos/' . $producto['imagen'];
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    // 4) Eliminar registro (los detalles históricos se eliminan por CASCADE
    //    pero solo si no hay pedidos activos, por eso la validación previa)
    $pdo->prepare("DELETE FROM productos WHERE id = :id")->execute([':id' => $id]);

    $_SESSION['admin_msg'] = [
        'tipo'  => 'success',
        'texto' => "Producto '{$producto['nombre']}' eliminado correctamente."
    ];
} catch (PDOException $ex) {
    $_SESSION['admin_msg'] = [
        'tipo'  => 'error',
        'texto' => 'Error al eliminar el producto. Verifica que no esté referenciado en pedidos.'
    ];
}

redirect('/kaia_sv/admin/productos.php');