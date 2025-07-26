<?php
header('Content-Type: application/json');
require_once 'config/database.php';
require_once 'controlador/ControladorInventario.php';

try {
    $controlador = new ControladorInventario($pdo);
    $stats = $controlador->obtenerEstadisticasInventario();
    
    // Obtener información adicional
    $stmt = $pdo->query("SELECT COUNT(*) as total_productos FROM productos");
    $total_productos = $stmt->fetch()['total_productos'];
    
    $stmt = $pdo->query("SELECT SUM(stock_actual) as stock_total FROM inventario_bodega");
    $stock_total = $stmt->fetch()['stock_total'] ?? 0;
    
    $stats['total_productos_db'] = $total_productos;
    $stats['stock_total_db'] = $stock_total;
    $stats['fecha_actual'] = date('Y-m-d H:i:s');
    
    echo json_encode($stats);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
}
?> 