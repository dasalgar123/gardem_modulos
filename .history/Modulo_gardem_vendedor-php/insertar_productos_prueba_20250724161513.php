<?php
// Script para insertar productos de prueba en el inventario del vendedor

// Incluir configuración de base de datos
require_once 'config/database.php';

// Incluir el controlador
require_once 'controlador/ControladorInventario.php';

try {
    // Crear instancia del controlador
    $controladorInventario = new ControladorInventario($pdo);
    
    // Insertar productos de prueba
    $resultado = $controladorInventario->insertarProductosPrueba();
    
    echo "<h2>Resultado de la inserción:</h2>";
    echo "<p><strong>$resultado</strong></p>";
    
    // Mostrar productos insertados
    $inventario = $controladorInventario->obtenerInventario();
    
    if (!empty($inventario)) {
        echo "<h3>Productos en el inventario:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Producto</th>";
        echo "<th>Tipo</th>";
        echo "<th>Precio</th>";
        echo "<th>Color</th>";
        echo "<th>Talla</th>";
        echo "<th>Stock</th>";
        echo "</tr>";
        
        foreach ($inventario as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
            echo "<td>" . ucfirst($item['tipo_producto']) . "</td>";
            echo "<td>$" . number_format($item['precio'], 0, ',', '.') . "</td>";
            echo "<td>" . ($item['color'] ?? 'N/A') . "</td>";
            echo "<td>" . ($item['talla'] ?? 'N/A') . "</td>";
            echo "<td>" . $item['stock_total'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><strong>Total de productos: " . count($inventario) . "</strong></p>";
    } else {
        echo "<p>No se encontraron productos en el inventario.</p>";
    }
    
    echo "<br><a href='vista/index.php?page=inventario' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Inventario</a>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?> 