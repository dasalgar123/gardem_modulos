<?php
// Script para agregar la columna stock_minimo a la tabla inventario_bodega
require_once '../config/database.php';

echo "<h2>Reparación de Tabla Inventario</h2>";
echo "<p>Base de datos: gardelcatalogo</p>";

try {
    // 1. Verificar si existe la tabla inventario_bodega
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventario_bodega'");
    $tabla_exists = $stmt->rowCount() > 0;
    
    if (!$tabla_exists) {
        echo "<div style='color: red;'>❌ No existe la tabla 'inventario_bodega'</div>";
        echo "<p>Necesitas ejecutar el script SQL completo para crear todas las tablas.</p>";
        exit;
    }
    
    echo "<div style='color: green;'>✅ Tabla 'inventario_bodega' encontrada</div>";
    
    // 2. Verificar si existe la columna stock_minimo
    $stmt = $pdo->query("SHOW COLUMNS FROM inventario_bodega LIKE 'stock_minimo'");
    $columna_exists = $stmt->rowCount() > 0;
    
    if ($columna_exists) {
        echo "<div style='color: green;'>✅ La columna 'stock_minimo' ya existe</div>";
        echo "<p>No es necesario realizar cambios.</p>";
    } else {
        echo "<div style='color: orange;'>⚠️ La columna 'stock_minimo' no existe</div>";
        
        // 3. Agregar la columna stock_minimo
        $alter_sql = "ALTER TABLE inventario_bodega ADD COLUMN stock_minimo INT DEFAULT 10 AFTER stock_actual";
        
        $pdo->exec($alter_sql);
        echo "<div style='color: green;'>✅ Columna 'stock_minimo' agregada exitosamente</div>";
        
        // 4. Verificar que la columna se agregó correctamente
        $stmt = $pdo->query("SHOW COLUMNS FROM inventario_bodega LIKE 'stock_minimo'");
        $verificacion = $stmt->rowCount() > 0;
        
        if ($verificacion) {
            echo "<div style='color: green;'>✅ Verificación exitosa: la columna 'stock_minimo' ahora existe</div>";
        } else {
            echo "<div style='color: red;'>❌ Error: la columna no se agregó correctamente</div>";
        }
    }
    
    // 5. Mostrar estructura actual de la tabla
    echo "<h3>Estructura actual de la tabla inventario_bodega:</h3>";
    $stmt = $pdo->query("DESCRIBE inventario_bodega");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Por defecto</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td><strong>{$columna['Field']}</strong></td>";
        echo "<td>{$columna['Type']}</td>";
        echo "<td>{$columna['Null']}</td>";
        echo "<td>{$columna['Key']}</td>";
        echo "<td>{$columna['Default']}</td>";
        echo "<td>{$columna['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 6. Contar registros en la tabla
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario_bodega");
    $total_registros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de registros en inventario_bodega:</strong> {$total_registros}</p>";
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0;'>";
    echo "<strong>✅ Reparación completada!</strong><br>";
    echo "La tabla inventario_bodega ahora tiene la columna stock_minimo y debería funcionar correctamente.";
    echo "</div>";
    
    echo "<p><a href='index.php?page=inventario' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Inventario</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?> 