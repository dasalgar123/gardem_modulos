<?php
// Verificar tallas y colores disponibles
session_start();
require_once 'config/database.php';

echo "<h1>Verificación de Variantes - Tallas y Colores</h1>";
echo "<hr>";

try {
    // Verificar si existen tablas de tallas y colores
    echo "<h2>1. Tablas disponibles:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li><strong>" . $tabla . "</strong></li>";
    }
    echo "</ul>";
    
    // Verificar si existe tabla tallas
    if (in_array('tallas', $tablas)) {
        echo "<h2>2. Tallas disponibles:</h2>";
        $stmt = $pdo->query("SELECT * FROM tallas");
        $tallas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Descripción</th>";
        echo "</tr>";
        
        foreach ($tallas as $talla) {
            echo "<tr>";
            echo "<td>" . $talla['id'] . "</td>";
            echo "<td>" . $talla['nombre'] . "</td>";
            echo "<td>" . ($talla['descripcion'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No existe tabla 'tallas'</p>";
    }
    
    // Verificar si existe tabla colores
    if (in_array('colores', $tablas)) {
        echo "<h2>3. Colores disponibles:</h2>";
        $stmt = $pdo->query("SELECT * FROM colores");
        $colores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Nombre</th><th>Código</th>";
        echo "</tr>";
        
        foreach ($colores as $color) {
            echo "<tr>";
            echo "<td>" . $color['id'] . "</td>";
            echo "<td>" . $color['nombre'] . "</td>";
            echo "<td>" . ($color['codigo'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No existe tabla 'colores'</p>";
    }
    
    // Probar generación de variantes
    echo "<h2>4. Prueba de generación de variantes:</h2>";
    
    // Obtener productos
    $stmt = $pdo->query("SELECT id, nombre, precio, tipo_producto FROM productos LIMIT 3");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Definir tallas y colores por defecto si no existen las tablas
    $tallas_default = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $colores_default = ['Negro', 'Blanco', 'Azul', 'Rojo', 'Verde', 'Gris'];
    
    echo "<h3>Variantes generadas para los primeros 3 productos:</h3>";
    
    foreach ($productos as $producto) {
        echo "<h4>Producto: " . htmlspecialchars($producto['nombre']) . "</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Variante</th><th>Talla</th><th>Color</th><th>Stock</th><th>Precio</th>";
        echo "</tr>";
        
        $contador = 1;
        foreach ($tallas_default as $talla) {
            foreach ($colores_default as $color) {
                echo "<tr>";
                echo "<td>Variante " . $contador . "</td>";
                echo "<td>" . $talla . "</td>";
                echo "<td>" . $color . "</td>";
                echo "<td>0</td>";
                echo "<td>$" . $producto['precio'] . "</td>";
                echo "</tr>";
                $contador++;
            }
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='vista/inventario.php'>← Ir al Inventario</a></p>";
?> 