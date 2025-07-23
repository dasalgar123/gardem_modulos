<?php
require_once 'config/database.php';

echo "<h2>🔍 Verificación de Base de Datos</h2>";

// Verificar estado de la base de datos
$estado = verificarBaseDatos();
echo "<h3>Estado: " . $estado['status'] . "</h3>";
echo "<p>" . $estado['mensaje'] . "</p>";

if ($estado['status'] === 'incompleta') {
    echo "<h3>📋 Tablas faltantes:</h3>";
    echo "<ul>";
    foreach ($estado['tablas_faltantes'] as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
}

// Verificar tablas existentes
echo "<h3>📊 Tablas existentes:</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Verificar datos en tablas principales
echo "<h3>📈 Datos en tablas principales:</h3>";

$tablas_verificar = ['productos', 'categorias', 'proveedores', 'entradas', 'salidas', 'inventario'];

foreach ($tablas_verificar as $tabla) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
        $count = $stmt->fetch()['total'];
        echo "<p><strong>$tabla:</strong> $count registros</p>";
    } catch (Exception $e) {
        echo "<p><strong>$tabla:</strong> Error - " . $e->getMessage() . "</p>";
    }
}

// Obtener estadísticas
echo "<h3>📊 Estadísticas actuales:</h3>";
$stats = obtenerEstadisticas();
foreach ($stats as $key => $value) {
    if ($key !== 'error') {
        echo "<p><strong>$key:</strong> $value</p>";
    }
}

if (isset($stats['error'])) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $stats['error'] . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Crear datos de prueba</h3>";
echo "<a href='crear_datos_prueba.php' class='btn btn-primary'>Crear Datos de Prueba</a>";
?> 