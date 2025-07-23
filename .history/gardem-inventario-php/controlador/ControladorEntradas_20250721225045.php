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
    
    // Método para guardar entrada directamente desde el formulario
    public function guardarEntrada() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'producto_id' => $_POST['producto_id'] ?? null,
                'bodega_id' => $_POST['bodega_id'] ?? 1,
                'cantidad' => $_POST['cantidad'] ?? 0,
                'motivo' => $_POST['motivo'] ?? 'compra',
                'beneficiario_tipo' => $_POST['beneficiario_tipo'] ?? 'proveedor',
                'beneficiario_id' => $_POST['beneficiario_id'] ?? 1,
                'factura_remision' => $_POST['factura_remision'] ?? '',
                'beneficiario' => $_POST['beneficiario'] ?? 'Proveedor'
            ];
            
            if ($this->crearEntrada($datos)) {
                // En lugar de header(), usar JavaScript
                echo "<script>alert('Entrada guardada exitosamente'); window.location.href='index.php?page=entradas&success=1';</script>";
            } else {
                echo "<script>alert('Error al guardar entrada'); window.location.href='index.php?page=entradas&error=Error al guardar entrada';</script>";
            }
        }
    }
}
?> 