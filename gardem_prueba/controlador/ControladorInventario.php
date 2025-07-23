<?php
// Controlador básico para inventario
class ControladorInventario {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerInventario() {
        $stmt = $this->pdo->query("SELECT * FROM inventario");
        return $stmt->fetchAll();
    }
    
    public function obtenerProductos() {
        $stmt = $this->pdo->query("SELECT * FROM productos");
        return $stmt->fetchAll();
    }
}
?> 