<?php
// Controlador para la vista de inventario del vendedor (solo lectura)
class ControladorInventario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerInventario() {
        try {
            // Obtener inventario real del módulo de almacén
            $sql = "SELECT 
                p.id AS producto_id,
                p.nombre AS producto_nombre,
                p.descripcion,
                p.precio,
                p.tipo_producto,
                COALESCE(c.nombre, 'N/A') AS color,
                COALESCE(t.nombre, 'N/A') AS talla,
                COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) as total_entradas,
                COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0) as total_salidas,
                (COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0)) as stock_total
            FROM productos p
            LEFT JOIN productos_entradas pe ON p.id = pe.producto_id
            LEFT JOIN productos_salidas ps ON p.id = ps.producto_id 
                AND (pe.color_id = ps.color_id OR (pe.color_id IS NULL AND ps.color_id IS NULL))
                AND (pe.talla_id = ps.talla_id OR (pe.talla_id IS NULL AND ps.talla_id IS NULL))
            LEFT JOIN colores c ON pe.color_id = c.id
            LEFT JOIN tallas t ON pe.talla_id = t.id
            GROUP BY p.id, p.nombre, p.descripcion, p.precio, p.tipo_producto, c.nombre, t.nombre
            ORDER BY p.nombre, c.nombre, t.nombre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos para la vista del vendedor
            $inventario_formateado = [];
            foreach ($inventario as $item) {
                $inventario_formateado[] = [
                    'producto_id' => $item['producto_id'],
                    'producto_nombre' => $item['producto_nombre'],
                    'descripcion' => $item['descripcion'],
                    'precio' => $item['precio'],
                    'tipo_producto' => $item['tipo_producto'],
                    'referencia' => 'REF-' . $item['producto_id'] . '-' . $item['talla'] . '-' . $item['color'],
                    'talla' => $item['talla'],
                    'color' => $item['color'],
                    'stock_total' => $item['stock_total']
                ];
            }
            
            return $inventario_formateado;
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerEstadisticasInventario() {
        try {
            $inventario = $this->obtenerInventario();
            
            $stats = [
                'total_productos' => count($inventario),
                'productos_disponibles' => 0,
                'productos_agotados' => 0,
                'productos_stock_bajo' => 0,
                'valor_total' => 0
            ];
            
            foreach ($inventario as $item) {
                $stock = (int)$item['stock_total'];
                $precio = (float)$item['precio'];
                
                if ($stock > 10) {
                    $stats['productos_disponibles']++;
                } elseif ($stock > 0) {
                    $stats['productos_stock_bajo']++;
                } else {
                    $stats['productos_agotados']++;
                }
                
                $stats['valor_total'] += ($stock * $precio);
            }
            
            return $stats;
        } catch (Exception $e) {
            return [
                'total_productos' => 0,
                'productos_disponibles' => 0,
                'productos_agotados' => 0,
                'productos_stock_bajo' => 0,
                'valor_total' => 0
            ];
        }
    }
    
    public static function formatPrice($price) {
        return '$' . number_format($price, 2);
    }
    
    public static function getStockStatus($stock) {
        $stock = (int)$stock;
        if ($stock <= 0) {
            return ['status' => 'danger', 'text' => 'Agotado'];
        } elseif ($stock <= 10) {
            return ['status' => 'warning', 'text' => 'Stock Bajo'];
        } else {
            return ['status' => 'success', 'text' => 'Disponible'];
        }
    }
}
?> 