<?php
// Controlador básico para entradas - Usa la estructura correcta
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        try {
            // Usar la tabla productos_entradas con la estructura correcta
            $stmt = $this->pdo->query("
                SELECT 
                    pe.*,
                    p.nombre as producto_nombre,
                    b.nombre as bodega_nombre
                FROM productos_entradas pe
                LEFT JOIN productos p ON pe.producto_id = p.id
                LEFT JOIN bodega b ON pe.bodega_id = b.id
                ORDER BY pe.fecha DESC
                LIMIT 50
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function crearEntrada($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO productos_entradas (
                    producto_id, 
                    bodega_id, 
                    cantidad, 
                    fecha, 
                    motivo, 
                    beneficiario_tipo, 
                    beneficiario_id, 
                    factura_remision,
                    beneficiario
                ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $datos['producto_id'],
                $datos['bodega_id'] ?? 1,
                $datos['cantidad'],
                $datos['motivo'] ?? 'compra',
                $datos['beneficiario_tipo'] ?? 'proveedor',
                $datos['beneficiario_id'] ?? 1,
                $datos['factura_remision'] ?? '',
                $datos['beneficiario'] ?? 'Proveedor'
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 