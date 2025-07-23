<?php
// ========================================
// SCRIPT DE OPTIMIZACIÓN DE BASE DE DATOS
// ========================================

require_once 'database.php';

echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6;'>";
echo "<h2 style='color: #007bff;'>🔧 Optimización de Base de Datos</h2>";

try {
    // 1. CREAR ÍNDICES PARA OPTIMIZAR CONSULTAS
    echo "<h3>📊 Creando índices...</h3>";
    
    $indices = [

        // Índices para productos
        "CREATE INDEX IF NOT EXISTS idx_productos_nombre ON productos(nombre)",
        "CREATE INDEX IF NOT EXISTS idx_productos_categoria ON productos(categoria_id)",
        "CREATE INDEX IF NOT EXISTS idx_productos_tipo ON productos(tipo_producto)",

        // Índices para entradas
        "CREATE INDEX IF NOT EXISTS idx_entradas_producto ON productos_entradas(producto_id)",
        "CREATE INDEX IF NOT EXISTS idx_entradas_fecha ON productos_entradas(fecha)",
        "CREATE INDEX IF NOT EXISTS idx_entradas_proveedor ON productos_entradas(proveedor_id)",

        // Índices para salidas
        "CREATE INDEX IF NOT EXISTS idx_salidas_producto ON productos_salidas(producto_id)",
        "CREATE INDEX IF NOT EXISTS idx_salidas_fecha ON productos_salidas(fecha)",
        "CREATE INDEX IF NOT EXISTS idx_salidas_cliente ON productos_salidas(cliente_id)",

        // Índices para categorías
        "CREATE INDEX IF NOT EXISTS idx_categorias_nombre ON categorias(nombre)",

        // Índices para colores
        "CREATE INDEX IF NOT EXISTS idx_colores_nombre ON colores(nombre)",

        // Índices para tallas
        "CREATE INDEX IF NOT EXISTS idx_tallas_nombre ON tallas(nombre)",

        // Índices para proveedores
        "CREATE INDEX IF NOT EXISTS idx_proveedores_nombre ON proveedores(nombre)",
        "CREATE INDEX IF NOT EXISTS idx_proveedores_estado ON proveedores(estado)",

        // Índices para clientes
        "CREATE INDEX IF NOT EXISTS idx_clientes_nombre ON clientes(nombre)",
        "CREATE INDEX IF NOT EXISTS idx_clientes_email ON clientes(email)"
    ];
    
    $indices_creados = 0;
    foreach ($indices as $indice) {
        try {
            $pdo->exec($indice);
            echo "<p style='color: #28a745;'>✅ $indice</p>";
            $indices_creados++;
        } catch (Exception $e) {
            echo "<p style='color: #dc3545;'>❌ Error en: $indice - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p><strong>Índices creados: $indices_creados</strong></p>";
    
    // 2. ANALIZAR TABLAS
    echo "<h3>📈 Analizando tablas...</h3>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tablas as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $total = $stmt->fetch()['total'];
            echo "<p>📋 $tabla: $total registros</p>";
        } catch (Exception $e) {
            echo "<p style='color: #dc3545;'>❌ Error analizando $tabla: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. OPTIMIZAR TABLAS
    echo "<h3>⚡ Optimizando tablas...</h3>";
    
    foreach ($tablas as $tabla) {
        try {
            $pdo->exec("OPTIMIZE TABLE `$tabla`");
            echo "<p style='color: #28a745;'>✅ Tabla $tabla optimizada</p>";
        } catch (Exception $e) {
            echo "<p style='color: #dc3545;'>❌ Error optimizando $tabla: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. VERIFICAR RENDIMIENTO
    echo "<h3>🚀 Verificando rendimiento...</h3>";
    
    // Medir tiempo de consulta de inventario
    $inicio = microtime(true);
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM productos p
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_entradas 
            FROM productos_entradas 
            GROUP BY producto_id
        ) pe ON p.id = pe.producto_id
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_salidas 
            FROM productos_salidas 
            GROUP BY producto_id
        ) ps ON p.id = ps.producto_id
    ");
    $total_productos = $stmt->fetch()['total'];
    
    $fin = microtime(true);
    $tiempo = round(($fin - $inicio) * 1000, 2);
    
    echo "<p>⏱️ Consulta de inventario: $tiempo ms</p>";
    echo "<p>📊 Total productos: $total_productos</p>";
    
    // 5. RECOMENDACIONES
    echo "<h3>💡 Recomendaciones</h3>";
    
    if ($tiempo > 1000) {
        echo "<p style='color: #dc3545;'>⚠️ La consulta es lenta (>1s). Considera implementar cache.</p>";
    } elseif ($tiempo > 500) {
        echo "<p style='color: #ffc107;'>⚠️ La consulta es moderada (>500ms). Considera optimizar más.</p>";
    } else {
        echo "<p style='color: #28a745;'>✅ La consulta es rápida (<500ms). Excelente rendimiento.</p>";
    }
    
    if ($total_productos > 1000) {
        echo "<p style='color: #dc3545;'>⚠️ Muchos productos. Implementa paginación obligatoria.</p>";
    } elseif ($total_productos > 500) {
        echo "<p style='color: #ffc107;'>⚠️ Cantidad moderada. Considera paginación.</p>";
    } else {
        echo "<p style='color: #28a745;'>✅ Cantidad manejable. Paginación opcional.</p>";
    }
    
    // 6. ESTADÍSTICAS FINALES
    echo "<h3>📊 Estadísticas Finales</h3>";
    
    $stmt = $pdo->query("SHOW TABLE STATUS");
    $tablas_info = $stmt->fetchAll();
    
    $total_tamano = 0;
    $total_registros = 0;
    
    foreach ($tablas_info as $tabla) {
        $total_tamano += $tabla['Data_length'] + $tabla['Index_length'];
        $total_registros += $tabla['Rows'];
    }
    
    $tamano_mb = round($total_tamano / 1024 / 1024, 2);
    
    echo "<p>💾 Tamaño total de BD: $tamano_mb MB</p>";
    echo "<p>📊 Total registros: " . number_format($total_registros) . "</p>";
    echo "<p>🔧 Índices creados: $indices_creados</p>";
    echo "<p>⚡ Tiempo consulta: $tiempo ms</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin-top: 20px;'>";
    echo "<h4 style='color: #155724; margin-top: 0;'>✅ Optimización Completada</h4>";
    echo "<p style='color: #155724; margin-bottom: 0;'>La base de datos ha sido optimizada exitosamente. El rendimiento debería mejorar significativamente.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; margin-top: 20px;'>";
    echo "<h4 style='color: #721c24; margin-top: 0;'>❌ Error en Optimización</h4>";
    echo "<p style='color: #721c24; margin-bottom: 0;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";
?> 