<?php
require_once 'config/database.php';

echo "<h2>📊 Reporte Completo de Inventario, Entradas y Salidas</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .resumen { background-color: #e8f4f8; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

// ========================================
// 1. REVISAR INVENTARIO
// ========================================
echo "<div class='card'>";
echo "<h3>📦 1. REVISAR INVENTARIO</h3>";

try {
    // Verificar si existe la tabla inventario
    $stmt = $pdo->query("SHOW TABLES LIKE 'inventario'");
    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>❌ La tabla 'inventario' NO existe</p>";
    } else {
        // Contar total de registros en inventario
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario");
        $total_inventario = $stmt->fetch()['total'];
        echo "<p class='success'>✅ Tabla inventario existe con $total_inventario registros</p>";
        
        // Mostrar estructura de la tabla inventario
        $stmt = $pdo->query("DESCRIBE inventario");
        $columnas_inv = $stmt->fetchAll();
        echo "<p><strong>Estructura de tabla inventario:</strong></p>";
        echo "<ul>";
        foreach ($columnas_inv as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Mostrar datos de inventario
        if ($total_inventario > 0) {
            $stmt = $pdo->query("SELECT * FROM inventario LIMIT 10");
            $datos_inv = $stmt->fetchAll();
            echo "<p><strong>Datos de inventario (primeros 10):</strong></p>";
            echo "<table>";
            if (!empty($datos_inv)) {
                echo "<tr>";
                foreach (array_keys($datos_inv[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                foreach ($datos_inv as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        
        // Resumen de inventario
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_productos,
            SUM(cantidad) as stock_total,
            COUNT(CASE WHEN cantidad = 0 THEN 1 END) as agotados,
            COUNT(CASE WHEN cantidad < 10 AND cantidad > 0 THEN 1 END) as stock_bajo
            FROM inventario");
        $resumen_inv = $stmt->fetch();
        
        echo "<div class='resumen'>";
        echo "<h4>📋 Resumen de Inventario:</h4>";
        echo "<p><strong>Total productos en inventario:</strong> {$resumen_inv['total_productos']}</p>";
        echo "<p><strong>Stock total:</strong> {$resumen_inv['stock_total']}</p>";
        echo "<p><strong>Productos agotados:</strong> {$resumen_inv['agotados']}</p>";
        echo "<p><strong>Productos con stock bajo:</strong> {$resumen_inv['stock_bajo']}</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al revisar inventario: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ========================================
// 2. REVISAR ENTRADAS
// ========================================
echo "<div class='card'>";
echo "<h3>⬇️ 2. REVISAR ENTRADAS</h3>";

try {
    // Verificar si existe la tabla entradas
    $stmt = $pdo->query("SHOW TABLES LIKE 'entradas'");
    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>❌ La tabla 'entradas' NO existe</p>";
    } else {
        // Contar total de entradas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas");
        $total_entradas = $stmt->fetch()['total'];
        echo "<p class='success'>✅ Tabla entradas existe con $total_entradas registros</p>";
        
        // Mostrar estructura de la tabla entradas
        $stmt = $pdo->query("DESCRIBE entradas");
        $columnas_ent = $stmt->fetchAll();
        echo "<p><strong>Estructura de tabla entradas:</strong></p>";
        echo "<ul>";
        foreach ($columnas_ent as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Mostrar datos de entradas
        if ($total_entradas > 0) {
            $stmt = $pdo->query("SELECT * FROM entradas ORDER BY fecha DESC LIMIT 10");
            $datos_ent = $stmt->fetchAll();
            echo "<p><strong>Datos de entradas (últimas 10):</strong></p>";
            echo "<table>";
            if (!empty($datos_ent)) {
                echo "<tr>";
                foreach (array_keys($datos_ent[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                foreach ($datos_ent as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        
        // Resumen de entradas
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_entradas,
            COUNT(CASE WHEN DATE(fecha) = CURDATE() THEN 1 END) as entradas_hoy,
            COUNT(CASE WHEN MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) THEN 1 END) as entradas_mes,
            SUM(cantidad) as cantidad_total
            FROM entradas");
        $resumen_ent = $stmt->fetch();
        
        echo "<div class='resumen'>";
        echo "<h4>📋 Resumen de Entradas:</h4>";
        echo "<p><strong>Total entradas:</strong> {$resumen_ent['total_entradas']}</p>";
        echo "<p><strong>Entradas hoy:</strong> {$resumen_ent['entradas_hoy']}</p>";
        echo "<p><strong>Entradas este mes:</strong> {$resumen_ent['entradas_mes']}</p>";
        echo "<p><strong>Cantidad total ingresada:</strong> {$resumen_ent['cantidad_total']}</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al revisar entradas: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ========================================
// 3. REVISAR SALIDAS
// ========================================
echo "<div class='card'>";
echo "<h3>⬆️ 3. REVISAR SALIDAS</h3>";

try {
    // Verificar si existe la tabla salidas
    $stmt = $pdo->query("SHOW TABLES LIKE 'salidas'");
    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>❌ La tabla 'salidas' NO existe</p>";
    } else {
        // Contar total de salidas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM salidas");
        $total_salidas = $stmt->fetch()['total'];
        echo "<p class='success'>✅ Tabla salidas existe con $total_salidas registros</p>";
        
        // Mostrar estructura de la tabla salidas
        $stmt = $pdo->query("DESCRIBE salidas");
        $columnas_sal = $stmt->fetchAll();
        echo "<p><strong>Estructura de tabla salidas:</strong></p>";
        echo "<ul>";
        foreach ($columnas_sal as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Mostrar datos de salidas
        if ($total_salidas > 0) {
            $stmt = $pdo->query("SELECT * FROM salidas ORDER BY fecha DESC LIMIT 10");
            $datos_sal = $stmt->fetchAll();
            echo "<p><strong>Datos de salidas (últimas 10):</strong></p>";
            echo "<table>";
            if (!empty($datos_sal)) {
                echo "<tr>";
                foreach (array_keys($datos_sal[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                foreach ($datos_sal as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        
        // Resumen de salidas
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_salidas,
            COUNT(CASE WHEN DATE(fecha) = CURDATE() THEN 1 END) as salidas_hoy,
            COUNT(CASE WHEN MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) THEN 1 END) as salidas_mes,
            SUM(cantidad) as cantidad_total
            FROM salidas");
        $resumen_sal = $stmt->fetch();
        
        echo "<div class='resumen'>";
        echo "<h4>📋 Resumen de Salidas:</h4>";
        echo "<p><strong>Total salidas:</strong> {$resumen_sal['total_salidas']}</p>";
        echo "<p><strong>Salidas hoy:</strong> {$resumen_sal['salidas_hoy']}</p>";
        echo "<p><strong>Salidas este mes:</strong> {$resumen_sal['salidas_mes']}</p>";
        echo "<p><strong>Cantidad total vendida:</strong> {$resumen_sal['cantidad_total']}</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al revisar salidas: " . $e->getMessage() . "</p>";
}
echo "</div>";

// ========================================
// 4. REPORTE FINAL
// ========================================
echo "<div class='card'>";
echo "<h3>📊 4. REPORTE FINAL</h3>";

try {
    // Obtener estadísticas finales
    $stats = obtenerEstadisticas();
    
    echo "<div class='resumen'>";
    echo "<h4>🎯 Estadísticas del Sistema:</h4>";
    echo "<table>";
    echo "<tr><th>Métrica</th><th>Valor</th></tr>";
    echo "<tr><td>Productos</td><td>{$stats['productos']}</td></tr>";
    echo "<tr><td>Categorías</td><td>{$stats['categorias']}</td></tr>";
    echo "<tr><td>Proveedores</td><td>{$stats['proveedores']}</td></tr>";
    echo "<tr><td>Entradas Hoy</td><td>{$stats['entradas_hoy']}</td></tr>";
    echo "<tr><td>Salidas Hoy</td><td>{$stats['salidas_hoy']}</td></tr>";
    echo "<tr><td>Stock Bajo</td><td>{$stats['stock_bajo']}</td></tr>";
    echo "<tr><td>Agotados</td><td>{$stats['agotados']}</td></tr>";
    echo "<tr><td>Movimientos del Mes</td><td>{$stats['movimientos_mes']}</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Análisis de problemas
    echo "<div class='resumen'>";
    echo "<h4>🔍 Análisis de Problemas:</h4>";
    
    if ($stats['productos'] == 0) {
        echo "<p class='warning'>⚠️ No hay productos registrados en la base de datos</p>";
    }
    if ($stats['entradas_hoy'] == 0) {
        echo "<p class='info'>ℹ️ No hay entradas registradas para hoy</p>";
    }
    if ($stats['salidas_hoy'] == 0) {
        echo "<p class='info'>ℹ️ No hay salidas registradas para hoy</p>";
    }
    if ($stats['stock_bajo'] > 0) {
        echo "<p class='warning'>⚠️ Hay {$stats['stock_bajo']} productos con stock bajo</p>";
    }
    if ($stats['agotados'] > 0) {
        echo "<p class='error'>❌ Hay {$stats['agotados']} productos agotados</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al generar reporte final: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<hr>";
echo "<h3>🔧 Acciones Recomendadas</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Volver al Dashboard</a></li>";
echo "<li><a href='diagnostico_db.php'>Diagnóstico Completo</a></li>";
echo "<li><a href='crear_datos_prueba.php'>Crear Datos de Prueba</a></li>";
echo "</ul>";
?> 