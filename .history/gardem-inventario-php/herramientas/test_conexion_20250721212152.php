<?php
require_once '../config/database.php';

// Prueba simple de conexión
$host = 'localhost';
$dbname = 'gardelcatalogo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ CONEXIÓN EXITOSA a $dbname";
    
    // Probar consulta
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuario");
    $total = $stmt->fetchColumn();
    echo "<br>✅ Usuarios en la tabla: $total";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?> 