<?php
// Controlador para la vista de inventario del vendedor (solo lectura)
class ControladorInventario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerInventario() {
        try {
            // Obtener productos base
            $sql = "SELECT 
                p.id AS producto_id,
                p.nombre AS producto_nombre,
                p.descripcion,
                p.precio,
                p.tipo_producto,
                COALESCE(ib.stock_actual, 0) AS stock_total
            FROM productos p
            LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
            ORDER BY p.nombre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generar variantes por talla y color
            $inventario = [];
            $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
            $colores = ['Negro', 'Blanco', 'Azul', 'Rojo', 'Verde', 'Gris'];
            
            foreach ($productos as $producto) {
                foreach ($tallas as $talla) {
                    foreach ($colores as $color) {
                        $inventario[] = [
                            'producto_id' => $producto['producto_id'],
                            'producto_nombre' => $producto['producto_nombre'],
                            'descripcion' => $producto['descripcion'],
                            'precio' => $producto['precio'],
                            'tipo_producto' => $producto['tipo_producto'],
                            'stock_total' => $producto['stock_total'],
                            'talla' => $talla,
                            'color' => $color,
                            'referencia' => 'REF-' . $producto['producto_id'] . '-' . $talla . '-' . $color
                        ];
                    }
                }
            }
            
            return $inventario;
        } catch (Exception $e) {
            error_log("Error en obtenerInventario: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDatosInventario() {
        $error_inventario = null;
        $inventario = [];
        
        try {
            // Obtener filtros
            $filtro_producto = isset($_GET['producto']) ? $_GET['producto'] : '';
            $filtro_stock = isset($_GET['stock']) ? $_GET['stock'] : '';

            // Consulta similar al módulo de inventario pero adaptada para vendedor
            $sql = "
                SELECT 
                    p.id as producto_id,
                    p.nombre as producto_nombre,
                    p.descripcion,
                    p.tipo_producto,
                    p.precio,
                    c.nombre as color,
                    t.nombre as talla,
                    COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) as total_entradas,
                    COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0) as total_salidas,
                    (COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0)) as saldo
                FROM productos p
                LEFT JOIN productos_entradas pe ON p.id = pe.producto_id
                LEFT JOIN productos_salidas ps ON p.id = ps.producto_id 
                    AND (pe.color_id = ps.color_id OR (pe.color_id IS NULL AND ps.color_id IS NULL))
                    AND (pe.talla_id = ps.talla_id OR (pe.talla_id IS NULL AND ps.talla_id IS NULL))
                LEFT JOIN colores c ON pe.color_id = c.id
                LEFT JOIN tallas t ON pe.talla_id = t.id
            ";
            
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
            
            $sql .= " GROUP BY p.id, p.nombre, p.descripcion, p.tipo_producto, p.precio, c.nombre, t.nombre, pe.color_id, pe.talla_id";
            $sql .= " ORDER BY p.nombre, c.nombre, t.nombre";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay datos con entradas, mostrar productos base
            if (empty($inventario)) {
                $sql_base = "
                    SELECT 
                        p.id as producto_id,
                        p.nombre as producto_nombre,
                        p.descripcion,
                        p.tipo_producto,
                        p.precio,
                        'N/A' as color,
                        'N/A' as talla,
                        0 as total_entradas,
                        0 as total_salidas,
                        0 as saldo
                    FROM productos p
                ";
                
                if ($filtro_producto) {
                    $sql_base .= " WHERE p.nombre LIKE ?";
                }
                
                $sql_base .= " ORDER BY p.nombre";
                
                $stmt_base = $this->pdo->prepare($sql_base);
                if ($filtro_producto) {
                    $stmt_base->execute([$filtro_producto]);
                } else {
                    $stmt_base->execute();
                }
                $inventario = $stmt_base->fetchAll(PDO::FETCH_ASSOC);
            }
            
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
    
    public function obtenerEstadisticasInventario() {
        try {
            // Obtener estadísticas reales de la base de datos
            $sql = "SELECT 
                COUNT(*) as total_productos,
                SUM(CASE WHEN ib.stock_actual > 10 THEN 1 ELSE 0 END) as productos_disponibles,
                SUM(CASE WHEN ib.stock_actual BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as productos_stock_bajo,
                SUM(CASE WHEN ib.stock_actual <= 0 THEN 1 ELSE 0 END) as productos_agotados,
                SUM(ib.stock_actual * p.precio) as valor_total
            FROM productos p
            LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay datos, retornar valores por defecto
            if (!$stats) {
                return [
                    'total_productos' => 0,
                    'productos_disponibles' => 0,
                    'productos_stock_bajo' => 0,
                    'productos_agotados' => 0,
                    'valor_total' => 0
                ];
            }
            
            return [
                'total_productos' => (int)$stats['total_productos'],
                'productos_disponibles' => (int)$stats['productos_disponibles'],
                'productos_stock_bajo' => (int)$stats['productos_stock_bajo'],
                'productos_agotados' => (int)$stats['productos_agotados'],
                'valor_total' => (float)$stats['valor_total']
            ];
        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasInventario: " . $e->getMessage());
            return [
                'total_productos' => 0,
                'productos_disponibles' => 0,
                'productos_stock_bajo' => 0,
                'productos_agotados' => 0,
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
    
    // Función para obtener el color del stock (igual que en admin)
    public static function getStockColor($stock) {
        if ($stock <= 0) return 'stock-agotado';
        if ($stock <= 10) return 'stock-bajo';
        return 'stock-disponible';
    }
}
?> 