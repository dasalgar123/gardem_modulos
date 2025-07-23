<?php
// ========================================
// CREAR TABLAS FALTANTES
// ========================================

// Configuración
$host = 'localhost';
$dbname = 'gardelcatalogo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Creando tablas faltantes...</h2>";
    
    // Crear tabla entradas si no existe
    $sql_entradas = "
    CREATE TABLE IF NOT EXISTS entradas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT,
        cantidad INT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        motivo VARCHAR(100),
        INDEX (producto_id)
    )";
    
    $pdo->exec($sql_entradas);
    echo "✅ Tabla 'entradas' creada/verificada<br>";
    
    // Crear tabla salidas si no existe
    $sql_salidas = "
    CREATE TABLE IF NOT EXISTS salidas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT,
        cantidad INT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        motivo VARCHAR(100),
        INDEX (producto_id)
    )";
    
    $pdo->exec($sql_salidas);
    echo "✅ Tabla 'salidas' creada/verificada<br>";
    
    // Crear tabla inventario si no existe
    $sql_inventario = "
    CREATE TABLE IF NOT EXISTS inventario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT,
        stock_actual INT DEFAULT 0,
        stock_minimo INT DEFAULT 0,
        INDEX (producto_id)
    )";
    
    $pdo->exec($sql_inventario);
    echo "✅ Tabla 'inventario' creada/verificada<br>";
    
    // Crear tabla usuarios si no existe
    $sql_usuarios = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        correo VARCHAR(100) UNIQUE NOT NULL,
        contraseña VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'almacenista') DEFAULT 'almacenista',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_usuarios);
    echo "✅ Tabla 'usuarios' creada/verificada<br>";
    
    // Crear usuario admin si no existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
    $stmt->execute(['admin@gardem.com']);
    
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['Administrador', 'admin@gardem.com', $hash]);
        echo "✅ Usuario admin creado<br>";
    } else {
        echo "✅ Usuario admin ya existe<br>";
    }
    
    echo "<h3>🎉 ¡Tablas creadas exitosamente!</h3>";
    echo "<p><a href='index.php'>Ir al sistema</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
p { background: white; padding: 15px; border-radius: 5px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style> 