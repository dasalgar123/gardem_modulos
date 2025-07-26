<?php
require_once 'config/database.php';

echo "<h2>Agregando datos de inventario...</h2>";

// Obtener productos existentes
$stmt = $pdo->query("SELECT id, nombre FROM productos LIMIT 5");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Productos encontrados: " . count($productos) . "</p>";

// Crear inventario para cada producto
$tallas = ['S', 'M', 'L', 'XL'];
$colores = ['Negro', 'Blanco', 'Azul', 'Gris'];

$stmt_inv = $pdo->prepare("INSERT INTO inventario_bodega (producto_id, talla, color, stock_actual) VALUES (?, ?, ?, ?)");

foreach ($productos as $producto) {
    echo "<p>Agregando inventario para: <strong>{$producto['nombre']}</strong></p>";
    
    foreach ($tallas as $talla) {
        foreach ($colores as $color) {
            // Verificar si ya existe este registro
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM inventario_bodega WHERE producto_id = ? AND talla = ? AND color = ?");
            $stmt_check->execute([$producto['id'], $talla, $color]);
            $existe = $stmt_check->fetchColumn();
            
            if (!$existe) {
                $stock = rand(5, 50); // Stock aleatorio entre 5 y 50
                $stmt_inv->execute([$producto['id'], $talla, $color, $stock]);
                echo "<p>✓ Inventario creado: {$producto['nombre']} - Talla $talla, Color $color, Stock $stock</p>";
            } else {
                echo "<p>⚠ Ya existe: {$producto['nombre']} - Talla $talla, Color $color</p>";
            }
        }
    }
}

// Mostrar resumen final
$stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
$total_inventario_final = $stmt->fetch()['total'];

echo "<h3>Resumen final:</h3>";
echo "<p>Total registros de inventario: <strong>$total_inventario_final</strong></p>";

echo "<p><a href='vista/index.php?page=inventario' class='btn btn-primary'>Ver Inventario</a></p>";
?> 