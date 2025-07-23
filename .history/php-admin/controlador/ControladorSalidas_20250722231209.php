<?php
// Controlador para la gestión de salidas
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        // Consultar salidas desde productos_salidas, uniendo con productos, colores y tallas
        $stmt = $this->pdo->query('
            SELECT 
                s.id, 
                s.producto_id, 
                p.nombre AS producto, 
                c.nombre AS color,
                t.nombre AS talla,
                s.cantidad, 
                s.fecha, 
                s.motivo, 
                s.beneficiario,
                s.factura_remision 
            FROM productos_salidas s 
            LEFT JOIN productos p ON s.producto_id = p.id 
            LEFT JOIN colores c ON s.color_id = c.id 
            LEFT JOIN tallas t ON s.talla_id = t.id 
            ORDER BY s.fecha DESC
        ');
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 