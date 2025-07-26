<?php
// Verificar referencia en productos_salidas
require_once 'config/database.php';

try {
    echo "<h2>Verificando referencia en productos_salidas</h2>";
    
    // Verificar si existe la columna referencia
    $stmt = $pdo->query("DESCRIBE productos_salidas");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('referencia', $columnas)) {
        echo "<p style='color: green;'>✓ La columna 'referencia' existe en productos_salidas</p>";
        
        // Verificar datos
        $stmt = $pdo->query("SELECT id, referencia FROM productos_salidas LIMIT 10");
        $datos = $stmt->fetchAll();
        
        echo "<h3>Primeros 10 registros:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Referencia</th></tr>";
        
        foreach ($datos as $row) {
            $referencia = $row['referencia'] ? $row['referencia'] : 'NULL';
            echo "<tr><td>{$row['id']}</td><td>{$referencia}</td></tr>";
        }
        echo "</table>";
        
        // Contar registros con referencia
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_salidas WHERE referencia IS NOT NULL AND referencia != ''");
        $con_referencia = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_salidas");
        $total = $stmt->fetch()['total'];
        
        echo "<p>Registros con referencia: {$con_referencia} de {$total}</p>";
        
    } else {
        echo "<p style='color: red;'>✗ La columna 'referencia' NO existe en productos_salidas</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 