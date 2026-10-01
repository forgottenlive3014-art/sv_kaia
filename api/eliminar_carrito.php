<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');

$clave = $_POST['clave'] ?? '';
unset($_SESSION['carrito'][$clave]);
echo json_encode(['ok' => true]);