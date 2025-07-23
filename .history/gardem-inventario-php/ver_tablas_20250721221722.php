<?php
// Ver qué tablas tienes realmente
$host = 'localhost';
$dbname = 'gardelcatalogo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📊 Tablas que tienes en tu base de datos:</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        echo "<p>❌ No hay tablas en tu base de datos</p>";
    } else {
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>✅ $tabla</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>🔍 Ver estructura de una tabla:</h3>";
    echo "<form method='post'>";
    echo "<select name='tabla'>";
    foreach ($tablas as $tabla) {
        echo "<option value='$tabla'>$tabla</option>";
    }
    echo "</select>";
    echo "<input type='submit' value='Ver estructura'>";
    echo "</form>";
    
    if (isset($_POST['tabla'])) {
        $tabla = $_POST['tabla'];
        echo "<h3>📋 Estructura de la tabla: $tabla</h3>";
        
        $stmt = $pdo->query("DESCRIBE $tabla");
        $columnas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th></tr>";
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>{$columna['Field']}</td>";
            echo "<td>{$columna['Type']}</td>";
            echo "<td>{$columna['Null']}</td>";
            echo "<td>{$columna['Key']}</td>";
            echo "<td>{$columna['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
ul { background: white; padding: 15px; border-radius: 5px; }
table { background: white; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f0f0f0; }
</style> 