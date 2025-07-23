<?php
// Configuración simple para conectar a tu base de datos
$host = 'localhost';
$dbname = 'gardelcatalogo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Crear tablas si no existen
$sql = "
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2),
    categoria VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    stock_maximo INT DEFAULT 1000,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nombre VARCHAR(100) NOT NULL,
    cliente_telefono VARCHAR(20),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) DEFAULT 0,
    estado ENUM('activa', 'cancelada', 'completada') DEFAULT 'activa'
);

CREATE TABLE IF NOT EXISTS ventas_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente_entrega', 'entregado') DEFAULT 'pendiente_entrega',
    almacenista_id INT,
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (almacenista_id) REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('entrada', 'salida', 'ajuste_manual', 'entrega') NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);
";

$queries = explode(';', $sql);
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        $pdo->exec($query);
    }
}

// Insertar datos de prueba si no existen
$stmt = $pdo->query("SELECT COUNT(*) FROM productos");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria) VALUES ('Boxer Clásico', 'BOX001', 'Boxer de algodón clásico', 15.99, 'Ropa Interior')");
    $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria) VALUES ('Boxer Premium', 'BOX002', 'Boxer premium de alta calidad', 25.99, 'Ropa Interior')");
    $pdo->exec("INSERT INTO inventario (producto_id, stock_actual, stock_minimo, stock_maximo) VALUES (1, 50, 10, 100)");
    $pdo->exec("INSERT INTO inventario (producto_id, stock_actual, stock_minimo, stock_maximo) VALUES (2, 30, 5, 80)");
}
?> 