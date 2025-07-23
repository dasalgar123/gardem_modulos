<?php
class ControladorProveedores {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Obtener todos los proveedores
    public function obtenerProveedores() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM proveedor ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Obtener un proveedor por ID
    public function obtenerProveedor($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM proveedor WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Crear nuevo proveedor
    public function crearProveedor($datos) {
        try {
            $sql = "INSERT INTO proveedor (nombre, telefono, correo, direccion, fecha_creacion) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $datos['nombre'],
                $datos['telefono'],
                $datos['correo'],
                $datos['direccion']
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Actualizar proveedor
    public function actualizarProveedor($id, $datos) {
        try {
            $sql = "UPDATE proveedor SET nombre = ?, telefono = ?, correo = ?, direccion = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $datos['nombre'],
                $datos['telefono'],
                $datos['correo'],
                $datos['direccion'],
                $id
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Eliminar proveedor
    public function eliminarProveedor($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM proveedor WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 