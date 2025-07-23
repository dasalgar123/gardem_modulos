<?php
// Controlador básico para salidas
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        $stmt = $this->pdo->query("SELECT * FROM salidas ORDER BY fecha DESC");
        return $stmt->fetchAll();
    }
    
    public function crearSalida($datos) {
        $stmt = $this->pdo->prepare("
            INSERT INTO salidas (producto_id, cantidad, fecha, motivo) 
            VALUES (?, ?, NOW(), ?)
        ");
        return $stmt->execute([$datos['producto_id'], $datos['cantidad'], $datos['motivo']]);
    }
}
?> 