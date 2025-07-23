<?php
/**
 * Configuración de Base de Datos
 * Sistema de Almacenista - Gardem Inventario
 * 
 * Maneja 3 entornos: LOCAL, 000WEBHOST, INFINITYFREE
 */

// Detectar el entorno automáticamente
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    // LOCAL (XAMPP)
    if (in_array($host, ['localhost', '127.0.0.1']) || 
        strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false) {
        return 'LOCAL';
    }
    
    // 000WEBHOST
    if (strpos($host, '000webhostapp.com') !== false ||
        strpos($host, '000webhost') !== false) {
        return '000WEBHOST';
    }
    
    // INFINITYFREE
    if (strpos($host, 'epizy.com') !== false ||
        strpos($host, 'rf.gd') !== false ||
        strpos($host, 'fwh.is') !== false ||
        strpos($host, 'infinityfree') !== false) {
        return 'INFINITYFREE';
    }
    
    // Por defecto, asumir servidor web genérico
    return 'GENERIC';
}

// Configuración según el entorno
$environment = detectEnvironment();

// Variables de configuración
$DB_HOST = '';
$DB_NAME = '';
$DB_USER = '';
$DB_PASS = '';

switch ($environment) {
    case 'LOCAL':
        // Configuración LOCAL (XAMPP)
        $DB_HOST = 'localhost';
        $DB_NAME = 'gardelcatalogo'; // Tu base de datos real
        $DB_USER = 'root';
        $DB_PASS = '';
        break;
        
    case '000WEBHOST':
        // Configuración 000WEBHOST
        // IMPORTANTE: Cambiar estos valores con los datos que te dé 000webhost
        $DB_HOST = 'localhost';
        $DB_NAME = 'tu_nombre_bd_000webhost'; // Tu nombre de BD en 000webhost
        $DB_USER = 'tu_usuario_000webhost';   // Tu usuario en 000webhost
        $DB_PASS = 'tu_password_000webhost';  // Tu contraseña en 000webhost
        break;
        
    case 'INFINITYFREE':
        // Configuración INFINITYFREE
        // IMPORTANTE: Cambiar estos valores con los datos que te dé InfinityFree
        $DB_HOST = 'sql.infinityfree.com'; // Host de InfinityFree
        $DB_NAME = 'if0_39503653_gardem_inventario'; // Tu nombre de BD en InfinityFree
        $DB_USER = 'if0_39503653';   // Tu usuario en InfinityFree
        $DB_PASS = 'Adso2826321';  // Tu contraseña en InfinityFree
        break;
        
    default:
        // Configuración genérica para otros servidores
        $DB_HOST = 'localhost';
        $DB_NAME = 'tu_nombre_base_datos';
        $DB_USER = 'tu_usuario_bd';
        $DB_PASS = 'tu_password_bd';
        break;
}

// Zona horaria
date_default_timezone_set('America/Bogota');

try {
    $dsn = 'mysql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Función para verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Función para verificar si el usuario es administrador
 */
function isAdministrador() {
    return isLoggedIn() && $_SESSION['usuario_rol'] === 'administrador';
}

/**
 * Función para redirigir si no está logueado
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Función para redirigir si no es administrador
 */
function requireAdministrador() {
    if (!isAdministrador()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Función para generar números de factura/recibo
 */
function generarNumeroDocumento($tipo) {
    $prefijo = '';
    switch ($tipo) {
        case 'entrada':
            $prefijo = 'ENT';
            break;
        case 'salida':
            $prefijo = 'SAL';
            break;
        case 'traslado':
            $prefijo = 'TRA';
            break;
        case 'garantia':
            $prefijo = 'GAR';
            break;
        case 'devolucion':
            $prefijo = 'DEV';
            break;
        case 'compra':
            $prefijo = 'COM';
            break;
        default:
            $prefijo = 'DOC';
    }
    
    return $prefijo . '-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Función para formatear fechas
 */
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Función para formatear precios
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Función para mostrar información del entorno (solo para debug)
 */
function getEnvironmentInfo() {
    $env = detectEnvironment();
    switch ($env) {
        case 'LOCAL':
            return "Entorno: LOCAL (XAMPP)";
        case '000WEBHOST':
            return "Entorno: 000WEBHOST";
        case 'INFINITYFREE':
            return "Entorno: INFINITYFREE";
        default:
            return "Entorno: SERVIDOR GENÉRICO";
    }
}
?> 