<?php
class ControladorIndex {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function verificarSesion() {
        return isset($_SESSION['usuario_id']);
    }
    
    public function obtenerPaginaSolicitada() {
        return isset($_GET['page']) ? $_GET['page'] : 'menu_principal';
    }
    
    public function obtenerPaginasValidas() {
        return [
            'menu_principal', 
            'productos', 
            'proveedores', 
            'entradas', 
            'salidas', 
            'garantias', 
            'devoluciones', 
            'traslados', 
            'compras', 
            'inventario', 
            'reportes', 
            'perfil', 
            'logout'
        ];
    }
    
    public function incluirPagina($page) {
        $validPages = $this->obtenerPaginasValidas();
        
        if (in_array($page, $validPages)) {
            $filePath = __DIR__ . '/../vista/' . $page . '.php';
            if (file_exists($filePath)) {
                // Hacer disponibles las variables globales en el archivo incluido
                $pdo = $this->pdo; // Make $pdo available to included files
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
                'stock_bajo' => 0,
                'agotados' => 0
            ];
        }
    }
}
?> 