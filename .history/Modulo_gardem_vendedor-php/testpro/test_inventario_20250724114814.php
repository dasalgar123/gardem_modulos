<?php
// Archivo de prueba para verificar datos del inventario
session_start();

// Incluir configuración de base de datos
require_once '../config/database.php';

// Incluir el controlador
require_once '../controlador/ControladorInventario.php';

// Crear instancia del controlador
$controladorInventario = new ControladorInventario($pdo);

echo "<h1>Prueba de Inventario - Datos Reales</h1>";
echo "<hr>";

// Probar obtención de inventario
echo "<h2>1. Datos del Inventario:</h2>";
$inventario = $controladorInventario->obtenerInventario();

if (empty($inventario)) {
    echo "<p style='color: red;'>❌ No se encontraron datos en el inventario</p>";
} else {
    echo "<p style='color: green;'>✅ Se encontraron " . count($inventario) . " productos en el inventario</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Producto</th><th>Talla</th><th>Color</th><th>Stock</th><th>Precio</th><th>Referencia</th>";
    echo "</tr>";
    
    foreach ($inventario as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['producto_id']) . "</td>";
        echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($item['talla']) . "</td>";
        echo "<td>" . htmlspecialchars($item['color']) . "</td>";
        echo "<td>" . htmlspecialchars($item['stock_total']) . "</td>";
        echo "<td>" . htmlspecialchars($item['precio']) . "</td>";
        echo "<td>" . htmlspecialchars($item['referencia']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Probar estadísticas
echo "<h2>2. Estadísticas del Inventario:</h2>";
$stats = $controladorInventario->obtenerEstadisticasInventario();

echo "<table border='1' style='border-collapse: collapse; width: 50%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Métrica</th><th>Valor</th>";
echo "</tr>";
echo "<tr><td>Total Productos</td><td>" . $stats['total_productos'] . "</td></tr>";
echo "<tr><td>Disponibles</td><td>" . $stats['productos_disponibles'] . "</td></tr>";
echo "<tr><td>Stock Bajo</td><td>" . $stats['productos_stock_bajo'] . "</td></tr>";
echo "<tr><td>Agotados</td><td>" . $stats['productos_agotados'] . "</td></tr>";
echo "<tr><td>Valor Total</td><td>$" . number_format($stats['valor_total'], 2) . "</td></tr>";
echo "</table>";

echo "<hr>";

// Verificar estructura de la base de datos
echo "<h2>3. Verificación de Tablas:</h2>";

try {
    // Verificar tabla productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productos_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📦 Productos en tabla 'productos': <strong>" . $productos_count . "</strong></p>";
    
    // Verificar tabla inventario_bodega
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
    $inventario_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📊 Registros en tabla 'inventario_bodega': <strong>" . $inventario_count . "</strong></p>";
    
    // Mostrar algunos productos de ejemplo
    if ($productos_count > 0) {
        echo "<h3>Productos disponibles:</h3>";
        $stmt = $pdo->query("SELECT id, nombre, precio, tipo_producto FROM productos LIMIT 5");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>";
        foreach ($productos as $producto) {
            echo "<li><strong>" . htmlspecialchars($producto['nombre']) . "</strong> - $" . $producto['precio'] . " (" . $producto['tipo_producto'] . ")</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al verificar tablas: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='../vista/inventario.php'>← Volver al Inventario</a></p>";
?> 