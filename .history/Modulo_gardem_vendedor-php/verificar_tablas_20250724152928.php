<?php
// Verificar estructura de tablas
session_start();
require_once 'config/database.php';

echo "<h1>Verificación de Estructura de Tablas</h1>";
echo "<hr>";

try {
    // Verificar estructura de tabla productos
    echo "<h2>1. Estructura de tabla 'productos':</h2>";
    $stmt = $pdo->query("DESCRIBE productos");
    $columnas_productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($columnas_productos as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar estructura de tabla inventario_bodega
    echo "<h2>2. Estructura de tabla 'inventario_bodega':</h2>";
    $stmt = $pdo->query("DESCRIBE inventario_bodega");
    $columnas_inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($columnas_inventario as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar si existen otras tablas relacionadas
    echo "<h2>3. Otras tablas relacionadas:</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE '%color%' OR SHOW TABLES LIKE '%talla%' OR SHOW TABLES LIKE '%inventario%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tablas) > 0) {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li><strong>" . $tabla . "</strong></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron tablas relacionadas con colores, tallas o inventario.</p>";
    }
    
    // Probar consulta simple sin color y talla
    echo "<h2>4. Prueba de consulta simple:</h2>";
    $sql_simple = "
        SELECT 
            p.id as producto_id,
            p.nombre as producto_nombre,
            p.descripcion,
            p.tipo_producto,
            p.precio,
            COALESCE(ib.stock_actual, 0) as stock_actual
        FROM productos p
        LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
        ORDER BY p.nombre
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($sql_simple);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📋 Resultados de consulta simple: <strong>" . count($resultados) . "</strong> registros</p>";
    
    if (count($resultados) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Producto</th><th>Tipo</th><th>Precio</th><th>Stock</th>";
        echo "</tr>";
        
        foreach ($resultados as $item) {
            echo "<tr>";
            echo "<td>" . $item['producto_id'] . "</td>";
            echo "<td>" . htmlspecialchars($item['producto_nombre']) . "</td>";
            echo "<td>" . $item['tipo_producto'] . "</td>";
            echo "<td>$" . $item['precio'] . "</td>";
            echo "<td>" . $item['stock_actual'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='debug_inventario.php'>← Volver al Debug</a></p>";
echo "<p><a href='test_simple.php'>← Ir a Test Simple</a></p>";
?> 