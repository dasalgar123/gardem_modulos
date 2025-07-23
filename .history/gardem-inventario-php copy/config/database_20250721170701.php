<?php
// Configuración de base de datos para Sistema de Almacenista

// Configuración de conexión
$host = 'localhost';
$dbname = 'gardem_inventario';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la base de datos no existe, mostrar instrucciones de configuración
    if ($e->getCode() == 1049) {
        echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 2px solid #e74c3c; border-radius: 10px; background-color: #fdf2f2;">';
        echo '<h2 style="color: #e74c3c; text-align: center;">⚠️ Base de Datos No Encontrada</h2>';
        echo '<p style="font-size: 16px; line-height: 1.6;">La base de datos <strong>gardem_inventario</strong> no existe. Para configurar el sistema:</p>';
        echo '<ol style="font-size: 16px; line-height: 1.8;">';
        echo '<li><strong>Ejecuta el script de configuración:</strong><br>';
        echo '<code style="background: #f8f9fa; padding: 5px 10px; border-radius: 5px; font-family: monospace;">http://localhost/gardem-inventario-php/setup_database.php</code></li>';
        echo '<li><strong>O ejecuta manualmente en MySQL:</strong><br>';
        echo '<code style="background: #f8f9fa; padding: 5px 10px; border-radius: 5px; font-family: monospace;">CREATE DATABASE gardem_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></li>';
        echo '<li><strong>Luego recarga esta página</strong></li>';
        echo '</ol>';
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a href="setup_database.php" style="background: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">🔧 Configurar Base de Datos</a>';
        echo '</div>';
        echo '<div style="margin-top: 20px; padding: 15px; background: #e8f4fd; border-radius: 5px;">';
        echo '<h4 style="color: #2980b9; margin-top: 0;">📋 Credenciales por defecto:</h4>';
        echo '<p style="margin: 5px 0;"><strong>Email:</strong> almacenista@test.com</p>';
        echo '<p style="margin: 5px 0;"><strong>Password:</strong> almacenista123</p>';
        echo '</div>';
        echo '</div>';
        exit();
    } else {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para crear las tablas si no existen
function crearTablasSiNoExisten($pdo) {
    $sql = "
    -- Tabla de usuarios (almacenistas)
    CREATE TABLE IF NOT EXISTS usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol ENUM('almacenista', 'vendedor', 'admin') DEFAULT 'almacenista',
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Tabla de bodegas
    CREATE TABLE IF NOT EXISTS bodega (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        direccion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Tabla de categorías
    CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        activo BOOLEAN DEFAULT TRUE
    );
    
    -- Tabla de productos
    CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(200) NOT NULL,
        codigo VARCHAR(50) UNIQUE NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2),
        imagen VARCHAR(255),
        categoria_id INT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    );
    
    -- Tabla de inventario por bodega
    CREATE TABLE IF NOT EXISTS inventario_bodega (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT NOT NULL,
        bodega_id INT NOT NULL,
        stock_actual INT DEFAULT 0,
        stock_minimo INT DEFAULT 0,
        stock_maximo INT DEFAULT 1000,
        ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id),
        UNIQUE KEY unique_producto_bodega (producto_id, bodega_id)
    );
    
    -- Tabla de inventario en línea (sincronización)
    CREATE TABLE IF NOT EXISTS inventario_en_linea (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT NOT NULL,
        bodega_id INT NOT NULL,
        stock_actual INT DEFAULT 0,
        ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        sincronizado BOOLEAN DEFAULT FALSE,
        version INT DEFAULT 1,
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id),
        UNIQUE KEY unique_producto_bodega_linea (producto_id, bodega_id)
    );
    
    -- Tabla de movimientos de inventario
    CREATE TABLE IF NOT EXISTS movimientos_inventario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('entrada', 'salida', 'traslado', 'ajuste_manual', 'entrega', 'garantia', 'devolucion') NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        bodega_id INT,
        usuario_id INT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        observaciones TEXT,
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de clientes
    CREATE TABLE IF NOT EXISTS cliente (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        direccion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Tabla de proveedores
    CREATE TABLE IF NOT EXISTS proveedor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        direccion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    -- Tabla de ventas
    CREATE TABLE IF NOT EXISTS ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) DEFAULT 0,
        estado ENUM('activa', 'cancelada', 'completada') DEFAULT 'activa',
        FOREIGN KEY (cliente_id) REFERENCES cliente(id)
    );
    
    -- Tabla de productos en ventas
    CREATE TABLE IF NOT EXISTS ventas_productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venta_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        bodega_id INT,
        estado ENUM('pendiente_entrega', 'entregado') DEFAULT 'pendiente_entrega',
        fecha_entrega TIMESTAMP NULL,
        almacenista_id INT,
        FOREIGN KEY (venta_id) REFERENCES ventas(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id),
        FOREIGN KEY (almacenista_id) REFERENCES usuario(id)
    );
    
    -- Tabla de entradas (compras)
    CREATE TABLE IF NOT EXISTS entradas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        proveedor_id INT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) DEFAULT 0,
        usuario_id INT NOT NULL,
        observaciones TEXT,
        FOREIGN KEY (proveedor_id) REFERENCES proveedor(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de detalle de entradas
    CREATE TABLE IF NOT EXISTS entradas_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        entrada_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        bodega_id INT,
        FOREIGN KEY (entrada_id) REFERENCES entradas(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id)
    );
    
    -- Tabla de salidas
    CREATE TABLE IF NOT EXISTS salidas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) DEFAULT 0,
        usuario_id INT NOT NULL,
        observaciones TEXT,
        FOREIGN KEY (cliente_id) REFERENCES cliente(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de detalle de salidas
    CREATE TABLE IF NOT EXISTS salidas_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        salida_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        bodega_id INT,
        FOREIGN KEY (salida_id) REFERENCES salidas(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id)
    );
    
    -- Tabla de traslados
    CREATE TABLE IF NOT EXISTS traslados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bodega_origen_id INT NOT NULL,
        bodega_destino_id INT NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT NOT NULL,
        observaciones TEXT,
        FOREIGN KEY (bodega_origen_id) REFERENCES bodega(id),
        FOREIGN KEY (bodega_destino_id) REFERENCES bodega(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de detalle de traslados
    CREATE TABLE IF NOT EXISTS traslados_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        traslado_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        FOREIGN KEY (traslado_id) REFERENCES traslados(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    );
    
    -- Tabla de garantías
    CREATE TABLE IF NOT EXISTS garantias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('entrada', 'salida') NOT NULL,
        cliente_id INT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT NOT NULL,
        observaciones TEXT,
        FOREIGN KEY (cliente_id) REFERENCES cliente(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de detalle de garantías
    CREATE TABLE IF NOT EXISTS garantias_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        garantia_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2),
        bodega_id INT,
        FOREIGN KEY (garantia_id) REFERENCES garantias(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id)
    );
    
    -- Tabla de devoluciones
    CREATE TABLE IF NOT EXISTS devoluciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('entrada', 'salida') NOT NULL,
        cliente_id INT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT NOT NULL,
        observaciones TEXT,
        FOREIGN KEY (cliente_id) REFERENCES cliente(id),
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
    );
    
    -- Tabla de detalle de devoluciones
    CREATE TABLE IF NOT EXISTS devoluciones_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        devolucion_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2),
        bodega_id INT,
        FOREIGN KEY (devolucion_id) REFERENCES devoluciones(id),
        FOREIGN KEY (producto_id) REFERENCES productos(id),
        FOREIGN KEY (bodega_id) REFERENCES bodega(id)
    );
    ";
    
    // Ejecutar las consultas
    $queries = explode(';', $sql);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    
    // Insertar datos de prueba si las tablas están vacías
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuario");
    if ($stmt->fetchColumn() == 0) {
        // Insertar usuario almacenista de prueba
        $password_hash = password_hash('almacenista123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO usuario (nombre, email, password, rol) VALUES ('Almacenista Test', 'almacenista@test.com', '$password_hash', 'almacenista')");
        
        // Insertar bodega de prueba
        $pdo->exec("INSERT INTO bodega (nombre, direccion) VALUES ('Bodega Principal', 'Dirección de la bodega principal')");
        
        // Insertar categorías de prueba
        $pdo->exec("INSERT INTO categorias (nombre, descripcion) VALUES ('Ropa Interior', 'Productos de ropa interior')");
        $pdo->exec("INSERT INTO categorias (nombre, descripcion) VALUES ('Accesorios', 'Accesorios varios')");
        
        // Insertar productos de prueba
        $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria_id) VALUES ('Boxer Clásico', 'BOX001', 'Boxer de algodón clásico', 15.99, 1)");
        $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria_id) VALUES ('Boxer Premium', 'BOX002', 'Boxer premium de alta calidad', 25.99, 1)");
        
        // Insertar inventario de prueba
        $pdo->exec("INSERT INTO inventario_bodega (producto_id, bodega_id, stock_actual, stock_minimo, stock_maximo) VALUES (1, 1, 50, 10, 100)");
        $pdo->exec("INSERT INTO inventario_bodega (producto_id, bodega_id, stock_actual, stock_minimo, stock_maximo) VALUES (2, 1, 30, 5, 80)");
        
        // Insertar cliente de prueba
        $pdo->exec("INSERT INTO cliente (nombre, telefono, email, direccion) VALUES ('Cliente Test', '123456789', 'cliente@test.com', 'Dirección del cliente')");
    }
}

// Crear tablas si no existen
crearTablasSiNoExisten($pdo);
?> 