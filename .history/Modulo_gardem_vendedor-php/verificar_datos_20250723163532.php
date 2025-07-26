<?php
require_once 'config/database.php';

echo "<h2>Verificando datos del sistema...</h2>";

// Verificar tabla productos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
$total_productos = $stmt->fetch()['total'];
echo "<p>Productos en la base de datos: <strong>$total_productos</strong></p>";

// Verificar tabla inventario_bodega
$stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
$total_inventario = $stmt->fetch()['total'];
echo "<p>Registros en inventario: <strong>$total_inventario</strong></p>";

// Si no hay productos, crear algunos de prueba
if ($total_productos == 0) {
    echo "<h3>Creando productos de prueba...</h3>";
    
    $productos = [
        ['Boxer Clásico', 'Boxer de algodón clásico', 25.00, 'Caballeros'],
        ['Boxer Deportivo', 'Boxer deportivo microfibra', 35.00, 'Caballeros'],
        ['Boxer Premium', 'Boxer premium algodón', 45.00, 'Caballeros'],
        ['Boxer Sin Costura', 'Boxer sin costura deportivo', 30.00, 'Caballeros'],
        ['Boxer Compresión', 'Boxer compresión ligera', 40.00, 'Caballeros']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, tipo_producto) VALUES (?, ?, ?, ?)");
    
    foreach ($productos as $producto) {
        $stmt->execute($producto);
        echo "<p>✓ Producto creado: {$producto[0]}</p>";
    }
    
    // Obtener IDs de productos creados
    $stmt = $pdo->query("SELECT id FROM productos ORDER BY id DESC LIMIT 5");
    $producto_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Crear inventario para cada producto
    $tallas = ['S', 'M', 'L', 'XL'];
    $colores = ['Negro', 'Blanco', 'Azul', 'Gris'];
    
    $stmt_inv = $pdo->prepare("INSERT INTO inventario_bodega (producto_id, talla, color, stock_actual) VALUES (?, ?, ?, ?)");
    
    foreach ($producto_ids as $producto_id) {
        foreach ($tallas as $talla) {
            foreach ($colores as $color) {
                $stock = rand(5, 50); // Stock aleatorio entre 5 y 50
                $stmt_inv->execute([$producto_id, $talla, $color, $stock]);
                echo "<p>✓ Inventario creado: Producto ID $producto_id, Talla $talla, Color $color, Stock $stock</p>";
            }
        }
    }
    
    echo "<h3>✓ Datos de prueba creados exitosamente!</h3>";
} else {
    echo "<h3>Ya existen productos en la base de datos.</h3>";
}

// Mostrar resumen final
$stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
$total_productos_final = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
$total_inventario_final = $stmt->fetch()['total'];

echo "<h3>Resumen final:</h3>";
echo "<p>Total productos: <strong>$total_productos_final</strong></p>";
echo "<p>Total registros de inventario: <strong>$total_inventario_final</strong></p>";

echo "<p><a href='vista/index.php?page=inventario' class='btn btn-primary'>Ver Inventario</a></p>";
?> 