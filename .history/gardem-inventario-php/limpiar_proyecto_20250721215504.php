<?php
// ========================================
// SCRIPT DE LIMPIEZA DEL PROYECTO
// ========================================

echo "<h2>🧹 Limpiando Proyecto Gardem</h2>";

// Archivos y carpetas que SÍ se usan
$archivos_utiles = [
    // Archivos principales
    'index.php',
    'login.php',
    'config/database.php',
    'config/database_simple.php',
    
    // Vistas principales
    'vista/inventario.php',
    'vista/entradas.php',
    'vista/salidas.php',
    'vista/menu_principal.php',
    
    // Controladores principales
    'controlador/ControladorAuth.php',
    'controlador/ControladorInventario.php',
    'controlador/ControladorEntradas.php',
    'controlador/ControladorSalidas.php',
    
    // Procesadores
    'procesadores/procesar_entrada.php',
    'procesadores/procesar_salida.php',
    
    // Editores
    'editores/editar_entrada.php',
    'editores/editar_salida.php',
    
    // Herramientas útiles
    'herramientas/verificar_foreign_keys.php',
    'herramientas/debug_salidas.php',
    'herramientas/fix_auto_increment.php',
    
    // SQL útil
    'sql/base_datos_simple.sql',
    'sql/README_SIMPLE.md',
    'sql/corregir_foreign_keys_xampp.sql',
    
    // Documentación
    'RUTAS.md',
    'ESTRUCTURA.md',
    'README.md'
];

// Archivos y carpetas a ELIMINAR
$archivos_eliminar = [
    // Carpetas duplicadas o innecesarias
    'gardem-inventario-php/',
    'gardem-inventario-limpio/',
    '.history/',
    
    // Archivos duplicados o innecesarios
    'login_simple.php',
    'test_conexion.php',
    'verificar_tablas.php',
    'verificar_bodegas.php',
    'fix_inventario_stock_minimo.php',
    
    // Migraciones innecesarias
    'migraciones/migrar_entradas.php',
    'migraciones/migrar_entradas_cli.php',
    
    // SQL innecesario
    'sql/agregar_foreign_keys.sql',
    'sql/arreglar_foreign_keys.sql',
    'sql/arreglar_salidas.sql',
    'sql/verificar_estructura_salidas.sql',
    'sql/verificar_inventario_bodega.sql',
    'sql/fix_auto_increment.sql',
    'sql/database.sql',
    'sql/database_production.sql',
    'sql/corregir_foreign_keys_completo.sql',
    'sql/README.md'
];

echo "<h3>📋 Archivos que se mantienen:</h3>";
echo "<ul>";
foreach ($archivos_utiles as $archivo) {
    if (file_exists($archivo)) {
        echo "<li>✅ $archivo</li>";
    } else {
        echo "<li>❌ $archivo (no existe)</li>";
    }
}
echo "</ul>";

echo "<h3>🗑️ Archivos a eliminar:</h3>";
echo "<ul>";

$eliminados = 0;
foreach ($archivos_eliminar as $archivo) {
    if (file_exists($archivo)) {
        if (is_dir($archivo)) {
            // Eliminar carpeta
            $resultado = eliminarCarpeta($archivo);
            if ($resultado) {
                echo "<li>✅ Eliminada carpeta: $archivo</li>";
                $eliminados++;
            } else {
                echo "<li>❌ Error eliminando carpeta: $archivo</li>";
            }
        } else {
            // Eliminar archivo
            if (unlink($archivo)) {
                echo "<li>✅ Eliminado archivo: $archivo</li>";
                $eliminados++;
            } else {
                echo "<li>❌ Error eliminando archivo: $archivo</li>";
            }
        }
    } else {
        echo "<li>⚠️ No existe: $archivo</li>";
    }
}
echo "</ul>";

echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Archivos eliminados: $eliminados</p>";

// Función para eliminar carpetas recursivamente
function eliminarCarpeta($carpeta) {
    if (!is_dir($carpeta)) {
        return false;
    }
    
    $archivos = scandir($carpeta);
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            $ruta = $carpeta . '/' . $archivo;
            if (is_dir($ruta)) {
                eliminarCarpeta($ruta);
            } else {
                unlink($ruta);
            }
        }
    }
    
    return rmdir($carpeta);
}

// Verificar estructura final
echo "<h3>📁 Estructura final del proyecto:</h3>";
echo "<pre>";
mostrarEstructura('.');
echo "</pre>";

function mostrarEstructura($directorio, $nivel = 0) {
    $indent = str_repeat('  ', $nivel);
    $archivos = scandir($directorio);
    
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..') {
            $ruta = $directorio . '/' . $archivo;
            if (is_dir($ruta)) {
                echo $indent . "📁 $archivo/\n";
                mostrarEstructura($ruta, $nivel + 1);
            } else {
                echo $indent . "📄 $archivo\n";
            }
        }
    }
}

echo "<h3>🎉 ¡Proyecto limpiado exitosamente!</h3>";
echo "<p>El proyecto ahora está <strong>LIMPIO y ORGANIZADO</strong>.</p>";
echo "<p>Solo quedan los archivos <strong>NECESARIOS</strong> para el funcionamiento del sistema.</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
ul { background: white; padding: 15px; border-radius: 5px; }
li { margin: 5px 0; }
pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style> 