<?php
// Prueba simple del controlador de inventario
session_start();

// Incluir configuración y controlador
require_once 'config/database.php';
require_once 'controlador/ControladorInventario.php';

echo "<h1>Prueba Simple - Controlador Inventario</h1>";
echo "<hr>";

try {
    // Crear instancia del controlador
    $controlador = new ControladorInventario($pdo);
    
    // Probar método obtenerInventario()
    echo "<h2>1. Método obtenerInventario():</h2>";
    $inventario = $controlador->obtenerInventario();
    echo "<p>📊 Total de productos obtenidos: <strong>" . count($inventario) . "</strong></p>";
    
    if (count($inventario) > 0) {
        echo "<h3>Primeros 5 productos:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Producto</th><th>Tipo</th><th>Precio</th><th>Stock</th><th>Color</th><th>Talla</th>";
        echo "</tr>";
        
        for ($i = 0; $i < min(5, count($inventario)); $i++) {
            $item = $inventario[$i];
            echo "<tr>";
            echo "<td>" . $item['producto_id'] . "</td>";
            echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
            echo "<td>" . $item['tipo_producto'] . "</td>";
            echo "<td>$" . $item['precio'] . "</td>";
            echo "<td>" . $item['stock_total'] . "</td>";
            echo "<td>" . $item['color'] . "</td>";
            echo "<td>" . $item['talla'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Probar método obtenerDatosInventario()
    echo "<h2>2. Método obtenerDatosInventario():</h2>";
    $datos = $controlador->obtenerDatosInventario();
    echo "<p>📋 Total de productos: <strong>" . count($datos['inventario']) . "</strong></p>";
    
    if (count($datos['inventario']) > 0) {
        echo "<h3>Primeros 3 productos:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Producto</th><th>Tipo</th><th>Precio</th><th>Entradas</th><th>Salidas</th><th>Saldo</th>";
        echo "</tr>";
        
        for ($i = 0; $i < min(3, count($datos['inventario'])); $i++) {
            $item = $datos['inventario'][$i];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
            echo "<td>" . $item['tipo_producto'] . "</td>";
            echo "<td>$" . $item['precio'] . "</td>";
            echo "<td>" . $item['total_entradas'] . "</td>";
            echo "<td>" . $item['total_salidas'] . "</td>";
            echo "<td>" . $item['saldo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Probar método obtenerEstadisticasInventario()
    echo "<h2>3. Método obtenerEstadisticasInventario():</h2>";
    $stats = $controlador->obtenerEstadisticasInventario();
    echo "<ul>";
    echo "<li>Total productos: <strong>" . $stats['total_productos'] . "</strong></li>";
    echo "<li>Disponibles: <strong>" . $stats['productos_disponibles'] . "</strong></li>";
    echo "<li>Stock bajo: <strong>" . $stats['productos_stock_bajo'] . "</strong></li>";
    echo "<li>Agotados: <strong>" . $stats['productos_agotados'] . "</strong></li>";
    echo "<li>Valor total: <strong>$" . number_format($stats['valor_total'], 2) . "</strong></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='vista/inventario.php'>← Ir al Inventario</a></p>";
echo "<p><a href='debug_inventario.php'>← Ir al Debug</a></p>";
?> 