<?php
// ========================================
// CONFIGURACIÓN BASE DE DATOS SIMPLE
// ========================================
// Esta configuración es para la base de datos LIMPIA

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'gardelcatalogo_simple';  // Base de datos SIMPLE
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
    // Si no existe la base de datos, crearla
    if ($e->getCode() == 1049) {
        try {
            // Conectar sin especificar base de datos
            $pdo_temp = new PDO(
                "mysql:host=$host;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Crear la base de datos
            $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
            $pdo_temp->exec("USE $dbname");
            
            // Ejecutar script de creación de tablas
            $sql_file = __DIR__ . '/../sql/base_datos_simple.sql';
            if (file_exists($sql_file)) {
                $sql_content = file_get_contents($sql_file);
                $pdo_temp->exec($sql_content);
            }
            
            // Reconectar a la base de datos creada
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
            
            $pdo->exec("SET time_zone = '-05:00'");
            
        } catch (PDOException $e2) {
            die("Error al crear la base de datos: " . $e2->getMessage());
        }
    } else {
        die("Error de conexión: " . $e->getMessage());
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

// Función para limpiar datos de prueba (si es necesario)
function limpiarDatosPrueba() {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Limpiar entradas y salidas de prueba
        $pdo->exec("DELETE FROM entradas WHERE id > 0");
        $pdo->exec("DELETE FROM salidas WHERE id > 0");
        
        // Resetear inventario
        $pdo->exec("UPDATE inventario SET stock_actual = 0");
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
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

?> 