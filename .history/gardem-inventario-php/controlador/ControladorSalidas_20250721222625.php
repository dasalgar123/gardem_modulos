<?php
// Controlador básico para salidas - Usa la estructura correcta
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        try {
            // Usar la tabla productos_salidas con la estructura correcta
            $stmt = $this->pdo->query("
                SELECT 
                    ps.*,
                    p.nombre as producto_nombre,
                    c.nombre as cliente_nombre
                FROM productos_salidas ps
                LEFT JOIN productos p ON ps.producto_id = p.id
                LEFT JOIN cliente c ON ps.cliente_id = c.id
                ORDER BY ps.fecha DESC
                LIMIT 50
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function crearSalida($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO productos_salidas (
                    producto_id, 
                    destinatario_tipo, 
                    destinatario_id, 
                    cantidad, 
                    fecha, 
                    motivo, 
                    cliente_id, 
                    factura_remision,
                    talla_id,
                    color_id
                ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $datos['producto_id'],
                $datos['destinatario_tipo'] ?? 'cliente',
                $datos['destinatario_id'] ?? 0,
                $datos['cantidad'],
                $datos['motivo'] ?? 'venta',
                $datos['cliente_id'] ?? 1,
                $datos['factura_remision'] ?? '',
                $datos['talla_id'] ?? null,
                $datos['color_id'] ?? null
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 