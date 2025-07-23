<?php
// Controlador básico para entradas - Usa lo que YA TIENES
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        try {
            // Ver qué tablas tienes realmente
            $stmt = $this->pdo->query("SHOW TABLES");
            $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Buscar tabla que tenga que ver con entradas
            foreach ($tablas as $tabla) {
                if (strpos($tabla, 'entrada') !== false || strpos($tabla, 'movimiento') !== false) {
                    $stmt = $this->pdo->query("SELECT * FROM $tabla ORDER BY fecha DESC LIMIT 10");
                    return $stmt->fetchAll();
                }
            }
            
            // Si no encuentra, devolver array vacío
            return [];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function crearEntrada($datos) {
        try {
            // Ver qué tablas tienes realmente
            $stmt = $this->pdo->query("SHOW TABLES");
            $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Buscar tabla que tenga que ver con entradas
            foreach ($tablas as $tabla) {
                if (strpos($tabla, 'entrada') !== false || strpos($tabla, 'movimiento') !== false) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO $tabla (producto_id, cantidad, fecha, motivo) 
                        VALUES (?, ?, NOW(), ?)
                    ");
                    return $stmt->execute([$datos['producto_id'], $datos['cantidad'], $datos['motivo']]);
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 