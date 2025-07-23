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
    header('Location: index.php?page=salidas&error=Metodo no permitido');
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

    // Verificar stock disponible
    $stmt = $pdo->prepare("
        SELECT COALESCE(ib.stock_actual, 0) as stock_actual,
               COALESCE(entradas.total_entradas, 0) as total_entradas,
               COALESCE(salidas.total_salidas, 0) as total_salidas
        FROM productos p
        LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_entradas 
            FROM productos_entradas 
            GROUP BY producto_id
        ) entradas ON p.id = entradas.producto_id
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_salidas 
            FROM productos_salidas 
            GROUP BY producto_id
        ) salidas ON p.id = salidas.producto_id
        WHERE p.id = ?
    ");
    $stmt->execute([$producto_id]);
    $stock_info = $stmt->fetch();
    
    $stock_disponible = ($stock_info['total_entradas'] ?? 0) - ($stock_info['total_salidas'] ?? 0);
    
    if ($cantidad > $stock_disponible) {
        throw new Exception("Stock insuficiente. Disponible: $stock_disponible, Solicitado: $cantidad");
    }

    // Obtener nombre del beneficiario según el tipo
    $beneficiario_nombre = '';
    if ($beneficiario_tipo && $beneficiario_id) {
        if ($beneficiario_tipo === 'proveedor') {
            // Consultar proveedor real
            $stmt = $pdo->prepare("SELECT nombre FROM proveedor WHERE id = ?");
            $stmt->execute([$beneficiario_id]);
            $result = $stmt->fetch();
            $beneficiario_nombre = $result['nombre'] ?? 'Proveedor ' . $beneficiario_id;
        } elseif ($beneficiario_tipo === 'cliente') {
            // Consultar cliente real
            $stmt = $pdo->prepare("SELECT nombre FROM cliente WHERE id = ?");
            $stmt->execute([$beneficiario_id]);
            $result = $stmt->fetch();
            $beneficiario_nombre = $result['nombre'] ?? 'Cliente ' . $beneficiario_id;
        } elseif ($beneficiario_tipo === 'interno') {
            $beneficiario_nombre = 'Uso Interno';
        }
    }

    // Insertar en productos_salidas (adaptado a tu estructura)
    $stmt = $pdo->prepare("
        INSERT INTO productos_salidas 
        (producto_id, cantidad, fecha, motivo, destinatario_tipo, destinatario_id, factura_remision, cliente_id) 
        VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $producto_id,
        $cantidad,
        $motivo,
        $beneficiario_tipo,
        $beneficiario_id,
        $factura_remision,
        $beneficiario_tipo === 'cliente' ? $beneficiario_id : null
    ]);

    $salida_id = $pdo->lastInsertId();

    // Actualizar stock en inventario_bodega (reducir)
    try {
        if ($bodega_id && $bodega_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE inventario_bodega 
                SET stock_actual = stock_actual - ? 
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$cantidad, $producto_id, $bodega_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE inventario_bodega 
                SET stock_actual = stock_actual - ? 
                WHERE producto_id = ?
            ");
            $stmt->execute([$cantidad, $producto_id]);
        }
    } catch (Exception $e) {
        // Si falla, intentar insertar con stock negativo
        $stmt = $pdo->prepare("
            INSERT INTO inventario_bodega (producto_id, stock_actual) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE stock_actual = stock_actual - ?
        ");
        $stmt->execute([$producto_id, -$cantidad, $cantidad]);
    }

    // Si hay talla y color, actualizar en inventario_tallas_colores_categorias
    if ($talla_id && $color_id && $categoria_id) {
        $stmt = $pdo->prepare("
            UPDATE inventario_tallas_colores_categorias 
            SET `stock existente` = `stock existente` - ? 
            WHERE producto_id = ? AND talla_id = ? AND color_id = ? AND categoria_id = ?
        ");
        $stmt->execute([$cantidad, $producto_id, $talla_id, $color_id, $categoria_id]);
    }

    // Redirigir con mensaje de éxito
    header('Location: index.php?page=salidas&success=1&salida_id=' . $salida_id);
    exit();

} catch (Exception $e) {
    // Redirigir con mensaje de error
    header('Location: index.php?page=salidas&error=' . urlencode($e->getMessage()));
    exit();
}
?> 