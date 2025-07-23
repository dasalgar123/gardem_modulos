<?php
require_once '../config/database.php';

echo "<h2>🔍 Verificación de Foreign Keys</h2>";

try {
    // 1. Verificar foreign keys existentes
    echo "<h3>1. Foreign Keys Existentes:</h3>";
    
    $stmt = $pdo->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'gardelcatalogo' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME
    ");
    
    $foreign_keys = $stmt->fetchAll();
    
    if (empty($foreign_keys)) {
        echo "<p style='color: orange;'>⚠️ No se encontraron foreign keys configuradas</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Tabla</th><th>Constraint</th><th>Columna</th><th>Referencia</th></tr>";
        foreach ($foreign_keys as $fk) {
            echo "<tr>";
            echo "<td>{$fk['TABLE_NAME']}</td>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Verificar datos huérfanos
    echo "<h3>2. Datos Huérfanos:</h3>";
    
    // Verificar productos_salidas
    $stmt = $pdo->query("
        SELECT COUNT(*) as cantidad
        FROM productos_salidas ps
        LEFT JOIN productos p ON ps.producto_id = p.id
        WHERE p.id IS NULL
    ");
    $result = $stmt->fetch();
    if ($result['cantidad'] > 0) {
        echo "<p style='color: red;'>❌ productos_salidas con producto_id inexistente: {$result['cantidad']}</p>";
    } else {
        echo "<p style='color: green;'>✅ productos_salidas: OK</p>";
    }
    
    // Verificar productos_entradas
    $stmt = $pdo->query("
        SELECT COUNT(*) as cantidad
        FROM productos_entradas pe
        LEFT JOIN productos p ON pe.producto_id = p.id
        WHERE p.id IS NULL
    ");
    $result = $stmt->fetch();
    if ($result['cantidad'] > 0) {
        echo "<p style='color: red;'>❌ productos_entradas con producto_id inexistente: {$result['cantidad']}</p>";
    } else {
        echo "<p style='color: green;'>✅ productos_entradas: OK</p>";
    }
    
    // 3. Mostrar estructura de tablas
    echo "<h3>3. Estructura de Tablas:</h3>";
    
    $tablas = ['productos_salidas', 'productos_entradas', 'productos', 'cliente', 'proveedor'];
    
    foreach ($tablas as $tabla) {
        echo "<h4>Tabla: $tabla</h4>";
        $stmt = $pdo->query("DESCRIBE $tabla");
        $columnas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // 4. Opciones de corrección
    echo "<h3>4. Opciones de Corrección:</h3>";
    echo "<p>Si hay problemas con foreign keys, puedes:</p>";
    echo "<ol>";
    echo "<li>Ejecutar el script <code>corregir_foreign_keys_completo.sql</code> en phpMyAdmin</li>";
    echo "<li>O usar el sistema sin foreign keys (funciona pero sin validación de integridad)</li>";
    echo "</ol>";
    
    // 5. Botón para limpiar datos huérfanos
    if (isset($_POST['limpiar'])) {
        echo "<h3>5. Limpiando Datos Huérfanos:</h3>";
        
        try {
            // Eliminar salidas con productos inexistentes
            $stmt = $pdo->prepare("
                DELETE ps FROM productos_salidas ps
                LEFT JOIN productos p ON ps.producto_id = p.id
                WHERE p.id IS NULL
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Datos huérfanos en productos_salidas eliminados</p>";
            
            // Eliminar entradas con productos inexistentes
            $stmt = $pdo->prepare("
                DELETE pe FROM productos_entradas pe
                LEFT JOIN productos p ON pe.producto_id = p.id
                WHERE p.id IS NULL
            ");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Datos huérfanos en productos_entradas eliminados</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al limpiar: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='limpiar' style='background: red; color: white; padding: 10px; border: none; border-radius: 5px;'>";
    echo "🧹 Limpiar Datos Huérfanos";
    echo "</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th { background: #f0f0f0; padding: 8px; }
td { padding: 6px; }
</style> 