<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=entradas&error=Metodo no permitido');
    exit();
}

try {
    // Obtener datos del formulario
    $producto_id = $_POST['producto_id'] ?? null;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $talla_id = $_POST['talla_id'] ?? null;
    $color_id = $_POST['color_id'] ?? null;
    $bodega_id = $_POST['bodega_id'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;
    $motivo = $_POST['motivo'] ?? null;
    $factura_remision = $_POST['factura_remision'] ?? null;
    $beneficiario_tipo = $_POST['beneficiario_tipo'] ?? null;
    $beneficiario_id = $_POST['beneficiario_id'] ?? null;

    // Validaciones básicas
    if (!$producto_id || !$cantidad) {
        throw new Exception('Producto y cantidad son obligatorios');
    }

    if ($cantidad <= 0) {
        throw new Exception('La cantidad debe ser mayor a 0');
    }

    // Obtener nombre del beneficiario según el tipo
    $beneficiario_nombre = '';
    if ($beneficiario_tipo && $beneficiario_id) {
        if ($beneficiario_tipo === 'proveedor') {
            // Lista hardcodeada de proveedores
            $proveedores = [
                1 => 'diseños stely',
                2 => 'Textiles ABC',
                3 => 'Ropa Express',
                4 => 'Moda Latina'
            ];
            $beneficiario_nombre = $proveedores[$beneficiario_id] ?? 'Proveedor ' . $beneficiario_id;
        } elseif ($beneficiario_tipo === 'cliente') {
            // Lista hardcodeada de clientes
            $clientes = [
                1 => 'Cliente Mayorista A',
                2 => 'Tienda Fashion',
                3 => 'Boutique Elegante',
                4 => 'Distribuidor XYZ'
            ];
            $beneficiario_nombre = $clientes[$beneficiario_id] ?? 'Cliente ' . $beneficiario_id;
        } elseif ($beneficiario_tipo === 'interno') {
            $beneficiario_nombre = 'Uso Interno';
        }
    }

    // Insertar en productos_entradas
    $stmt = $pdo->prepare("
        INSERT INTO productos_entradas 
        (producto_id, bodega_id, cantidad, fecha, motivo, beneficiario_tipo, beneficiario_id, factura_remision, beneficiario) 
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $producto_id,
        $bodega_id ?: null,
        $cantidad,
        $motivo,
        $beneficiario_tipo,
        $beneficiario_id,
        $factura_remision,
        $beneficiario_nombre
    ]);

    $entrada_id = $pdo->lastInsertId();

    // Actualizar stock en inventario_bodega (solo si hay bodega_id válido)
    if ($bodega_id) {
        $stmt = $pdo->prepare("
            INSERT INTO inventario_bodega (producto_id, bodega_id, stock_actual) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE stock_actual = stock_actual + ?
        ");
        
        $stmt->execute([$producto_id, $bodega_id, $cantidad, $cantidad]);
    } else {
        // Si no hay bodega_id, solo actualizar el stock del producto
        $stmt = $pdo->prepare("
            INSERT INTO inventario_bodega (producto_id, stock_actual) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE stock_actual = stock_actual + ?
        ");
        
        $stmt->execute([$producto_id, $cantidad, $cantidad]);
    }

    // Si hay talla y color, insertar en inventario_tallas_colores_categorias
    if ($talla_id && $color_id && $categoria_id) {
        $stmt = $pdo->prepare("
            INSERT INTO inventario_tallas_colores_categorias 
            (producto_id, talla_id, color_id, `stock existente`, categoria_id, fecha_ingreso) 
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE `stock existente` = `stock existente` + ?
        ");
        
        $stmt->execute([$producto_id, $talla_id, $color_id, $cantidad, $categoria_id, $cantidad]);
    }

    // Redirigir con mensaje de éxito
    header('Location: index.php?page=entradas&success=1&entrada_id=' . $entrada_id);
    exit();

} catch (Exception $e) {
    // Redirigir con mensaje de error
    header('Location: index.php?page=entradas&error=' . urlencode($e->getMessage()));
    exit();
}
?> 