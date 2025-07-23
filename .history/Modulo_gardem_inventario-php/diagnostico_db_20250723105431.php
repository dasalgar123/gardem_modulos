<?php
require_once 'config/database.php';

echo "<h2>🔍 Diagnóstico de Base de Datos</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style>";

// 1. Verificar conexión
echo "<div class='card'>";
echo "<h3>1. Conexión a Base de Datos</h3>";
try {
    $pdo->query("SELECT 1");
    echo "<p class='success'>✅ Conexión exitosa a la base de datos</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}
echo "</div>";

// 2. Listar todas las tablas
echo "<div class='card'>";
echo "<h3>2. Tablas Existentes</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tablas encontradas: " . count($tablas) . "</p>";
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al listar tablas: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Verificar tablas específicas y sus datos
$tablas_importantes = ['productos', 'categorias', 'proveedores', 'entradas', 'salidas', 'inventario'];

echo "<div class='card'>";
echo "<h3>3. Verificación de Tablas Importantes</h3>";

foreach ($tablas_importantes as $tabla) {
    echo "<h4>Tabla: $tabla</h4>";
    
    try {
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() == 0) {
            echo "<p class='error'>❌ La tabla '$tabla' NO existe</p>";
            continue;
        }
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
        $count = $stmt->fetch()['total'];
        echo "<p class='success'>✅ Tabla '$tabla' existe con $count registros</p>";
        
        // Mostrar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE $tabla");
        $columnas = $stmt->fetchAll();
        echo "<p><strong>Columnas:</strong></p>";
        echo "<ul>";
        foreach ($columnas as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Mostrar algunos datos de ejemplo
        if ($count > 0) {
            $stmt = $pdo->query("SELECT * FROM $tabla LIMIT 3");
            $datos = $stmt->fetchAll();
            echo "<p><strong>Datos de ejemplo:</strong></p>";
            echo "<table>";
            if (!empty($datos)) {
                echo "<tr>";
                foreach (array_keys($datos[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                foreach ($datos as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al verificar tabla '$tabla': " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}
echo "</div>";

// 4. Probar consultas específicas
echo "<div class='card'>";
echo "<h3>4. Prueba de Consultas Específicas</h3>";

// Consulta de productos
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p><strong>Productos:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de productos: " . $e->getMessage() . "</p>";
}

// Consulta de categorías
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $result = $stmt->fetch();
    echo "<p><strong>Categorías:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de categorías: " . $e->getMessage() . "</p>";
}

// Consulta de proveedores
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM proveedores");
    $result = $stmt->fetch();
    echo "<p><strong>Proveedores:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de proveedores: " . $e->getMessage() . "</p>";
}

// Consulta de entradas hoy
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas WHERE DATE(fecha) = CURDATE()");
    $result = $stmt->fetch();
    echo "<p><strong>Entradas hoy:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de entradas: " . $e->getMessage() . "</p>";
}

// Consulta de salidas hoy
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM salidas WHERE DATE(fecha) = CURDATE()");
    $result = $stmt->fetch();
    echo "<p><strong>Salidas hoy:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de salidas: " . $e->getMessage() . "</p>";
}

// Consulta de inventario
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario WHERE cantidad < 10");
    $result = $stmt->fetch();
    echo "<p><strong>Stock bajo:</strong> " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en consulta de stock bajo: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 5. Probar función obtenerEstadisticas
echo "<div class='card'>";
echo "<h3>5. Prueba de Función obtenerEstadisticas()</h3>";
$stats = obtenerEstadisticas();
echo "<p><strong>Resultado de la función:</strong></p>";
echo "<ul>";
foreach ($stats as $key => $value) {
    echo "<li><strong>$key:</strong> $value</li>";
}
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🔧 Acciones Recomendadas</h3>";
echo "<p>Si ves ceros pero hay datos en las tablas, puede ser por:</p>";
echo "<ul>";
echo "<li>Nombres de columnas diferentes</li>";
echo "<li>Estructura de tabla diferente</li>";
echo "<li>Fechas en formato diferente</li>";
echo "<li>Relaciones entre tablas incorrectas</li>";
echo "</ul>";
echo "<p><a href='index.php' class='btn btn-primary'>Volver al Dashboard</a></p>";
?> 