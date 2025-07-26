<?php
require_once 'config/database.php';

echo "<h2>Verificando estructura de tablas...</h2>";

// Verificar estructura de inventario_bodega
echo "<h3>Estructura de tabla inventario_bodega:</h3>";
$stmt = $pdo->query("DESCRIBE inventario_bodega");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
foreach ($columnas as $columna) {
    echo "<tr>";
    echo "<td>{$columna['Field']}</td>";
    echo "<td>{$columna['Type']}</td>";
    echo "<td>{$columna['Null']}</td>";
    echo "<td>{$columna['Key']}</td>";
    echo "<td>{$columna['Default']}</td>";
    echo "<td>{$columna['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Verificar estructura de productos
echo "<h3>Estructura de tabla productos:</h3>";
$stmt = $pdo->query("DESCRIBE productos");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
foreach ($columnas as $columna) {
    echo "<tr>";
    echo "<td>{$columna['Field']}</td>";
    echo "<td>{$columna['Type']}</td>";
    echo "<td>{$columna['Null']}</td>";
    echo "<td>{$columna['Key']}</td>";
    echo "<td>{$columna['Default']}</td>";
    echo "<td>{$columna['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Mostrar algunos datos de ejemplo
echo "<h3>Datos de ejemplo en inventario_bodega:</h3>";
$stmt = $pdo->query("SELECT * FROM inventario_bodega LIMIT 3");
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($datos)) {
    echo "<p>No hay datos en inventario_bodega</p>";
} else {
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
}
?> 