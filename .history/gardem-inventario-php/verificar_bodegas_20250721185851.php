<?php
require_once 'config/database.php';

try {
    echo "<h2>🔍 Verificando estructura de bodegas</h2>";
    
    // Verificar si existe la tabla bodega
    $stmt = $pdo->query("SHOW TABLES LIKE 'bodega'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ La tabla 'bodega' NO existe</p>";
        echo "<h3>Creando tabla bodega:</h3>";
        echo "<pre>
CREATE TABLE bodega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(200) NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
        </pre>";
        
        // Crear la tabla
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bodega (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                ubicacion VARCHAR(200) NULL,
                descripcion TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ Tabla bodega creada<br>";
        
        // Insertar bodegas por defecto
        $bodegas = [
            ['Bodega Principal', 'Almacén Central'],
            ['Bodega Secundaria', 'Almacén Norte'],
            ['Bodega de Distribución', 'Centro de Distribución']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO bodega (nombre, ubicacion) VALUES (?, ?)");
        foreach ($bodegas as $bodega) {
            $stmt->execute($bodega);
        }
        echo "✅ Bodegas por defecto insertadas<br>";
        
    } else {
        echo "<p style='color: green;'>✅ La tabla 'bodega' existe</p>";
    }
    
    // Mostrar bodegas existentes
    echo "<h3>📋 Bodegas disponibles:</h3>";
    $stmt = $pdo->query("SELECT * FROM bodega ORDER BY id");
    $bodegas = $stmt->fetchAll();
    
    if (count($bodegas) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Ubicación</th></tr>";
        foreach ($bodegas as $bodega) {
            echo "<tr>";
            echo "<td>{$bodega['id']}</td>";
            echo "<td>{$bodega['nombre']}</td>";
            echo "<td>{$bodega['ubicacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay bodegas registradas</p>";
    }
    
    // Verificar estructura de inventario_bodega
    echo "<h3>🔧 Verificando tabla inventario_bodega:</h3>";
    $stmt = $pdo->query("SHOW CREATE TABLE inventario_bodega");
    $result = $stmt->fetch();
    echo "<pre>" . $result[1] . "</pre>";
    
    // Verificar si hay registros con bodega_id inválidos
    echo "<h3>⚠️ Verificando registros con bodega_id inválidos:</h3>";
    $stmt = $pdo->query("
        SELECT ib.*, b.nombre as bodega_nombre 
        FROM inventario_bodega ib 
        LEFT JOIN bodega b ON ib.bodega_id = b.id 
        WHERE ib.bodega_id IS NOT NULL AND b.id IS NULL
    ");
    $invalidos = $stmt->fetchAll();
    
    if (count($invalidos) > 0) {
        echo "<p style='color: red;'>❌ Hay registros con bodega_id inválidos:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>producto_id</th><th>bodega_id</th><th>stock_actual</th></tr>";
        foreach ($invalidos as $inv) {
            echo "<tr>";
            echo "<td>{$inv['id']}</td>";
            echo "<td>{$inv['producto_id']}</td>";
            echo "<td>{$inv['bodega_id']}</td>";
            echo "<td>{$inv['stock_actual']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>🔧 Solución:</h4>";
        echo "<pre>
-- Opción 1: Eliminar bodega_id inválidos
UPDATE inventario_bodega SET bodega_id = NULL WHERE bodega_id NOT IN (SELECT id FROM bodega);

-- Opción 2: Asignar a bodega por defecto
UPDATE inventario_bodega SET bodega_id = 1 WHERE bodega_id NOT IN (SELECT id FROM bodega);
        </pre>";
    } else {
        echo "<p style='color: green;'>✅ No hay registros con bodega_id inválidos</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 