<?php
// Controlador para la gestión de inventario
class ControladorInventario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerDatosInventario() {
        $error_inventario = null;
        $inventario = [];
        $bodegas = [];
        $bodegas_exists = false;
        
        try {
            // Verificar si existe la tabla bodega (singular, no plural)
            try {
                $this->pdo->query('SELECT 1 FROM bodega LIMIT 1');
                $bodegas_exists = true;
            } catch (Exception $e) {}

            // Obtener filtros
            $filtro_bodega = isset($_GET['bodega']) ? (int)$_GET['bodega'] : '';
            $filtro_producto = isset($_GET['producto']) ? $_GET['producto'] : '';
            $filtro_stock = isset($_GET['stock']) ? $_GET['stock'] : '';

            // Consulta simple usando la estructura actual de la base de datos
            if ($bodegas_exists) {
                // Consulta con bodegas
                $sql = "SELECT 
                    p.id AS producto_id,
                    p.nombre AS producto_nombre,
                    p.descripcion,
                    p.precio,
                    b.id AS bodega_id,
                    b.nombre AS bodega_nombre,
                    COALESCE(i.stock_actual, 0) AS stock_actual
                FROM productos p
                CROSS JOIN bodega b
                LEFT JOIN inventario i ON p.id = i.producto_id";
                
                // Aplicar filtros
                $where_conditions = [];
                $params = [];
                
                if ($filtro_bodega) {
                    $where_conditions[] = "b.id = ?";
                    $params[] = $filtro_bodega;
                }
                
                if ($filtro_producto) {
                    $where_conditions[] = "p.nombre LIKE ?";
                    $params[] = "%$filtro_producto%";
                }
                
                if ($where_conditions) {
                    $sql .= " WHERE " . implode(" AND ", $where_conditions);
                }
                
                $sql .= " ORDER BY b.nombre, p.nombre";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Obtener lista de bodegas para el filtro
                $stmt_bodegas = $this->pdo->query("SELECT id, nombre FROM bodega ORDER BY nombre");
                $bodegas = $stmt_bodegas->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Consulta simple sin bodegas
                $sql = "SELECT 
                    p.id AS producto_id,
                    p.nombre AS producto_nombre,
                    p.descripcion,
                    p.precio,
                    COALESCE(i.stock_actual, 0) AS stock_actual
                FROM productos p
                LEFT JOIN inventario i ON p.id = i.producto_id";
                
                // Aplicar filtros
                $where_conditions = [];
                $params = [];
                
                if ($filtro_producto) {
                    $where_conditions[] = "p.nombre LIKE ?";
                    $params[] = "%$filtro_producto%";
                }
                
                if ($where_conditions) {
                    $sql .= " WHERE " . implode(" AND ", $where_conditions);
                }
                
                $sql .= " ORDER BY p.nombre";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error_inventario = "No se pudo mostrar el inventario por un error de configuración. Contacta al administrador. <br><small>" . htmlspecialchars($e->getMessage()) . "</small>";
        }
        
        return [
            'inventario' => $inventario,
            'bodegas' => $bodegas,
            'bodegas_exists' => $bodegas_exists,
            'error_inventario' => $error_inventario,
            'filtros' => [
                'bodega' => $filtro_bodega ?? '',
                'producto' => $filtro_producto ?? '',
                'stock' => $filtro_stock ?? ''
            ]
        ];
    }
    
    // Función para obtener el color del stock
    public static function getStockColor($stock) {
        if ($stock <= 0) return 'stock-agotado';
        if ($stock <= 10) return 'stock-bajo';
        return 'stock-disponible';
    }
    
    // Función para formatear precio
    public static function formatPrice($price) {
        return '$' . number_format($price, 2);
    }
}
?> 