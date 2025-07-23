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

            // Consulta con cálculo en tiempo real (entradas - salidas) por variante
            $sql = "
                SELECT 
                    p.id AS producto_id,
                    p.nombre AS producto_nombre,
                    p.descripcion,
                    p.precio,
                    p.tipo_producto,
                    c.nombre AS color,
                    t.nombre AS talla,
                    COALESCE(SUM(e.cantidad), 0) AS total_entradas,
                    COALESCE(SUM(s.cantidad), 0) AS total_salidas,
                    (COALESCE(SUM(e.cantidad), 0) - COALESCE(SUM(s.cantidad), 0)) AS saldo
                FROM productos p
                LEFT JOIN colores c ON p.color_id = c.id
                LEFT JOIN tallas t ON p.talla_id = t.id
                LEFT JOIN productos_entradas e ON p.id = e.producto_id
                LEFT JOIN productos_salidas s ON p.id = s.producto_id
                GROUP BY p.id, p.nombre, p.descripcion, p.precio, p.tipo_producto, c.nombre, t.nombre
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