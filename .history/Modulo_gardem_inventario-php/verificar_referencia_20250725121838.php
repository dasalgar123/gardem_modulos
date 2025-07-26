<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=gardelcatalogo', 'root', '');
    
    // Verificar si existe la columna referencia
    $stmt = $pdo->query("DESCRIBE productos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas en productos: " . implode(', ', $columns) . "\n";
    
    // Verificar si hay datos en la columna referencia
    $stmt = $pdo->query("SELECT id, nombre, referencia FROM productos WHERE referencia IS NOT NULL AND referencia != '' LIMIT 5");
    $productos_con_ref = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nProductos CON referencia:\n";
    foreach ($productos_con_ref as $producto) {
        echo "ID: {$producto['id']}, Nombre: {$producto['nombre']}, Referencia: {$producto['referencia']}\n";
    }
    
    $stmt = $pdo->query("SELECT id, nombre, referencia FROM productos WHERE referencia IS NULL OR referencia = '' LIMIT 5");
    $productos_sin_ref = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nProductos SIN referencia:\n";
    foreach ($productos_sin_ref as $producto) {
        echo "ID: {$producto['id']}, Nombre: {$producto['nombre']}, Referencia: " . ($producto['referencia'] ?? 'NULL') . "\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 