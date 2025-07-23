<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Incluir el controlador
require_once 'controlador/ControladorProveedores.php';

// Crear instancia del controlador
$controladorProveedores = new ControladorProveedores($pdo);

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de proveedor requerido']);
    exit();
}

$id = $_GET['id'];

// Obtener el proveedor
$proveedor = $controladorProveedores->obtenerProveedor($id);

if ($proveedor) {
    echo json_encode([
        'success' => true,
        'proveedor' => $proveedor
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Proveedor no encontrado'
    ]);
}
?> 