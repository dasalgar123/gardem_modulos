<?php
class ControladorVentas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Obtener el siguiente número de factura
    public function obtenerSiguienteFactura() {
        try {
            $stmt = $this->pdo->query("SELECT MAX(CAST(SUBSTRING(factura, 2) AS UNSIGNED)) as ultimo_numero FROM productos_ventas WHERE factura LIKE 'F%'");
            $result = $stmt->fetch();
            return 'F' . str_pad(($result['ultimo_numero'] ?? 0) + 1, 6, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            return 'F001';
        }
    }
    
    // Obtener clientes activos
    public function obtenerClientes() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre FROM cliente ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Obtener productos disponibles
    public function obtenerProductos() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre, precio, descripcion, tipo_producto, color_id, tallas_id FROM productos ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Buscar producto por código de barras o nombre
    public function buscarProductoPorCodigo($codigo) {
        try {
            $codigo = trim($codigo);
            
            // Buscar por ID exacto primero
            $stmt = $this->pdo->prepare("SELECT id, nombre, precio, descripcion, tipo_producto FROM productos WHERE id = ?");
            $stmt->execute([$codigo]);
            $producto = $stmt->fetch();
            
            if ($producto) {
                return $producto;
            }
            
            // Si no se encuentra por ID, buscar por nombre (búsqueda parcial)
            $stmt = $this->pdo->prepare("SELECT id, nombre, precio, descripcion, tipo_producto FROM productos WHERE nombre LIKE ? ORDER BY nombre LIMIT 1");
            $stmt->execute(['%' . $codigo . '%']);
            $producto = $stmt->fetch();
            
            if ($producto) {
                return $producto;
            }
            
            // Si no se encuentra por nombre, buscar por descripción
            $stmt = $this->pdo->prepare("SELECT id, nombre, precio, descripcion, tipo_producto FROM productos WHERE descripcion LIKE ? ORDER BY nombre LIMIT 1");
            $stmt->execute(['%' . $codigo . '%']);
            $producto = $stmt->fetch();
            
            return $producto;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Obtener historial de ventas
    public function obtenerVentas() {
        try {
            $sql = "SELECT pv.*, c.nombre as cliente_nombre 
                    FROM productos_ventas pv 
                    LEFT JOIN cliente c ON pv.cliente_id = c.id 
                    ORDER BY pv.fecha DESC LIMIT 20";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Crear nueva venta con múltiples productos
    public function crearVenta($factura, $cliente_id, $productos) {
        try {
            $this->pdo->beginTransaction();
            
            // Verificar cliente
            $stmt = $this->pdo->prepare("SELECT id FROM cliente WHERE id = ?");
            $stmt->execute([$cliente_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Cliente no válido');
            }
            
            $productos_texto = [];
            $total_venta = 0;
            
            // PRIMERA PASADA: Verificar stock disponible para todos los productos
            foreach ($productos as $producto) {
                $stock_disponible = $this->obtenerStockProducto($producto['producto_id']);
                
                if ($stock_disponible < $producto['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID {$producto['producto_id']}. Stock disponible: {$stock_disponible}, solicitado: {$producto['cantidad']}");
                }
                
                if ($stock_disponible - $producto['cantidad'] < 12) {
                    throw new Exception("No se puede realizar la venta. El producto ID {$producto['producto_id']} quedaría con menos de 12 unidades en stock. Stock actual: {$stock_disponible}");
                }
            }
            
            // SEGUNDA PASADA: Procesar productos y descontar del inventario
            foreach ($productos as $producto) {
                // Verificar producto y obtener información completa incluyendo color y talla
                $stmt = $this->pdo->prepare("SELECT p.id, p.nombre, p.precio, p.color_id, p.tallas_id, c.nombre as color_nombre, t.nombre as talla_nombre 
                                           FROM productos p 
                                           LEFT JOIN colores c ON p.color_id = c.id 
                                           LEFT JOIN tallas t ON p.tallas_id = t.id 
                                           WHERE p.id = ?");
                $stmt->execute([$producto['producto_id']]);
                $producto_info = $stmt->fetch();
                
                if (!$producto_info) {
                    throw new Exception('Producto no encontrado: ' . $producto['producto_id']);
                }
                
                // Descontar del inventario
                $this->descontarInventario($producto['producto_id'], $producto['cantidad']);
                
                // Construir descripción del producto con la información del producto
                $descripcion = $producto_info['nombre'];
                if ($producto_info['color_nombre']) {
                    $descripcion .= " - Color: " . $producto_info['color_nombre'];
                }
                if ($producto_info['talla_nombre']) {
                    $descripcion .= " - Talla: " . $producto_info['talla_nombre'];
                }
                $descripcion .= " (x" . $producto['cantidad'] . ")";
                
                $productos_texto[] = $descripcion;
                $total_venta += $producto_info['precio'] * $producto['cantidad'];
            }
            
            // Insertar venta con todos los productos
            $stmt = $this->pdo->prepare("INSERT INTO productos_ventas (cliente_id, factura, productos, total, fecha, usuario_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            $productos_final = implode(" | ", $productos_texto);
            $usuario_id = $_SESSION['usuario_id'] ?? 1;
            $stmt->execute([$cliente_id, $factura, $productos_final, $total_venta, $usuario_id]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
    
    // Obtener stock actual de un producto
    public function obtenerStockProducto($producto_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(stock_actual), 0) as stock_total FROM inventario_bodega WHERE producto_id = ?");
            $stmt->execute([$producto_id]);
            $result = $stmt->fetch();
            return (int)$result['stock_total'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Descontar cantidad del inventario
    private function descontarInventario($producto_id, $cantidad) {
        try {
            // Obtener registros de inventario para este producto ordenados por fecha (FIFO)
            $stmt = $this->pdo->prepare("SELECT id, stock_actual FROM inventario_bodega WHERE producto_id = ? AND stock_actual > 0 ORDER BY id ASC");
            $stmt->execute([$producto_id]);
            $registros_inventario = $stmt->fetchAll();
            
            $cantidad_restante = $cantidad;
            
            foreach ($registros_inventario as $registro) {
                if ($cantidad_restante <= 0) break;
                
                $stock_actual = $registro['stock_actual'];
                $descuento = min($cantidad_restante, $stock_actual);
                $nuevo_stock = $stock_actual - $descuento;
                
                // Actualizar el registro de inventario
                $stmt_update = $this->pdo->prepare("UPDATE inventario_bodega SET stock_actual = ? WHERE id = ?");
                $stmt_update->execute([$nuevo_stock, $registro['id']]);
                
                $cantidad_restante -= $descuento;
            }
            
            if ($cantidad_restante > 0) {
                throw new Exception("Error: No hay suficiente stock para descontar");
            }
            
        } catch (Exception $e) {
            throw new Exception("Error al descontar inventario: " . $e->getMessage());
        }
    }
    
    // Verificar si un producto tiene stock suficiente y cumple con mínimo
    public function verificarStockDisponible($producto_id, $cantidad_solicitada) {
        $stock_actual = $this->obtenerStockProducto($producto_id);
        $stock_despues_venta = $stock_actual - $cantidad_solicitada;
        
        return [
            'stock_actual' => $stock_actual,
            'cantidad_solicitada' => $cantidad_solicitada,
            'stock_despues_venta' => $stock_despues_venta,
            'tiene_stock_suficiente' => $stock_actual >= $cantidad_solicitada,
            'cumple_minimo' => $stock_despues_venta >= 12,
            'puede_vender' => ($stock_actual >= $cantidad_solicitada && $stock_despues_venta >= 12)
        ];
    }
    
    // Obtener productos con información de stock
    public function obtenerProductosConStock() {
        try {
            $sql = "SELECT p.id, p.nombre, p.precio, p.descripcion, p.tipo_producto, p.color_id, p.tallas_id,
                           COALESCE(SUM(ib.stock_actual), 0) AS stock_total
                    FROM productos p
                    LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
                    GROUP BY p.id, p.nombre, p.precio, p.descripcion, p.tipo_producto, p.color_id, p.tallas_id
                    ORDER BY p.nombre";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Obtener colores disponibles
    public function obtenerColores() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre FROM colores ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Obtener tallas disponibles
    public function obtenerTallas() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre, categoria FROM tallas ORDER BY categoria, nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Obtener tallas por categoría
    public function obtenerTallasPorCategoria($categoria) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre FROM tallas WHERE categoria = ? ORDER BY nombre");
            $stmt->execute([$categoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Obtener información del producto incluyendo categoría
    public function obtenerProductoConDetalles($producto_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT p.*, t.categoria as categoria_talla FROM productos p 
                                        LEFT JOIN tallas t ON p.tallas_id = t.id 
                                        WHERE p.id = ?");
            $stmt->execute([$producto_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}
?> 