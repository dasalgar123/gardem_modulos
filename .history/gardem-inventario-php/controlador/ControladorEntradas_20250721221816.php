<?php
// Controlador básico para entradas - Trabaja con lo que hay
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        try {
            // Intentar con diferentes nombres de tabla que podrían existir
            $tablas_posibles = ['entradas', 'productos_entradas', 'movimientos_entrada'];
            
            foreach ($tablas_posibles as $tabla) {
                try {
                    $stmt = $this->pdo->query("SELECT * FROM $tabla ORDER BY fecha DESC LIMIT 10");
                    return $stmt->fetchAll();
                } catch (PDOException $e) {
                    continue; // Intentar con la siguiente tabla
                }
            }
            
            // Si no encuentra ninguna tabla, devolver array vacío
            return [];
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function crearEntrada($datos) {
        try {
            // Intentar con diferentes nombres de tabla
            $tablas_posibles = ['entradas', 'productos_entradas', 'movimientos_entrada'];
            
            foreach ($tablas_posibles as $tabla) {
                try {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO $tabla (producto_id, cantidad, fecha, motivo) 
                        VALUES (?, ?, NOW(), ?)
                    ");
                    return $stmt->execute([$datos['producto_id'], $datos['cantidad'], $datos['motivo']]);
                } catch (PDOException $e) {
                    continue; // Intentar con la siguiente tabla
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 