<?php
// Controlador básico para entradas
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        $stmt = $this->pdo->query("SELECT * FROM entradas ORDER BY fecha DESC");
        return $stmt->fetchAll();
    }
    
    public function crearEntrada($datos) {
        $stmt = $this->pdo->prepare("
            INSERT INTO entradas (producto_id, cantidad, fecha, motivo) 
            VALUES (?, ?, NOW(), ?)
        ");
        return $stmt->execute([$datos['producto_id'], $datos['cantidad'], $datos['motivo']]);
    }
}
?> 