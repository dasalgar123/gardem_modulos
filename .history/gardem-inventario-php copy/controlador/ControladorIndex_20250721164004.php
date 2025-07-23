<?php
class ControladorIndex {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function verificarSesion() {
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_rol'] === 'almacenista';
    }
    
    public function obtenerPaginaSolicitada() {
        return isset($_GET['page']) ? $_GET['page'] : 'menu_principal';
    }
    
    public function obtenerPaginasValidas() {
        return [
            'menu_principal', 
            'entradas',           // Compras, garantías, devoluciones, traslados, ver_todo
            'salidas',            // Ventas, garantías, devoluciones, traslados, ver_todo
            'inventario',         // Ver inventario actual
            'ver_ventas',         // Ver ventas realizadas
            'compras',            // Gestionar compras
            'garantias',          // Gestionar garantías
            'devoluciones',       // Gestionar devoluciones
            'traslados',          // Gestionar traslados
            'ventas',             // Gestionar ventas
            'ver_todo',           // Ver todos los movimientos
            'entregar',           // Gestionar entregas
            'actualizar_inventario', // Actualizar inventario
            'inventario_en_linea',   // Sincronizar con inventario en línea
            'cerrar_sesion',      // Cerrar sesión
            'logout'              // Logout
        ];
    }
    
    public function incluirPagina($page) {
        $validPages = $this->obtenerPaginasValidas();
        
        if (in_array($page, $validPages)) {
            $filePath = __DIR__ . '/../vista/' . $page . '.php';
            if (file_exists($filePath)) {
                // Hacer disponibles las variables globales en el archivo incluido
                $pdo = $this->pdo;
                include $filePath;
            } else {
                echo '<div class="alert alert-warning">Página en construcción: ' . ucfirst($page) . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Página no encontrada</div>';
        }
    }
    
    public function obtenerUsuarioActual() {
        if ($this->verificarSesion()) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol' => $_SESSION['usuario_rol']
            ];
        }
        return null;
    }
    
    public function cerrarSesion() {
        session_destroy();
        session_start();
        header('Location: login.php');
        exit();
    }
    
    // Funciones específicas del flujo del almacenista
    
    public function actualizarInventario($tipo_movimiento, $producto_id, $cantidad, $bodega_id = null) {
        try {
            $this->pdo->beginTransaction();
            
            // Registrar el movimiento
            $stmt = $this->pdo->prepare("
                INSERT INTO movimientos_inventario (tipo, producto_id, cantidad, bodega_id, usuario_id, fecha)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tipo_movimiento, $producto_id, $cantidad, $bodega_id, $_SESSION['usuario_id']]);
            
            // Actualizar stock en inventario_bodega
            if ($bodega_id) {
                $stmt = $this->pdo->prepare("
                    UPDATE inventario_bodega 
                    SET stock_actual = stock_actual + ? 
                    WHERE producto_id = ? AND bodega_id = ?
                ");
                $stmt->execute([$cantidad, $producto_id, $bodega_id]);
            }
            
            // Sincronizar con inventario en línea
            $this->sincronizarInventarioEnLinea($producto_id, $bodega_id);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function sincronizarInventarioEnLinea($producto_id, $bodega_id = null) {
        try {
            // Obtener stock actual
            $stmt = $this->pdo->prepare("
                SELECT stock_actual FROM inventario_bodega 
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$producto_id, $bodega_id]);
            $stock = $stmt->fetchColumn();
            
            // Actualizar o insertar en inventario_en_linea
            $stmt = $this->pdo->prepare("
                INSERT INTO inventario_en_linea (producto_id, bodega_id, stock_actual, ultima_actualizacion, sincronizado, version)
                VALUES (?, ?, ?, NOW(), 1, 1)
                ON DUPLICATE KEY UPDATE 
                stock_actual = VALUES(stock_actual),
                ultima_actualizacion = NOW(),
                sincronizado = 1,
                version = version + 1
            ");
            $stmt->execute([$producto_id, $bodega_id, $stock]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function gestionarEntrega($venta_id, $producto_id, $cantidad, $bodega_id) {
        try {
            $this->pdo->beginTransaction();
            
            // Marcar producto como entregado
            $stmt = $this->pdo->prepare("
                UPDATE ventas_productos 
                SET estado = 'entregado', fecha_entrega = NOW(), almacenista_id = ?
                WHERE venta_id = ? AND producto_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $venta_id, $producto_id]);
            
            // Descontar del inventario físico
            $stmt = $this->pdo->prepare("
                UPDATE inventario_bodega 
                SET stock_actual = stock_actual - ? 
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$cantidad, $producto_id, $bodega_id]);
            
            // Registrar movimiento
            $this->actualizarInventario('entrega', $producto_id, -$cantidad, $bodega_id);
            
            // Sincronizar con inventario en línea
            $this->sincronizarInventarioEnLinea($producto_id, $bodega_id);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function obtenerEstadisticasGenerales() {
        try {
            $stats = [];
            
            // Total de productos
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos");
            $stats['total_productos'] = $stmt->fetchColumn();
            
            // Productos con stock bajo
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM inventario_bodega 
                WHERE stock_actual <= stock_minimo
            ");
            $stats['stock_bajo'] = $stmt->fetchColumn();
            
            // Productos agotados
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM inventario_bodega 
                WHERE stock_actual = 0
            ");
            $stats['agotados'] = $stmt->fetchColumn();
            
            // Ventas pendientes de entrega
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM ventas_productos 
                WHERE estado = 'pendiente_entrega'
            ");
            $stats['pendientes_entrega'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerMovimientosRecientes($limite = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT mi.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       u.nombre as usuario_nombre, b.nombre as bodega_nombre
                FROM movimientos_inventario mi
                JOIN productos p ON mi.producto_id = p.id
                JOIN usuario u ON mi.usuario_id = u.id
                LEFT JOIN bodega b ON mi.bodega_id = b.id
                ORDER BY mi.fecha DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Nuevas funciones para las vistas
    
    public function obtenerProducto($id, $bodega_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, ib.stock_actual, ib.stock_minimo, ib.stock_maximo
                FROM productos p
                JOIN inventario_bodega ib ON p.id = ib.producto_id
                WHERE p.id = ? AND ib.bodega_id = ?
            ");
            $stmt->execute([$id, $bodega_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function actualizarStockProducto($producto_id, $bodega_id, $stock_actual, $stock_minimo, $stock_maximo, $observaciones) {
        try {
            $this->pdo->beginTransaction();
            
            // Obtener stock anterior
            $stmt = $this->pdo->prepare("
                SELECT stock_actual FROM inventario_bodega 
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$producto_id, $bodega_id]);
            $stock_anterior = $stmt->fetchColumn();
            
            // Actualizar stock
            $stmt = $this->pdo->prepare("
                UPDATE inventario_bodega 
                SET stock_actual = ?, stock_minimo = ?, stock_maximo = ?, ultima_actualizacion = NOW()
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$stock_actual, $stock_minimo, $stock_maximo, $producto_id, $bodega_id]);
            
            // Registrar ajuste
            $diferencia = $stock_actual - $stock_anterior;
            if ($diferencia != 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO movimientos_inventario (tipo, producto_id, cantidad, bodega_id, usuario_id, fecha, observaciones)
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)
                ");
                $stmt->execute(['ajuste_manual', $producto_id, $diferencia, $bodega_id, $_SESSION['usuario_id'], $observaciones]);
            }
            
            // Sincronizar con inventario en línea
            $this->sincronizarInventarioEnLinea($producto_id, $bodega_id);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function obtenerDetalleVenta($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono, c.direccion as cliente_direccion,
                       vp.*, p.nombre as producto_nombre, p.codigo as producto_codigo, p.imagen as producto_imagen
                FROM ventas v
                JOIN cliente c ON v.cliente_id = c.id
                JOIN ventas_productos vp ON v.id = vp.venta_id
                JOIN productos p ON vp.producto_id = p.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function exportarVentas() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono,
                       COUNT(vp.id) as total_productos,
                       SUM(vp.cantidad * vp.precio_unitario) as total_venta
                FROM ventas v
                LEFT JOIN cliente c ON v.cliente_id = c.id
                LEFT JOIN ventas_productos vp ON v.id = vp.venta_id
                GROUP BY v.id
                ORDER BY v.fecha DESC
            ");
            $stmt->execute();
            $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generar CSV
            $filename = 'ventas_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Fecha', 'Cliente', 'Teléfono', 'Productos', 'Total', 'Estado']);
            
            foreach ($ventas as $venta) {
                fputcsv($output, [
                    $venta['id'],
                    $venta['fecha'],
                    $venta['cliente_nombre'],
                    $venta['cliente_telefono'],
                    $venta['total_productos'],
                    $venta['total_venta'],
                    $venta['estado'] ?? 'activa'
                ]);
            }
            
            fclose($output);
            exit();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function exportarInventario() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.nombre, p.codigo, c.nombre as categoria, b.nombre as bodega,
                       ib.stock_actual, ib.stock_minimo, ib.stock_maximo,
                       ib.ultima_actualizacion
                FROM productos p
                JOIN inventario_bodega ib ON p.id = ib.producto_id
                JOIN bodega b ON ib.bodega_id = b.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                ORDER BY p.nombre
            ");
            $stmt->execute();
            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $filename = 'inventario_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Producto', 'Código', 'Categoría', 'Bodega', 'Stock Actual', 'Stock Mínimo', 'Stock Máximo', 'Última Actualización']);
            
            foreach ($inventario as $item) {
                fputcsv($output, [
                    $item['nombre'],
                    $item['codigo'],
                    $item['categoria'] ?? 'Sin categoría',
                    $item['bodega'],
                    $item['stock_actual'],
                    $item['stock_minimo'],
                    $item['stock_maximo'],
                    $item['ultima_actualizacion']
                ]);
            }
            
            fclose($output);
            exit();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function sincronizarTodo() {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT p.id, ib.bodega_id
                FROM productos p
                JOIN inventario_bodega ib ON p.id = ib.producto_id
            ");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sincronizados = 0;
            foreach ($productos as $producto) {
                if ($this->sincronizarInventarioEnLinea($producto['id'], $producto['bodega_id'])) {
                    $sincronizados++;
                }
            }
            
            return ['success' => true, 'sincronizados' => $sincronizados, 'total' => count($productos)];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function verificarConexion() {
        try {
            $inicio = microtime(true);
            $stmt = $this->pdo->query("SELECT 1");
            $stmt->fetch();
            $fin = microtime(true);
            $latencia = round(($fin - $inicio) * 1000, 2);
            
            return ['success' => true, 'latencia' => $latencia];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Procesar acciones AJAX
    public function procesarAccion() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'gestionar_entrega':
                $resultado = $this->gestionarEntrega(
                    $input['venta_id'],
                    $input['producto_id'],
                    $input['cantidad'],
                    $input['bodega_id']
                );
                echo json_encode(['success' => $resultado]);
                break;
                
            case 'actualizar_stock':
                $resultado = $this->actualizarStockProducto(
                    $input['producto_id'],
                    $input['bodega_id'],
                    $input['stock_actual'],
                    $input['stock_minimo'],
                    $input['stock_maximo'],
                    $input['observaciones'] ?? ''
                );
                echo json_encode(['success' => $resultado]);
                break;
                
            case 'sincronizar_todo':
                $resultado = $this->sincronizarTodo();
                echo json_encode($resultado);
                break;
                
            case 'verificar_conexion':
                $resultado = $this->verificarConexion();
                echo json_encode($resultado);
                break;
                
            case 'detalle_venta':
                $venta = $this->obtenerDetalleVenta($_GET['id']);
                include __DIR__ . '/../vista/detalle_venta.php';
                break;
                
            case 'exportar_ventas':
                $this->exportarVentas();
                break;
                
            case 'exportar_inventario':
                $this->exportarInventario();
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Procesar acciones AJAX si se solicita
if (isset($_GET['action']) || isset($_POST['action']) || (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json')) {
    $controlador = new ControladorIndex($pdo);
    $controlador->procesarAccion();
    exit();
}
?> 