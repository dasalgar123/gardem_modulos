<?php
class ModeloProducto {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM productos ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerPorId($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function crear($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO productos (nombre, descripcion, precio, categoria_id) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio'],
                $datos['categoria_id']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function actualizar($id, $datos) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio'],
                $datos['categoria_id'],
                $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function eliminar($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 