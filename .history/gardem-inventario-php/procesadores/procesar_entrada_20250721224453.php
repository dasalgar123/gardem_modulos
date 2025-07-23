<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php?page=login');
    exit();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Verificar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=entradas&error=Metodo no permitido');
    exit();
}

try {
    // Obtener datos del formulario
    $producto_id = $_POST['producto_id'] ?? null;
    $bodega_id = $_POST['bodega_id'] ?? null;
    $cantidad = $_POST['cantidad'] ?? 0;
    $motivo = $_POST['motivo'] ?? 'compra';
    $beneficiario_tipo = $_POST['beneficiario_tipo'] ?? 'proveedor';
    $beneficiario_id = $_POST['beneficiario_id'] ?? 1;
    $factura_remision = $_POST['factura_remision'] ?? '';
    $beneficiario = $_POST['beneficiario'] ?? 'Proveedor';

    // Validar datos obligatorios
    if (!$producto_id || $cantidad <= 0) {
        header('Location: ../index.php?page=entradas&error=Datos incompletos');
        exit();
    }

    // Insertar en productos_entradas
    $stmt = $pdo->prepare("
        INSERT INTO productos_entradas (
            producto_id, 
            bodega_id, 
            cantidad, 
            fecha, 
            motivo, 
            beneficiario_tipo, 
            beneficiario_id, 
            factura_remision,
            beneficiario
        ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)
    ");

    $resultado = $stmt->execute([
        $producto_id,
        $bodega_id,
        $cantidad,
        $motivo,
        $beneficiario_tipo,
        $beneficiario_id,
        $factura_remision,
        $beneficiario
    ]);

    if ($resultado) {
        $entrada_id = $pdo->lastInsertId();
        header('Location: ../index.php?page=entradas&success=1&entrada_id=' . $entrada_id);
    } else {
        header('Location: ../index.php?page=entradas&error=Error al guardar entrada');
    }

} catch (Exception $e) {
    header('Location: ../index.php?page=entradas&error=Error: ' . $e->getMessage());
}
?> 