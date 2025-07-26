<?php
// Script para insertar datos de prueba en la base de datos
session_start();

// Incluir configuración de base de datos
require_once '../config/database.php';

echo "<h1>Insertar Datos de Prueba - Inventario</h1>";
echo "<hr>";

try {
    // Verificar si ya existen productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productos_existentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($productos_existentes == 0) {
        echo "<h2>1. Insertando productos de prueba...</h2>";
        
        // Insertar productos de prueba
        $productos_prueba = [
            ['Boxer Clásico', 'Boxer de algodón 100% cómodo y transpirable', 15.99, 'ropa_interior'],
            ['Boxer Deportivo', 'Boxer deportivo con tecnología de secado rápido', 22.50, 'ropa_interior'],
            ['Boxer Premium', 'Boxer premium con acabados de alta calidad', 29.99, 'ropa_interior'],
            ['Boxer Básico', 'Boxer básico para uso diario', 12.99, 'ropa_interior'],
            ['Boxer Compresión', 'Boxer con tecnología de compresión', 35.00, 'ropa_interior']
        ];
        
        $sql_producto = "INSERT INTO productos (nombre, descripcion, precio, tipo_producto) VALUES (?, ?, ?, ?)";
        $stmt_producto = $pdo->prepare($sql_producto);
        
        foreach ($productos_prueba as $producto) {
            $stmt_producto->execute($producto);
            echo "<p>✅ Producto insertado: <strong>" . $producto[0] . "</strong></p>";
        }
        
        echo "<p style='color: green;'>✅ Se insertaron " . count($productos_prueba) . " productos</p>";
    } else {
        echo "<p>ℹ️ Ya existen " . $productos_existentes . " productos en la base de datos</p>";
    }
    
    echo "<hr>";
    
    // Verificar si ya existe inventario
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
    $inventario_existente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($inventario_existente == 0) {
        echo "<h2>2. Insertando inventario de prueba...</h2>";
        
        // Obtener productos para crear inventario
        $stmt = $pdo->query("SELECT id, nombre FROM productos");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $tallas = ['S', 'M', 'L', 'XL'];
        $colores = ['Negro', 'Blanco', 'Azul', 'Gris'];
        
        $sql_inventario = "INSERT INTO inventario_bodega (producto_id, talla, color, stock_actual, stock_minimo, referencia) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_inventario = $pdo->prepare($sql_inventario);
        
        $contador = 0;
        foreach ($productos as $producto) {
            foreach ($tallas as $talla) {
                foreach ($colores as $color) {
                    $stock = rand(5, 50); // Stock aleatorio entre 5 y 50
                    $stock_minimo = 10;
                    $referencia = 'REF-' . $producto['id'] . '-' . $talla . '-' . $color;
                    
                    $stmt_inventario->execute([
                        $producto['id'],
                        $talla,
                        $color,
                        $stock,
                        $stock_minimo,
                        $referencia
                    ]);
                    
                    $contador++;
                }
            }
        }
        
        echo "<p style='color: green;'>✅ Se insertaron " . $contador . " registros de inventario</p>";
    } else {
        echo "<p>ℹ️ Ya existen " . $inventario_existente . " registros en el inventario</p>";
    }
    
    echo "<hr>";
    
    // Mostrar resumen final
    echo "<h2>3. Resumen de la base de datos:</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
    $total_inventario = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT SUM(stock_actual) as stock_total FROM inventario_bodega");
    $stock_total = $stmt->fetch(PDO::FETCH_ASSOC)['stock_total'];
    
    echo "<table border='1' style='border-collapse: collapse; width: 50%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Métrica</th><th>Valor</th>";
    echo "</tr>";
    echo "<tr><td>Total Productos</td><td>" . $total_productos . "</td></tr>";
    echo "<tr><td>Registros de Inventario</td><td>" . $total_inventario . "</td></tr>";
    echo "<tr><td>Stock Total</td><td>" . $stock_total . "</td></tr>";
    echo "</table>";
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✅ Datos de prueba insertados correctamente</p>";
    echo "<p><a href='test_inventario.php'>🧪 Probar Inventario</a> | <a href='../vista/inventario.php'>📊 Ver Inventario</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que las tablas 'productos' e 'inventario_bodega' existan en la base de datos.</p>";
}
?> 