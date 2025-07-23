<?php
session_start();
require_once '../config/database.php';
require_once '../controlador/ControladorVentas.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si es una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida']);
    exit();
}

// Configurar cabeceras para JSON
header('Content-Type: application/json');

try {
    $controladorVentas = new ControladorVentas($pdo);
    
    // Verificar método y parámetros
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if (isset($_GET['obtener_color'])) {
            // Obtener nombre del color
            $color_id = (int)$_GET['obtener_color'];
            $stmt = $pdo->prepare("SELECT nombre FROM colores WHERE id = ?");
            $stmt->execute([$color_id]);
            $color = $stmt->fetch();
            
            if ($color) {
                echo json_encode(['success' => true, 'nombre' => $color['nombre']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Color no encontrado']);
            }
            
        } elseif (isset($_GET['obtener_talla'])) {
            // Obtener nombre de la talla
            $talla_id = (int)$_GET['obtener_talla'];
            $stmt = $pdo->prepare("SELECT nombre FROM tallas WHERE id = ?");
            $stmt->execute([$talla_id]);
            $talla = $stmt->fetch();
            
            if ($talla) {
                echo json_encode(['success' => true, 'nombre' => $talla['nombre']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Talla no encontrada']);
            }
            
        } elseif (isset($_GET['producto_id'])) {
            // Verificar stock de un producto específico
            $producto_id = (int)$_GET['producto_id'];
            $cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;
            
            if ($producto_id <= 0) {
                throw new Exception('ID de producto inválido');
            }
            
            $stock_info = $controladorVentas->verificarStockDisponible($producto_id, $cantidad);
            
            // Obtener información del producto
            $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }
            
            $response = [
                'success' => true,
                'producto_id' => $producto_id,
                'producto_nombre' => $producto['nombre'],
                'stock_actual' => $stock_info['stock_actual'],
                'cantidad_solicitada' => $stock_info['cantidad_solicitada'],
                'stock_despues_venta' => $stock_info['stock_despues_venta'],
                'tiene_stock_suficiente' => $stock_info['tiene_stock_suficiente'],
                'cumple_minimo' => $stock_info['cumple_minimo'],
                'puede_vender' => $stock_info['puede_vender'],
                'mensaje' => $this->generarMensajeStock($stock_info)
            ];
            
            echo json_encode($response);
            
        } elseif (isset($_GET['todos_productos'])) {
            // Obtener stock de todos los productos
            $productos = $controladorVentas->obtenerProductosConStock();
            
            $response = [
                'success' => true,
                'productos' => $productos
            ];
            
            echo json_encode($response);
            
        } else {
            throw new Exception('Parámetros insuficientes');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar múltiples productos (como en una venta)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['productos']) || !is_array($input['productos'])) {
            throw new Exception('Lista de productos requerida');
        }
        
        $validaciones = [];
        $puede_procesar_venta = true;
        $errores = [];
        
        foreach ($input['productos'] as $producto) {
            if (!isset($producto['producto_id']) || !isset($producto['cantidad'])) {
                $errores[] = 'Datos de producto incompletos';
                continue;
            }
            
            $producto_id = (int)$producto['producto_id'];
            $cantidad = (int)$producto['cantidad'];
            
            $stock_info = $controladorVentas->verificarStockDisponible($producto_id, $cantidad);
            
            if (!$stock_info['puede_vender']) {
                $puede_procesar_venta = false;
            }
            
            $validaciones[] = [
                'producto_id' => $producto_id,
                'validacion' => $stock_info,
                'mensaje' => $this->generarMensajeStock($stock_info)
            ];
        }
        
        $response = [
            'success' => true,
            'puede_procesar_venta' => $puede_procesar_venta,
            'validaciones' => $validaciones,
            'errores' => $errores
        ];
        
        echo json_encode($response);
        
    } else {
        throw new Exception('Método no permitido');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Función auxiliar para generar mensajes de stock
function generarMensajeStock($stock_info) {
    if (!$stock_info['tiene_stock_suficiente']) {
        return "Stock insuficiente. Disponible: {$stock_info['stock_actual']}, solicitado: {$stock_info['cantidad_solicitada']}";
    }
    
    if (!$stock_info['cumple_minimo']) {
        return "No se puede vender. El producto quedaría con menos de 12 unidades (quedarían: {$stock_info['stock_despues_venta']})";
    }
    
    if ($stock_info['stock_despues_venta'] <= 20) {
        return "⚠️ Advertencia: Después de la venta quedarán {$stock_info['stock_despues_venta']} unidades";
    }
    
    return "✅ Stock disponible: {$stock_info['stock_actual']} unidades";
}
?> 