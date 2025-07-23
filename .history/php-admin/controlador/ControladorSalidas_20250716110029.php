<?php
// Controlador para la gestión de salidas
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        // Consultar salidas desde productos_salidas, uniendo con productos para mostrar el nombre
        $stmt = $this->pdo->query('SELECT s.id, s.producto_id, p.nombre AS producto, s.venta_id, s.destinatario_tipo, s.destinatario_id, s.cantidad, s.fecha, s.motivo, s.cliente_id, s.factura_remision FROM productos_salidas s LEFT JOIN productos p ON s.producto_id = p.id ORDER BY s.fecha DESC');
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 