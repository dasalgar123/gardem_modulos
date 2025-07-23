<?php
class ModeloInventario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerStock($producto_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT cantidad FROM inventario 
                WHERE producto_id = ?
            ");
            $stmt->execute([$producto_id]);
            $result = $stmt->fetch();
            return $result ? $result['cantidad'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function actualizarStock($producto_id, $cantidad) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO inventario (producto_id, cantidad) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE cantidad = ?
            ");
            return $stmt->execute([$producto_id, $cantidad, $cantidad]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function obtenerProductosStockBajo($limite = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.nombre, i.cantidad 
                FROM inventario i 
                JOIN productos p ON i.producto_id = p.id 
                WHERE i.cantidad < ?
                ORDER BY i.cantidad ASC
            ");
            $stmt->execute([$limite]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerProductosAgotados() {
        try {
            $stmt = $this->pdo->query("
                SELECT p.nombre, i.cantidad 
                FROM inventario i 
                JOIN productos p ON i.producto_id = p.id 
                WHERE i.cantidad = 0
                ORDER BY p.nombre
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerResumenInventario() {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_productos,
                    SUM(cantidad) as stock_total,
                    COUNT(CASE WHEN cantidad = 0 THEN 1 END) as agotados,
                    COUNT(CASE WHEN cantidad < 10 AND cantidad > 0 THEN 1 END) as stock_bajo
                FROM inventario
            ");
            return $stmt->fetch();
        } catch (Exception $e) {
            return [
                'total_productos' => 0,
                'stock_total' => 0,
                'agotados' => 0,
                'stock_bajo' => 0
            ];
        }
    }
}
?> 