<?php
/**
 * Configuración de errores para debugging
 * Sistema de Vendedor
 */

// Mostrar errores en desarrollo
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // En producción, solo log de errores
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Función para log de errores personalizado
function logError($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logMessage .= ' - Context: ' . json_encode($context);
    }
    error_log($logMessage);
}

// Función para verificar conexión a base de datos
function verificarConexionDB($pdo) {
    try {
        $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        logError('Error de conexión a BD: ' . $e->getMessage());
        return false;
    }
}

// Función para verificar si las tablas existen
function verificarTablas($pdo) {
    $tablas_requeridas = ['usuario', 'productos', 'cliente', 'productos_ventas'];
    $tablas_faltantes = [];
    
    foreach ($tablas_requeridas as $tabla) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
            if ($stmt->rowCount() === 0) {
                $tablas_faltantes[] = $tabla;
            }
        } catch (PDOException $e) {
            $tablas_faltantes[] = $tabla;
        }
    }
    
    return $tablas_faltantes;
}
?> 