<?php
// ========================================
// CONFIGURACIÓN PRINCIPAL - Base de Datos EXISTENTE
// ========================================

// Configuración de la base de datos EXISTENTE
$host = 'localhost';
$dbname = 'gardelcatalogo';  // TU base de datos existente
$username = 'root';
$password = '';

try {
    // Crear conexión PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Configurar zona horaria
    $pdo->exec("SET time_zone = '-05:00'");
    
} catch (PDOException $e) {
    // Si no existe la base de datos, mostrar instrucciones
    if ($e->getCode() == 1049) {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6;'>
            <h2 style='color: #dc3545;'>⚠️ Base de Datos No Encontrada</h2>
            <p><strong>La base de datos 'gardelcatalogo' no existe.</strong></p>
            
            <h3>🔧 Para crear la base de datos:</h3>
            <ol>
                <li>Abre <strong>phpMyAdmin</strong></li>
                <li>Ve a la pestaña <strong>SQL</strong></li>
                <li>Ejecuta: <code>CREATE DATABASE gardelcatalogo;</code></li>
                <li>Recarga esta página</li>
            </ol>
            
            <h3>🌐 Acceso directo:</h3>
            <p><a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a></p>
        </div>
        ");
    } else {
        die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6;'>
            <h2 style='color: #dc3545;'>❌ Error de Conexión</h2>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            
            <h3>🔧 Verificar:</h3>
            <ul>
                <li>XAMPP está corriendo</li>
                <li>MySQL está activo</li>
                <li>Credenciales correctas</li>
            </ul>
        </div>
        ");
    }
}

// Función para verificar si la base de datos está configurada
function verificarBaseDatos() {
    global $pdo;
    
    try {
        // Verificar si las tablas principales existen
        $tablas_requeridas = [
            'usuarios', 'categorias', 'productos', 'tallas', 
            'colores', 'bodegas', 'proveedores', 'clientes',
            'inventario', 'entradas', 'salidas'
        ];
        
        $tablas_existentes = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            $tablas_existentes[] = $row[0];
        }
        
        $faltantes = array_diff($tablas_requeridas, $tablas_existentes);
        
        if (!empty($faltantes)) {
            return [
                'status' => 'incompleta',
                'mensaje' => 'Faltan tablas: ' . implode(', ', $faltantes),
                'tablas_faltantes' => $faltantes
            ];
        }
        
        return [
            'status' => 'ok',
            'mensaje' => 'Base de datos configurada correctamente',
            'tablas' => count($tablas_existentes)
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'mensaje' => 'Error al verificar: ' . $e->getMessage()
        ];
    }
}

// Función para obtener estadísticas básicas
function obtenerEstadisticas() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Contar productos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $stats['productos'] = $stmt->fetch()['total'];
        
        // Contar categorías
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
        $stats['categorias'] = $stmt->fetch()['total'];
        
        // Contar proveedores
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM proveedores");
        $stats['proveedores'] = $stmt->fetch()['total'];
        
        // Contar entradas hoy
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas WHERE DATE(fecha) = CURDATE()");
        $stats['entradas_hoy'] = $stmt->fetch()['total'];
        
        // Contar salidas hoy
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM salidas WHERE DATE(fecha) = CURDATE()");
        $stats['salidas_hoy'] = $stmt->fetch()['total'];
        
        // Contar productos con stock bajo (menos de 10 unidades)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario WHERE cantidad < 10");
        $stats['stock_bajo'] = $stmt->fetch()['total'];
        
        // Contar productos agotados (0 unidades)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario WHERE cantidad = 0");
        $stats['agotados'] = $stmt->fetch()['total'];
        
        // Total de movimientos del mes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM (
            SELECT fecha FROM entradas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())
            UNION ALL
            SELECT fecha FROM salidas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())
        ) as movimientos");
        $stats['movimientos_mes'] = $stmt->fetch()['total'];
        
        return $stats;
        
    } catch (Exception $e) {
        // Si hay error, devolver valores por defecto
        return [
            'productos' => 0,
            'categorias' => 0,
            'proveedores' => 0,
            'entradas_hoy' => 0,
            'salidas_hoy' => 0,
            'stock_bajo' => 0,
            'agotados' => 0,
            'movimientos_mes' => 0,
            'error' => $e->getMessage()
        ];
    }
}

// Función para crear usuario administrador
function crearUsuarioAdmin($nombre, $correo, $contraseña) {
    global $pdo;
    
    try {
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, correo, contraseña, rol) 
            VALUES (?, ?, ?, 'admin')
            ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre), 
            contraseña = VALUES(contraseña)
        ");
        
        $stmt->execute([$nombre, $correo, $hash]);
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

// Configuración por defecto
define('DB_NAME', 'gardelcatalogo');
define('DB_VERSION', '1.0');
define('DB_DESCRIPTION', 'Base de datos existente para almacén');

// Crear usuario admin por defecto si no existe
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin'");
    $admin_count = $stmt->fetch()['total'];
    
    if ($admin_count == 0) {
        crearUsuarioAdmin('Administrador', 'admin@gardem.com', 'admin123');
    }
} catch (Exception $e) {
    // Ignorar errores al crear usuario admin
}

?> 