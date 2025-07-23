<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Incluir el controlador
require_once 'controlador/ControladorProveedores.php';

// Crear instancia del controlador
$controladorProveedores = new ControladorProveedores($pdo);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        // Crear nuevo proveedor
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'direccion' => $_POST['direccion'] ?? ''
        ];
        
        if ($controladorProveedores->crearProveedor($datos)) {
            header('Location: index.php?page=proveedores&success=1');
        } else {
            header('Location: index.php?page=proveedores&error=Error al crear proveedor');
        }
        exit();
        
    } elseif ($accion === 'actualizar') {
        // Actualizar proveedor existente
        $id = $_POST['id'] ?? '';
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'direccion' => $_POST['direccion'] ?? ''
        ];
        
        if ($controladorProveedores->actualizarProveedor($id, $datos)) {
            header('Location: index.php?page=proveedores&success=1');
        } else {
            header('Location: index.php?page=proveedores&error=Error al actualizar proveedor');
        }
        exit();
        
    } elseif ($accion === 'eliminar') {
        // Eliminar proveedor
        $id = $_POST['id'] ?? '';
        
        if ($controladorProveedores->eliminarProveedor($id)) {
            header('Location: index.php?page=proveedores&success=1');
        } else {
            header('Location: index.php?page=proveedores&error=Error al eliminar proveedor');
        }
        exit();
    }
}

// Si no es POST, redirigir a la página de proveedores
header('Location: index.php?page=proveedores');
exit();
?> 