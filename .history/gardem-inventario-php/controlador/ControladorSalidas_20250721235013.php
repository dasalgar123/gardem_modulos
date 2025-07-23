<?php
// Controlador básico para salidas - Usa la estructura correcta
class ControladorSalidas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerSalidas() {
        try {
            // Usar la tabla productos_salidas con la estructura correcta
            $sql = "SELECT ps.*, p.nombre as producto_nombre, c.nombre as cliente_nombre 
                    FROM productos_salidas ps 
                    LEFT JOIN productos p ON ps.producto_id = p.id 
                    LEFT JOIN cliente c ON ps.cliente_id = c.id 
                    ORDER BY ps.fecha DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function guardarSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        try {
            $producto_id = $_POST['producto_id'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 0;
            $cliente_id = $_POST['cliente_id'] ?? null;
            
            if (empty($producto_id) || empty($cantidad)) {
                echo "<script>alert('Error: Producto y cantidad son obligatorios'); window.location.href='index.php?page=salidas';</script>";
                return;
            }
            
            $sql = "INSERT INTO productos_salidas (producto_id, cantidad, cliente_id, fecha, motivo, factura_remision) 
                    VALUES (?, ?, ?, NOW(), 'venta', 'SAL-001')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id, $cantidad, $cliente_id]);
            
            echo "<script>alert('Salida guardada correctamente'); window.location.href='index.php?page=salidas&success=1';</script>";
            
        } catch (Exception $e) {
            echo "<script>alert('Error al guardar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=salidas';</script>";
        }
    }
}
?> 