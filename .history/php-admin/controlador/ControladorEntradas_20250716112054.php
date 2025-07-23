<?php
// Controlador para la gestión de entradas
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        // Consultar entradas desde productos_entradas, uniendo con productos para mostrar el nombre
        $stmt = $this->pdo->query('SELECT e.id, e.producto_id, p.nombre AS producto, e.cantidad, e.fecha, e.motivo, e.beneficiario_tipo, e.beneficiario_id, e.factura_remision, e.beneficiario FROM productos_entradas e LEFT JOIN productos p ON e.producto_id = p.id ORDER BY e.fecha DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 