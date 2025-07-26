<?php
/**
 * Script para actualizar la tabla productos_ventas
 * Agrega las columnas faltantes: productos y total
 */

// Incluir configuración de base de datos
require_once __DIR__ . '/database.php';

try {
    echo "🔧 Actualizando tabla productos_ventas...\n";
    
    // Verificar si las columnas ya existen
    $stmt = $pdo->query("DESCRIBE productos_ventas");
    $columnas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Columnas actuales: " . implode(', ', $columnas_existentes) . "\n";
    
    $cambios_realizados = 0;
    
    // Agregar columna 'productos' si no existe
    if (!in_array('productos', $columnas_existentes)) {
        echo "➕ Agregando columna 'productos'...\n";
        $pdo->exec("ALTER TABLE productos_ventas ADD COLUMN productos TEXT NOT NULL COMMENT 'Descripción de productos vendidos' AFTER factura");
        echo "✅ Columna 'productos' agregada correctamente\n";
        $cambios_realizados++;
    } else {
        echo "ℹ️  Columna 'productos' ya existe\n";
    }
    
    // Agregar columna 'total' si no existe
    if (!in_array('total', $columnas_existentes)) {
        echo "➕ Agregando columna 'total'...\n";
        $pdo->exec("ALTER TABLE productos_ventas ADD COLUMN total DECIMAL(10,2) NOT NULL AFTER productos");
        echo "✅ Columna 'total' agregada correctamente\n";
        $cambios_realizados++;
    } else {
        echo "ℹ️  Columna 'total' ya existe\n";
    }
    
    // Agregar columna 'usuario_id' si no existe
    if (!in_array('usuario_id', $columnas_existentes)) {
        echo "➕ Agregando columna 'usuario_id'...\n";
        $pdo->exec("ALTER TABLE productos_ventas ADD COLUMN usuario_id INT(11) DEFAULT 1 AFTER total");
        echo "✅ Columna 'usuario_id' agregada correctamente\n";
        $cambios_realizados++;
    } else {
        echo "ℹ️  Columna 'usuario_id' ya existe\n";
    }
    
    // Verificar estructura final
    $stmt = $pdo->query("DESCRIBE productos_ventas");
    $columnas_finales = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 Columnas finales: " . implode(', ', $columnas_finales) . "\n";
    
    if ($cambios_realizados > 0) {
        echo "\n🎉 Actualización completada:\n";
        echo "✅ Cambios realizados: $cambios_realizados\n";
        echo "✅ La tabla productos_ventas está lista para usar\n";
    } else {
        echo "\nℹ️  No se requirieron cambios. La tabla ya tiene la estructura correcta.\n";
    }
    
    // Verificar si hay datos existentes que necesiten actualización
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_ventas");
    $total_registros = $stmt->fetch()['total'];
    
    if ($total_registros > 0) {
        echo "\n⚠️  La tabla tiene $total_registros registros existentes.\n";
        echo "💡 Si hay registros con valores NULL en las nuevas columnas, considera actualizarlos.\n";
    } else {
        echo "\n✅ La tabla está vacía, lista para nuevas ventas.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?> 