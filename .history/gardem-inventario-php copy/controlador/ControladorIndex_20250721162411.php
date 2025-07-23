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
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function sincronizarInventarioEnLinea($producto_id, $bodega_id = null) {
        try {
            // Actualizar el inventario en línea (base de datos compartida)
            $stmt = $this->pdo->prepare("
                UPDATE inventario_en_linea 
                SET ultima_actualizacion = NOW(), 
                    sincronizado = 1 
                WHERE producto_id = ? AND bodega_id = ?
            ");
            $stmt->execute([$producto_id, $bodega_id]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function gestionarEntrega($venta_id, $producto_id, $cantidad, $bodega_id) {
        try {
            $this->pdo->beginTransaction();
            
            // Marcar como entregado
            $stmt = $this->pdo->prepare("
                UPDATE ventas_productos 
                SET estado = 'entregado', 
                    fecha_entrega = NOW(),
                    almacenista_id = ?
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
            
            // Sincronizar con inventario en línea
            $this->sincronizarInventarioEnLinea($producto_id, $bodega_id);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function obtenerEstadisticasGenerales() {
        try {
            $stats = [];
            
            // Total productos
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM productos");
            $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total proveedores
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM proveedores");
            $stats['total_proveedores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Entradas del día
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM entradas WHERE DATE(fecha) = CURDATE()");
            $stats['entradas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Salidas del día
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM salidas WHERE DATE(fecha) = CURDATE()");
            $stats['salidas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Ventas pendientes de entrega
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total 
                FROM ventas_productos 
                WHERE estado = 'pendiente_entrega'
            ");
            $stats['pendientes_entrega'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Productos con stock bajo
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM inventario_bodega WHERE stock_actual <= 10");
            $stats['stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Productos agotados
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM inventario_bodega WHERE stock_actual = 0");
            $stats['agotados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return $stats;
        } catch (Exception $e) {
            return [
                'total_productos' => 0,
                'total_proveedores' => 0,
                'entradas_hoy' => 0,
                'salidas_hoy' => 0,
                'pendientes_entrega' => 0,
                'stock_bajo' => 0,
                'agotados' => 0
            ];
        }
    }
    
    public function obtenerMovimientosRecientes($limite = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT m.*, p.nombre as producto_nombre, u.nombre as usuario_nombre
                FROM movimientos_inventario m
                JOIN productos p ON m.producto_id = p.id
                JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.fecha DESC
                LIMIT ?
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?> 