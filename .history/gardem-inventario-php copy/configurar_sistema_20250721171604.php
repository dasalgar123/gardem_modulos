<?php
/**
 * Script de configuración rápida para el Sistema de Inventario
 * Adaptado a la base de datos existente del usuario
 */

// Configuración de conexión
$host = 'localhost';
$dbname = 'gardelcatalogo'; // Tu base de datos real
$username = 'root';
$password = '';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Configuración del Sistema - GARDEM</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .config-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); padding: 30px; max-width: 800px; margin: 0 auto; }
        .step { margin-bottom: 30px; padding: 20px; border-radius: 10px; border-left: 5px solid #27ae60; }
        .step.success { background: #d4edda; border-left-color: #28a745; }
        .step.error { background: #f8d7da; border-left-color: #dc3545; }
        .step.info { background: #d1ecf1; border-left-color: #17a2b8; }
        .btn-next { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); border: none; padding: 12px 30px; border-radius: 8px; color: white; font-weight: 600; }
    </style>
</head>
<body>
    <div class='config-container'>
        <div class='text-center mb-4'>
            <i class='fas fa-cog fa-3x text-primary mb-3'></i>
            <h2>Configuración del Sistema de Inventario</h2>
            <p class='text-muted'>GARDEM - Módulo Almacenista</p>
        </div>";

try {
    // Paso 1: Conectar a la base de datos
    echo "<div class='step info'>
        <h5><i class='fas fa-database'></i> Paso 1: Conectando a la base de datos</h5>
        <p>Intentando conectar a: <strong>$dbname</strong></p>";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle'></i> Conexión exitosa a la base de datos '$dbname'
    </div></div>";
    
    // Paso 2: Verificar tabla de usuarios
    echo "<div class='step info'>
        <h5><i class='fas fa-users'></i> Paso 2: Verificando tabla de usuarios</h5>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuario'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-success'>
            <i class='fas fa-check-circle'></i> Tabla 'usuario' encontrada
        </div>";
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE usuario");
        $columnas = $stmt->fetchAll();
        $columnas_nombres = array_column($columnas, 'Field');
        
        echo "<p><strong>Columnas encontradas:</strong> " . implode(', ', $columnas_nombres) . "</p>";
        
        // Verificar usuarios existentes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario");
        $total_usuarios = $stmt->fetch()['total'];
        echo "<p><strong>Usuarios registrados:</strong> $total_usuarios</p>";
        
    } else {
        echo "<div class='alert alert-danger'>
            <i class='fas fa-exclamation-triangle'></i> Tabla 'usuario' no encontrada
        </div></div>";
        throw new Exception("La tabla 'usuario' no existe en tu base de datos");
    }
    echo "</div>";
    
    // Paso 3: Crear tablas faltantes
    echo "<div class='step info'>
        <h5><i class='fas fa-table'></i> Paso 3: Creando tablas del sistema</h5>";
    
    $tablas_creadas = [];
    
    // Tabla productos
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
    $pdo->exec($sql);
    $tablas_creadas[] = 'productos';
    
    // Tabla inventario
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
    $pdo->exec($sql);
    $tablas_creadas[] = 'inventario';
    
    // Tabla ventas
    $sql = "
    CREATE TABLE IF NOT EXISTS ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_nombre VARCHAR(100) NOT NULL,
        cliente_telefono VARCHAR(20),
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) DEFAULT 0,
        estado ENUM('activa', 'cancelada', 'completada') DEFAULT 'activa'
    )";
    $pdo->exec($sql);
    $tablas_creadas[] = 'ventas';
    
    // Tabla ventas_productos
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
    $pdo->exec($sql);
    $tablas_creadas[] = 'ventas_productos';
    
    // Tabla movimientos
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
    $pdo->exec($sql);
    $tablas_creadas[] = 'movimientos';
    
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle'></i> Tablas creadas: " . implode(', ', $tablas_creadas) . "
    </div></div>";
    
    // Paso 4: Insertar datos de prueba
    echo "<div class='step info'>
        <h5><i class='fas fa-seedling'></i> Paso 4: Insertando datos de prueba</h5>";
    
    // Verificar si ya existen productos
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    if ($stmt->fetchColumn() == 0) {
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
        
        echo "<div class='alert alert-success'>
            <i class='fas fa-check-circle'></i> Datos de prueba insertados correctamente
        </div>";
    } else {
        echo "<div class='alert alert-info'>
            <i class='fas fa-info-circle'></i> Los datos de prueba ya existen
        </div>";
    }
    echo "</div>";
    
    // Paso 5: Verificación final
    echo "<div class='step success'>
        <h5><i class='fas fa-check-double'></i> Paso 5: Verificación final</h5>";
    
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $stats['productos'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventario");
    $stats['inventario'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas");
    $stats['ventas'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM movimientos");
    $stats['movimientos'] = $stmt->fetchColumn();
    
    echo "<div class='row'>
        <div class='col-md-3'>
            <div class='text-center'>
                <h3 class='text-primary'>$stats[productos]</h3>
                <small>Productos</small>
            </div>
        </div>
        <div class='col-md-3'>
            <div class='text-center'>
                <h3 class='text-success'>$stats[inventario]</h3>
                <small>Inventario</small>
            </div>
        </div>
        <div class='col-md-3'>
            <div class='text-center'>
                <h3 class='text-info'>$stats[ventas]</h3>
                <small>Ventas</small>
            </div>
        </div>
        <div class='col-md-3'>
            <div class='text-center'>
                <h3 class='text-warning'>$stats[movimientos]</h3>
                <small>Movimientos</small>
            </div>
        </div>
    </div></div>";
    
    // Éxito
    echo "<div class='step success'>
        <h5><i class='fas fa-trophy'></i> ¡Configuración Completada!</h5>
        <p>El sistema está listo para usar con tu base de datos existente.</p>
        
        <div class='alert alert-info'>
            <h6><i class='fas fa-key'></i> Credenciales de acceso:</h6>
            <p>Usa cualquier usuario de tu tabla 'usuario' con rol 'administrador' o 'almacenista'</p>
        </div>
        
        <div class='text-center mt-4'>
            <a href='login_existente.php' class='btn btn-next btn-lg'>
                <i class='fas fa-sign-in-alt'></i> Ir al Login
            </a>
        </div>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>
        <h5><i class='fas fa-exclamation-triangle'></i> Error de Configuración</h5>
        <div class='alert alert-danger'>
            <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
        </div>
        
        <div class='alert alert-warning'>
            <h6>Posibles soluciones:</h6>
            <ul>
                <li>Verifica que la base de datos '$dbname' existe</li>
                <li>Verifica las credenciales de MySQL</li>
                <li>Asegúrate de que MySQL esté ejecutándose</li>
                <li>Edita el archivo 'config/database_existing.php' con el nombre correcto de tu BD</li>
            </ul>
        </div>
    </div>";
}

echo "</div></body></html>";
?> 