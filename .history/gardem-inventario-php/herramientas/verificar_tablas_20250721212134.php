<?php
require_once '../config/database.php';

// Configuración directa para XAMPP local
$DB_HOST = 'localhost';
$DB_NAME = 'gardelcatalogo';
$DB_USER = 'root';
$DB_PASS = '';

try {
    // Conectar a la base de datos
    $dsn = 'mysql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Verificación de Tablas ===\n";
    
    // Verificar tablas principales
    $tablas = ['productos_entradas', 'entradas', 'entrada_detalles', 'productos', 'proveedores', 'bodegas'];
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tabla}'");
        $existe = $stmt->rowCount() > 0;
        
        if ($existe) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM {$tabla}");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "✅ {$tabla}: {$count} registros\n";
        } else {
            echo "❌ {$tabla}: No existe\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 