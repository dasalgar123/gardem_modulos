<?php
// Configuración de base de datos para Sistema de Almacenista
// Adaptada a la estructura existente del usuario

// Configuración de conexión
$host = 'localhost';
$dbname = 'gardelcatalogo'; // Tu base de datos real
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para verificar si las tablas necesarias existen
function verificarTablasExistentes($pdo) {
    $tablas_requeridas = [
        'usuario' => 'Tabla de usuarios existente',
        'productos' => 'Tabla de productos (crear si no existe)',
        'inventario' => 'Tabla de inventario (crear si no existe)',
        'ventas' => 'Tabla de ventas (crear si no existe)',
        'movimientos' => 'Tabla de movimientos (crear si no existe)'
    ];
    
    $tablas_faltantes = [];
    
    foreach ($tablas_requeridas as $tabla => $descripcion) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() == 0) {
            $tablas_faltantes[$tabla] = $descripcion;
        }
    }
    
    return $tablas_faltantes;
}

// Función para crear tablas faltantes
function crearTablasFaltantes($pdo, $tablas_faltantes) {
    foreach ($tablas_faltantes as $tabla => $descripcion) {
        switch ($tabla) {
            case 'productos':
                $sql = "
                CREATE TABLE IF NOT EXISTS productos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(200) NOT NULL,
                    codigo VARCHAR(50) UNIQUE NOT NULL,
                    descripcion TEXT,
                    precio DECIMAL(10,2),
                    imagen VARCHAR(255),
                    categoria VARCHAR(100),
                    activo BOOLEAN DEFAULT TRUE,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                break;
                
            case 'inventario':
                $sql = "
                CREATE TABLE IF NOT EXISTS inventario (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    producto_id INT NOT NULL,
                    stock_actual INT DEFAULT 0,
                    stock_minimo INT DEFAULT 0,
                    stock_maximo INT DEFAULT 1000,
                    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (producto_id) REFERENCES productos(id)
                )";
                break;
                
            case 'ventas':
                $sql = "
                CREATE TABLE IF NOT EXISTS ventas (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cliente_nombre VARCHAR(100) NOT NULL,
                    cliente_telefono VARCHAR(20),
                    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    total DECIMAL(10,2) DEFAULT 0,
                    estado ENUM('activa', 'cancelada', 'completada') DEFAULT 'activa'
                )";
                break;
                
            case 'ventas_productos':
                $sql = "
                CREATE TABLE IF NOT EXISTS ventas_productos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    venta_id INT NOT NULL,
                    producto_id INT NOT NULL,
                    cantidad INT NOT NULL,
                    precio_unitario DECIMAL(10,2) NOT NULL,
                    estado ENUM('pendiente_entrega', 'entregado') DEFAULT 'pendiente_entrega',
                    fecha_entrega TIMESTAMP NULL,
                    almacenista_id INT,
                    FOREIGN KEY (venta_id) REFERENCES ventas(id),
                    FOREIGN KEY (producto_id) REFERENCES productos(id),
                    FOREIGN KEY (almacenista_id) REFERENCES usuario(id)
                )";
                break;
                
            case 'movimientos':
                $sql = "
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
                )";
                break;
        }
        
        if (isset($sql)) {
            $pdo->exec($sql);
            echo "✅ Tabla '$tabla' creada correctamente\n";
        }
    }
}

// Función para insertar datos de prueba
function insertarDatosPrueba($pdo) {
    // Verificar si ya existen productos
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    if ($stmt->fetchColumn() > 0) {
        return; // Ya existen datos
    }
    
    // Insertar productos de prueba
    $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria) VALUES ('Boxer Clásico', 'BOX001', 'Boxer de algodón clásico', 15.99, 'Ropa Interior')");
    $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria) VALUES ('Boxer Premium', 'BOX002', 'Boxer premium de alta calidad', 25.99, 'Ropa Interior')");
    $pdo->exec("INSERT INTO productos (nombre, codigo, descripcion, precio, categoria) VALUES ('Boxer Deportivo', 'BOX003', 'Boxer deportivo con tecnología de secado rápido', 19.99, 'Ropa Interior')");
    
    // Insertar inventario de prueba
    $pdo->exec("INSERT INTO inventario (producto_id, stock_actual, stock_minimo, stock_maximo) VALUES (1, 50, 10, 100)");
    $pdo->exec("INSERT INTO inventario (producto_id, stock_actual, stock_minimo, stock_maximo) VALUES (2, 30, 5, 80)");
    $pdo->exec("INSERT INTO inventario (producto_id, stock_actual, stock_minimo, stock_maximo) VALUES (3, 25, 8, 60)");
    
    // Insertar venta de prueba
    $pdo->exec("INSERT INTO ventas (cliente_nombre, cliente_telefono, total) VALUES ('Cliente Test', '123456789', 31.98)");
    $pdo->exec("INSERT INTO ventas_productos (venta_id, producto_id, cantidad, precio_unitario) VALUES (1, 1, 2, 15.99)");
    
    echo "✅ Datos de prueba insertados correctamente\n";
}

// Verificar y crear tablas faltantes
$tablas_faltantes = verificarTablasExistentes($pdo);
if (!empty($tablas_faltantes)) {
    echo "📋 Creando tablas faltantes...\n";
    crearTablasFaltantes($pdo, $tablas_faltantes);
    insertarDatosPrueba($pdo);
}

// Función para autenticar usuario con la estructura existente
function autenticarUsuario($pdo, $correo, $contraseña) {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND contraseña = ?");
    $stmt->execute([$correo, $contraseña]);
    return $stmt->fetch();
}

// Función para obtener usuario por ID
function obtenerUsuario($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para verificar si el usuario es almacenista
function esAlmacenista($usuario) {
    return $usuario && ($usuario['rol'] === 'almacenista' || $usuario['rol'] === 'administrador');
}
?> 