<?php
class ControladorEntradas {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerEntradas() {
        try {
            $sql = "SELECT pe.*, p.nombre as producto_nombre, b.nombre as bodega_nombre 
                    FROM productos_entradas pe 
                    LEFT JOIN productos p ON pe.producto_id = p.id 
                    LEFT JOIN bodega b ON pe.bodega_id = b.id 
                    ORDER BY pe.fecha DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function guardarEntrada() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        try {
            $producto_id = $_POST['producto_id'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 0;
            $bodega_id = $_POST['bodega_id'] ?? null;
            
            if (empty($producto_id) || empty($cantidad)) {
                echo "<script>alert('Error: Producto y cantidad son obligatorios'); window.location.href='index.php?page=entradas';</script>";
                return;
            }
            
            $sql = "INSERT INTO productos_entradas (producto_id, cantidad, bodega_id, fecha, motivo, beneficiario) 
                    VALUES (?, ?, ?, NOW(), 'compra', 'Sistema')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id, $cantidad, $bodega_id]);
            
            echo "<script>alert('Entrada guardada correctamente'); window.location.href='index.php?page=entradas&success=1';</script>";
            
        } catch (Exception $e) {
            echo "<script>alert('Error al guardar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=entradas';</script>";
        }
    }
}
?> 