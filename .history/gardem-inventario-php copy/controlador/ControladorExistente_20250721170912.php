<?php
class ControladorExistente {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function verificarSesion() {
        return isset($_SESSION['usuario_id']) && ($_SESSION['usuario_rol'] === 'almacenista' || $_SESSION['usuario_rol'] === 'administrador');
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
                'correo' => $_SESSION['usuario_correo'],
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
    
    // Funciones específicas del flujo del almacenista adaptadas a tu estructura
    
    public function actualizarInventario($tipo_movimiento, $producto_id, $cantidad, $observaciones = '') {
        try {
            $this->pdo->beginTransaction();
            
            // Registrar el movimiento
            $stmt = $this->pdo->prepare("
                INSERT INTO movimientos (tipo, producto_id, cantidad, usuario_id, observaciones)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tipo_movimiento, $producto_id, $cantidad, $_SESSION['usuario_id'], $observaciones]);
            
            // Actualizar stock en inventario
            $stmt = $this->pdo->prepare("
                UPDATE inventario 
                SET stock_actual = stock_actual + ?, ultima_actualizacion = NOW()
                WHERE producto_id = ?
            ");
            $stmt->execute([$cantidad, $producto_id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function gestionarEntrega($venta_id, $producto_id, $cantidad) {
        try {
            $this->pdo->beginTransaction();
            
            // Marcar producto como entregado
            $stmt = $this->pdo->prepare("
                UPDATE ventas_productos 
                SET estado = 'entregado', fecha_entrega = NOW(), almacenista_id = ?
                WHERE venta_id = ? AND producto_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $venta_id, $producto_id]);
            
            // Descontar del inventario
            $stmt = $this->pdo->prepare("
                UPDATE inventario 
                SET stock_actual = stock_actual - ?, ultima_actualizacion = NOW()
                WHERE producto_id = ?
            ");
            $stmt->execute([$cantidad, $producto_id]);
            
            // Registrar movimiento
            $this->actualizarInventario('entrega', $producto_id, -$cantidad, 'Entrega de venta #' . $venta_id);
            
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
                SELECT COUNT(*) FROM inventario 
                WHERE stock_actual <= stock_minimo
            ");
            $stats['stock_bajo'] = $stmt->fetchColumn();
            
            // Productos agotados
            $stmt = $this->pdo->query("
                SELECT COUNT(*) FROM inventario 
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
                SELECT m.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       u.nombre as usuario_nombre
                FROM movimientos m
                JOIN productos p ON m.producto_id = p.id
                JOIN usuario u ON m.usuario_id = u.id
                ORDER BY m.fecha DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerProducto($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, i.stock_actual, i.stock_minimo, i.stock_maximo
                FROM productos p
                JOIN inventario i ON p.id = i.producto_id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function actualizarStockProducto($producto_id, $stock_actual, $stock_minimo, $stock_maximo, $observaciones) {
        try {
            $this->pdo->beginTransaction();
            
            // Obtener stock anterior
            $stmt = $this->pdo->prepare("
                SELECT stock_actual FROM inventario 
                WHERE producto_id = ?
            ");
            $stmt->execute([$producto_id]);
            $stock_anterior = $stmt->fetchColumn();
            
            // Actualizar stock
            $stmt = $this->pdo->prepare("
                UPDATE inventario 
                SET stock_actual = ?, stock_minimo = ?, stock_maximo = ?, ultima_actualizacion = NOW()
                WHERE producto_id = ?
            ");
            $stmt->execute([$stock_actual, $stock_minimo, $stock_maximo, $producto_id]);
            
            // Registrar ajuste
            $diferencia = $stock_actual - $stock_anterior;
            if ($diferencia != 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO movimientos (tipo, producto_id, cantidad, usuario_id, observaciones)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute(['ajuste_manual', $producto_id, $diferencia, $_SESSION['usuario_id'], $observaciones]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }
    
    public function obtenerVentasPendientes() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*, vp.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                FROM ventas v
                JOIN ventas_productos vp ON v.id = vp.venta_id
                JOIN productos p ON vp.producto_id = p.id
                WHERE vp.estado = 'pendiente_entrega'
                ORDER BY v.fecha DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerTodasLasVentas() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT v.*, 
                       COUNT(vp.id) as total_productos,
                       SUM(vp.cantidad * vp.precio_unitario) as total_venta
                FROM ventas v
                LEFT JOIN ventas_productos vp ON v.id = vp.venta_id
                GROUP BY v.id
                ORDER BY v.fecha DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerInventarioCompleto() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.nombre, p.codigo, p.categoria, p.precio,
                       i.stock_actual, i.stock_minimo, i.stock_maximo,
                       i.ultima_actualizacion
                FROM productos p
                JOIN inventario i ON p.id = i.producto_id
                ORDER BY p.nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerTodosLosMovimientos() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT m.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       u.nombre as usuario_nombre
                FROM movimientos m
                JOIN productos p ON m.producto_id = p.id
                JOIN usuario u ON m.usuario_id = u.id
                ORDER BY m.fecha DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
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
                    $input['cantidad']
                );
                echo json_encode(['success' => $resultado]);
                break;
                
            case 'actualizar_stock':
                $resultado = $this->actualizarStockProducto(
                    $input['producto_id'],
                    $input['stock_actual'],
                    $input['stock_minimo'],
                    $input['stock_maximo'],
                    $input['observaciones'] ?? ''
                );
                echo json_encode(['success' => $resultado]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Procesar acciones AJAX si se solicita
if (isset($_GET['action']) || isset($_POST['action']) || (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json')) {
    $controlador = new ControladorExistente($pdo);
    $controlador->procesarAccion();
    exit();
}
?> 