<?php
// Controlador para la gestión de salidas
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        // Consulta exactamente igual al sistema de almacén
        $stmt = $this->pdo->query("
            SELECT 
                ps.id,
                p.nombre as producto,
                c.nombre as color,
                t.nombre as talla,
                ps.cantidad,
                ps.fecha,
                ps.motivo,
                ps.beneficiario,
                ps.factura_remision as factura,
                ps.producto_id,
                ps.color_id,
                ps.talla_id,
                ps.bodega_id,
                ps.beneficiario_id
            FROM productos_salidas ps
            LEFT JOIN productos p ON ps.producto_id = p.id
            LEFT JOIN colores c ON ps.color_id = c.id
            LEFT JOIN tallas t ON ps.talla_id = t.id
            ORDER BY ps.fecha DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 