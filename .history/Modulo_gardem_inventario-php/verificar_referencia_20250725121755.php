<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=gardelcatalogo', 'root', '');
    
    // Verificar si existe la columna referencia
    $stmt = $pdo->query("DESCRIBE productos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas en productos: " . implode(', ', $columns) . "\n";
    
    // Verificar si hay datos en la columna referencia
    $stmt = $pdo->query("SELECT id, nombre, referencia FROM productos LIMIT 5");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nPrimeros 5 productos:\n";
    foreach ($productos as $producto) {
        echo "ID: {$producto['id']}, Nombre: {$producto['nombre']}, Referencia: " . ($producto['referencia'] ?? 'NULL') . "\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 