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
        
        try {
            // Obtener filtros
            $filtro_producto = isset($_GET['producto']) ? $_GET['producto'] : '';
            $filtro_stock = isset($_GET['stock']) ? $_GET['stock'] : '';

            // Consulta exactamente igual al sistema de almacén
            $sql = "
                SELECT 
                    p.id as producto_id,
                    p.nombre as producto_nombre,
                    p.descripcion,
                    p.tipo_producto,
                    p.precio,
                    c.nombre as color,
                    t.nombre as talla,
                    COALESCE(SUM(CASE WHEN e.id IS NOT NULL THEN e.cantidad ELSE 0 END), 0) as total_entradas,
                    COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN s.cantidad ELSE 0 END), 0) as total_salidas,
                    (COALESCE(SUM(CASE WHEN e.id IS NOT NULL THEN e.cantidad ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN s.cantidad ELSE 0 END), 0)) as saldo
                FROM productos p
                LEFT JOIN productos_entradas e ON p.id = e.producto_id
                LEFT JOIN productos_salidas s ON p.id = s.producto_id 
                    AND (e.color_id = s.color_id OR (e.color_id IS NULL AND s.color_id IS NULL))
                    AND (e.talla_id = s.talla_id OR (e.talla_id IS NULL AND s.talla_id IS NULL))
                LEFT JOIN colores c ON e.color_id = c.id
                LEFT JOIN tallas t ON e.talla_id = t.id
                WHERE e.id IS NOT NULL
                GROUP BY p.id, p.nombre, p.descripcion, p.tipo_producto, p.precio, c.nombre, t.nombre, e.color_id, e.talla_id
            ";
            
            // Aplicar filtros
            $where_conditions = [];
            $params = [];
            
            if ($filtro_producto) {
                $where_conditions[] = "p.nombre LIKE ?";
                $params[] = "%$filtro_producto%";
            }
            
            if ($where_conditions) {
                $sql .= " HAVING " . implode(" AND ", $where_conditions);
            }
            
            $sql .= " ORDER BY p.nombre, c.nombre, t.nombre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error_inventario = "No se pudo mostrar el inventario por un error de configuración. Contacta al administrador. <br><small>" . htmlspecialchars($e->getMessage()) . "</small>";
        }
        
        return [
            'inventario' => $inventario,
            'error_inventario' => $error_inventario,
            'filtros' => [
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