<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=gardelcatalogo', 'root', '');
    
    // Agregar columna referencia a productos_salidas
    $stmt = $pdo->prepare("ALTER TABLE productos_salidas ADD COLUMN referencia VARCHAR(50) AFTER id");
    $stmt->execute();
    
    echo "Columna 'referencia' agregada correctamente a la tabla productos_salidas\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 