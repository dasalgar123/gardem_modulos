<?php
/**
 * Script para crear las tablas de ventas automáticamente
 * Este archivo debe ejecutarse una sola vez para inicializar la base de datos
 */

// Incluir configuración de base de datos
require_once __DIR__ . '/database.php';

try {
    echo "🔧 Inicializando tablas de ventas...\n";
    
    // Leer el archivo SQL
    $sql_file = __DIR__ . '/../sql/crear_tablas_ventas.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("Archivo SQL no encontrado: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Dividir el SQL en comandos individuales
    $commands = explode(';', $sql_content);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($commands as $command) {
        $command = trim($command);
        
        // Ignorar líneas vacías y comentarios
        if (empty($command) || strpos($command, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($command);
            $success_count++;
            echo "✅ Comando ejecutado correctamente\n";
        } catch (PDOException $e) {
            // Ignorar errores de "table already exists"
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ️  Tabla ya existe, continuando...\n";
                $success_count++;
            } else {
                echo "❌ Error: " . $e->getMessage() . "\n";
                $error_count++;
            }
        }
    }
    
    echo "\n🎉 Proceso completado:\n";
    echo "✅ Comandos exitosos: $success_count\n";
    echo "❌ Errores: $error_count\n";
    
    if ($error_count === 0) {
        echo "\n✅ Todas las tablas de ventas han sido creadas correctamente.\n";
        echo "✅ El sistema de ventas está listo para usar.\n";
    } else {
        echo "\n⚠️  Algunos errores ocurrieron. Revisa los mensajes anteriores.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    exit(1);
}
?> 