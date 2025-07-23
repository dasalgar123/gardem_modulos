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
            $codigo_producto = $_POST['codigo_producto'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 0;
            $cliente_id = $_POST['cliente_id'] ?? null;
            $color_id = $_POST['color_id'] ?? null;
            $talla_id = $_POST['talla_id'] ?? null;
            $bodega_id = $_POST['bodega_id'] ?? null;
            $motivo = $_POST['motivo'] ?? 'venta';
            
            if (empty($codigo_producto) || empty($cantidad)) {
                echo "<script>alert('Error: Código de producto y cantidad son obligatorios'); window.location.href='index.php?page=salidas';</script>";
                return;
            }
            
            // Convertir código a producto_id (el código es el ID)
            $producto_id = $codigo_producto;
            
            // Obtener nombre del cliente por referencia
            $beneficiario = 'Sistema';
            $beneficiario_tipo = 'cliente';
            $beneficiario_id = $cliente_id;
            
            if ($cliente_id) {
                try {
                    $stmt = $this->pdo->prepare("SELECT nombre FROM cliente WHERE id = ?");
                    $stmt->execute([$cliente_id]);
                    $cliente = $stmt->fetch();
                    $beneficiario = $cliente['nombre'] ?? 'Sistema';
                } catch (Exception $e) {
                    $beneficiario = 'Sistema';
                }
            }
            
            $sql = "INSERT INTO productos_salidas (producto_id, cantidad, bodega_id, fecha, motivo, beneficiario_tipo, beneficiario_id, beneficiario, factura_remision, color_id, talla_id) 
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'SAL-001', ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id, $cantidad, $bodega_id, $motivo, $beneficiario_tipo, $beneficiario_id, $beneficiario, $color_id, $talla_id]);
            
            echo "<script>alert('Salida guardada correctamente'); window.location.href='index.php?page=salidas&success=1';</script>";
            
        } catch (Exception $e) {
            echo "<script>alert('Error al guardar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=salidas';</script>";
        }
    }
}
?> 