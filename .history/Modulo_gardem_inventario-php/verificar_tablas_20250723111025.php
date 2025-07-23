<?php
require_once 'config/database.php';

echo "<h2>🔍 Verificar Tablas de Base de Datos</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    // Listar todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📊 Tablas encontradas en la base de datos:</h3>";
    echo "<p class='success'>Total de tablas: " . count($tablas) . "</p>";
    
    if (count($tablas) > 0) {
        echo "<table>";
        echo "<tr><th>#</th><th>Nombre de Tabla</th><th>Registros</th></tr>";
        
        foreach ($tablas as $index => $tabla) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
                $count = $stmt->fetch()['total'];
                echo "<tr>";
                echo "<td>" . ($index + 1) . "</td>";
                echo "<td><strong>$tabla</strong></td>";
                echo "<td>$count</td>";
                echo "</tr>";
            } catch (Exception $e) {
                echo "<tr>";
                echo "<td>" . ($index + 1) . "</td>";
                echo "<td><strong>$tabla</strong></td>";
                echo "<td class='error'>Error: " . $e->getMessage() . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p class='error'>No se encontraron tablas en la base de datos.</p>";
    }
    
    // Verificar tablas específicas que necesita el sistema
    echo "<h3>🔍 Verificación de tablas específicas:</h3>";
    
    $tablas_requeridas = ['productos', 'categorias', 'proveedores', 'entradas', 'salidas', 'inventario'];
    
    foreach ($tablas_requeridas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Tabla '$tabla' existe</p>";
        } else {
            echo "<p class='error'>❌ Tabla '$tabla' NO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al Dashboard</a></p>";
?> 