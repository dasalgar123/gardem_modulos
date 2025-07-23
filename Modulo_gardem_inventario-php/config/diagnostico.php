<?php
require_once 'database.php';

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

// Obtener estadísticas
echo "<h3>📊 Estadísticas actuales:</h3>";
$stats = obtenerEstadisticas();
foreach ($stats as $key => $value) {
    if ($key !== 'error') {
        echo "<p><strong>$key:</strong> $value</p>";
    }
}

echo "<hr>";
echo "<p><a href='../index.php'>Volver al Dashboard</a></p>";
?> 