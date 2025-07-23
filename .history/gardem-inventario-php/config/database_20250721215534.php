<?php
// ========================================
// CONFIGURACIÓN PRINCIPAL - Base de Datos SIMPLE
// ========================================

// Configuración de la base de datos SIMPLE
$host = 'localhost';
$dbname = 'gardelcatalogo_simple';  // Base de datos SIMPLE y LIMPIA
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
            <p><strong>La base de datos 'gardelcatalogo_simple' no existe.</strong></p>
            
            <h3>🔧 Para crear la base de datos:</h3>
            <ol>
                <li>Abre <strong>phpMyAdmin</strong></li>
                <li>Ve a la pestaña <strong>SQL</strong></li>
                <li>Copia y pega el contenido de: <code>sql/base_datos_simple.sql</code></li>
                <li>Ejecuta el script</li>
                <li>Recarga esta página</li>
            </ol>
            
            <h3>📁 Archivo SQL:</h3>
            <p><code>gardem-inventario-php/sql/base_datos_simple.sql</code></p>
            
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
            'mensaje' => 'Base de datos SIMPLE configurada correctamente',
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
        
        // Contar entradas hoy
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas WHERE DATE(fecha) = CURDATE()");
        $stats['entradas_hoy'] = $stmt->fetch()['total'];
        
        // Contar salidas hoy
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM salidas WHERE DATE(fecha) = CURDATE()");
        $stats['salidas_hoy'] = $stmt->fetch()['total'];
        
        // Productos con stock bajo
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM vista_stock_bajo");
        $stats['stock_bajo'] = $stmt->fetch()['total'];
        
        return $stats;
        
    } catch (Exception $e) {
        return [
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
define('DB_NAME', 'gardelcatalogo_simple');
define('DB_VERSION', '1.0');
define('DB_DESCRIPTION', 'Base de datos SIMPLE para almacén');

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