<?php
require_once 'config/database.php';

try {
    // Verificar si la tabla existe y su estructura
    $stmt = $pdo->query("SHOW CREATE TABLE productos_entradas");
    $result = $stmt->fetch();
    echo "<h3>Estructura actual de la tabla productos_entradas:</h3>";
    echo "<pre>" . $result[1] . "</pre>";
    
    // Intentar arreglar el autoincrement
    echo "<h3>Arreglando autoincrement...</h3>";
    
    // Opción 1: Modificar la columna id para que sea autoincrement
    $pdo->exec("ALTER TABLE productos_entradas MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY");
    echo "✅ Auto increment configurado correctamente<br>";
    
    // Opción 2: Si la opción 1 falla, recrear la tabla
    echo "<h3>Si el autoincrement no funciona, aquí está el SQL para recrear la tabla:</h3>";
    echo "<pre>
DROP TABLE IF EXISTS productos_entradas;
CREATE TABLE productos_entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    bodega_id INT NULL,
    cantidad INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(100) NULL,
    beneficiario_tipo ENUM('proveedor', 'cliente', 'interno') NULL,
    beneficiario_id INT NULL,
    factura_remision VARCHAR(50) NULL,
    beneficiario VARCHAR(100) NULL,
    INDEX idx_producto (producto_id),
    INDEX idx_fecha (fecha)
);
    </pre>";
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    echo "<h3>SQL para crear la tabla correctamente:</h3>";
    echo "<pre>
CREATE TABLE IF NOT EXISTS productos_entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    bodega_id INT NULL,
    cantidad INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(100) NULL,
    beneficiario_tipo ENUM('proveedor', 'cliente', 'interno') NULL,
    beneficiario_id INT NULL,
    factura_remision VARCHAR(50) NULL,
    beneficiario VARCHAR(100) NULL,
    INDEX idx_producto (producto_id),
    INDEX idx_fecha (fecha)
);
    </pre>";
}
?> 