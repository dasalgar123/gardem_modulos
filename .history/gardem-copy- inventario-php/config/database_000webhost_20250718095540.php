<?php
/**
 * Configuración de Base de Datos para 000WEBHOST
 * Sistema de Almacenista - Gardem Inventario
 * 
 * INSTRUCCIONES:
 * 1. Cambia los valores de abajo con los datos que te dé 000webhost
 * 2. Renombra este archivo a database.php
 * 3. O modifica el archivo database.php original con estos valores
 */

// Configuración de la base de datos para 000WEBHOST
// IMPORTANTE: Cambiar estos valores con los datos que te dé 000webhost
const DB_HOST = 'localhost'; // Normalmente es localhost en 000webhost
const DB_NAME = 'tu_nombre_bd_000webhost'; // El nombre que elijas para tu BD
const DB_USER = 'tu_usuario_000webhost';   // El usuario que te dé 000webhost
const DB_PASS = 'tu_password_000webhost';  // La contraseña que elijas

// Zona horaria
date_default_timezone_set('America/Bogota');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
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
?> 