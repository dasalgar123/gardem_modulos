<?php
class ControladorAuth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND rol = 'administrador'");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $password === $usuario['contraseña']) {
                // Iniciar sesión
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function loginAlmacenista($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND rol = 'almacenista'");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $password === $usuario['contraseña']) {
                // Iniciar sesión como almacenista
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_bodega_id'] = $usuario['bodega_id'] ?? null;
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        return true;
    }
    
    public function verificarSesion() {
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_rol'] === 'administrador';
    }
    
    public function verificarSesionAlmacenista() {
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_rol'] === 'almacenista';
    }
    
    public function obtenerUsuarioActual() {
        if ($this->verificarSesion() || $this->verificarSesionAlmacenista()) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol' => $_SESSION['usuario_rol'],
                'bodega_id' => $_SESSION['usuario_bodega_id'] ?? null
            ];
        }
        return null;
    }
    
    public function cambiarPassword($usuario_id, $password_actual, $password_nuevo) {
        try {
            // Verificar password actual
            $stmt = $this->pdo->prepare("SELECT contraseña FROM usuario WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario || $password_actual !== $usuario['contraseña']) {
                return false;
            }
            
            // Cambiar password
            $stmt = $this->pdo->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
            $stmt->execute([$password_nuevo, $usuario_id]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function obtenerBodegas() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM bodega ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerBodegaAlmacenista($almacenista_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT bodega_id FROM usuario WHERE id = ? AND rol = 'almacenista'");
            $stmt->execute([$almacenista_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado['bodega_id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?> 