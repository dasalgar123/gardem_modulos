<?php
require_once 'config/database.php';

echo "<h2>🔍 Debug de Salidas</h2>";

try {
    // 1. Verificar si la tabla existe
    echo "<h3>1. Verificar tabla productos_salidas:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos_salidas'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La tabla productos_salidas existe<br>";
    } else {
        echo "❌ La tabla productos_salidas NO existe<br>";
        exit();
    }
    
    // 2. Verificar estructura
    echo "<h3>2. Estructura de la tabla:</h3>";
    $stmt = $pdo->query("DESCRIBE productos_salidas");
    $columns = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Contar registros
    echo "<h3>3. Contar registros:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_salidas");
    $count = $stmt->fetch();
    echo "Total de registros: {$count['total']}<br>";
    
    // 4. Ver registros existentes
    echo "<h3>4. Registros existentes:</h3>";
    $stmt = $pdo->query("SELECT * FROM productos_salidas ORDER BY fecha DESC LIMIT 5");
    $registros = $stmt->fetchAll();
    
    if (count($registros) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>producto_id</th><th>cantidad</th><th>motivo</th><th>fecha</th><th>destinatario_tipo</th><th>destinatario_id</th><th>cliente_id</th><th>factura_remision</th></tr>";
        foreach ($registros as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>{$reg['producto_id']}</td>";
            echo "<td>{$reg['cantidad']}</td>";
            echo "<td>{$reg['motivo']}</td>";
            echo "<td>{$reg['fecha']}</td>";
            echo "<td>{$reg['destinatario_tipo']}</td>";
            echo "<td>{$reg['destinatario_id']}</td>";
            echo "<td>{$reg['cliente_id']}</td>";
            echo "<td>{$reg['factura_remision']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay registros en la tabla<br>";
    }
    
    // 5. Verificar si existen las columnas talla_id y color_id
    echo "<h3>5. Verificar columnas talla_id y color_id:</h3>";
    $tiene_talla = false;
    $tiene_color = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'talla_id') {
            $tiene_talla = true;
        }
        if ($col['Field'] === 'color_id') {
            $tiene_color = true;
        }
    }
    
    echo "talla_id: " . ($tiene_talla ? "✅ Existe" : "❌ No existe") . "<br>";
    echo "color_id: " . ($tiene_color ? "✅ Existe" : "❌ No existe") . "<br>";
    
    // 6. Si no existen, agregarlas
    if (!$tiene_talla || !$tiene_color) {
        echo "<h3>6. Agregando columnas faltantes:</h3>";
        if (!$tiene_talla) {
            $pdo->exec("ALTER TABLE productos_salidas ADD COLUMN talla_id INT NULL");
            echo "✅ Columna talla_id agregada<br>";
        }
        if (!$tiene_color) {
            $pdo->exec("ALTER TABLE productos_salidas ADD COLUMN color_id INT NULL");
            echo "✅ Columna color_id agregada<br>";
        }
    }
    
    // 7. Probar consulta con JOIN
    echo "<h3>7. Probar consulta con JOIN:</h3>";
    try {
        $stmt = $pdo->query("
            SELECT 
                ps.*,
                p.nombre as producto_nombre,
                t.nombre as talla_nombre,
                c.nombre as color_nombre
            FROM productos_salidas ps
            LEFT JOIN productos p ON ps.producto_id = p.id
            LEFT JOIN tallas t ON ps.talla_id = t.id
            LEFT JOIN colores c ON ps.color_id = c.id
            ORDER BY ps.fecha DESC
            LIMIT 3
        ");
        $resultados = $stmt->fetchAll();
        echo "✅ Consulta con JOIN exitosa<br>";
        echo "Registros encontrados: " . count($resultados) . "<br>";
        
        if (count($resultados) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Producto</th><th>Talla</th><th>Color</th><th>Cantidad</th><th>Motivo</th></tr>";
            foreach ($resultados as $res) {
                echo "<tr>";
                echo "<td>{$res['id']}</td>";
                echo "<td>{$res['producto_nombre']}</td>";
                echo "<td>" . ($res['talla_nombre'] ? $res['talla_nombre'] : 'N/A') . "</td>";
                echo "<td>" . ($res['color_nombre'] ? $res['color_nombre'] : 'N/A') . "</td>";
                echo "<td>{$res['cantidad']}</td>";
                echo "<td>{$res['motivo']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "❌ Error en consulta con JOIN: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error general:</h3>";
    echo $e->getMessage();
}
?> 