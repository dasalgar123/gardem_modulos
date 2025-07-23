<?php
// Controlador para la gestión de entradas
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        // Consulta exactamente igual al sistema de almacén
        $stmt = $this->pdo->query("
            SELECT pe.id, pe.producto_id, pe.cantidad, pe.fecha, pe.motivo, pe.beneficiario, pe.factura_remision, 
                p.nombre as producto, c.nombre as color, t.nombre as talla
            FROM productos_entradas pe
            LEFT JOIN productos p ON pe.producto_id = p.id
            LEFT JOIN colores c ON pe.color_id = c.id
            LEFT JOIN tallas t ON pe.talla_id = t.id
            ORDER BY pe.fecha DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 