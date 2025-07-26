<?php
// Archivo de debug para verificar datos del inventario
session_start();

// Incluir configuración de base de datos
require_once 'config/database.php';

echo "<h1>Debug - Inventario Vendedor</h1>";
echo "<hr>";

try {
    // Verificar conexión
    echo "<h2>1. Conexión a Base de Datos:</h2>";
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar tablas
    echo "<h2>2. Verificación de Tablas:</h2>";
    
    // Tabla productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productos_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📦 Productos en tabla 'productos': <strong>" . $productos_count . "</strong></p>";
    
    // Tabla inventario_bodega
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
    $inventario_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📊 Registros en tabla 'inventario_bodega': <strong>" . $inventario_count . "</strong></p>";
    
    // Mostrar algunos productos
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
    
    // Probar consulta del inventario
    echo "<h2>3. Prueba de Consulta de Inventario:</h2>";
    
    $sql = "
        SELECT 
            p.id as producto_id,
            p.nombre as producto_nombre,
            p.descripcion,
            p.tipo_producto,
            p.precio,
            COALESCE(ib.color, 'N/A') as color,
            COALESCE(ib.talla, 'N/A') as talla,
            COALESCE(ib.stock_actual, 0) as total_entradas,
            0 as total_salidas,
            COALESCE(ib.stock_actual, 0) as saldo
        FROM productos p
        LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
        ORDER BY p.nombre, ib.color, ib.talla
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📋 Resultados de la consulta: <strong>" . count($inventario) . "</strong> registros</p>";
    
    if (count($inventario) > 0) {
        echo "<h3>Primeros 3 registros:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Producto</th><th>Tipo</th><th>Precio</th><th>Color</th><th>Talla</th><th>Stock</th>";
        echo "</tr>";
        
        for ($i = 0; $i < min(3, count($inventario)); $i++) {
            $item = $inventario[$i];
            echo "<tr>";
            echo "<td>" . $item['producto_id'] . "</td>";
            echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
            echo "<td>" . $item['tipo_producto'] . "</td>";
            echo "<td>$" . $item['precio'] . "</td>";
            echo "<td>" . $item['color'] . "</td>";
            echo "<td>" . $item['talla'] . "</td>";
            echo "<td>" . $item['saldo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se encontraron datos en el inventario</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='vista/inventario.php'>← Volver al Inventario</a></p>";
?> 