<?php
header('Content-Type: application/json');
require_once 'config/database.php';

try {
    // Obtener productos
    $sql = "SELECT id, nombre, descripcion, precio, tipo_producto FROM productos ORDER BY nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($productos);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener productos: ' . $e->getMessage()]);
}
?> 