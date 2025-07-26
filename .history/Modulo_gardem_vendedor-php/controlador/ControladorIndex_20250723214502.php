<?php
class ControladorIndex {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function verificarSesion() {
        return isset($_SESSION['usuario_id']);
    }
    
    public function obtenerPaginaSolicitada() {
        return isset($_GET['page']) ? $_GET['page'] : 'menu_principal';
    }
    
    public function obtenerPaginasValidas() {
        return ['menu_principal', 'ventas', 'pedidos', 'clientes', 'productos', 'inventario', 'perfil', 'logout'];
    }
    
    public function incluirPagina($page) {
        $validPages = $this->obtenerPaginasValidas();
        
        if (in_array($page, $validPages)) {
            $filePath = __DIR__ . '/../vista/' . $page . '.php';
            if (file_exists($filePath)) {
                try {
                    // Hacer disponibles las variables globales en el archivo incluido
                    $pdo = $this->pdo; // Make $pdo available to included files
                    include $filePath;
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error al cargar la página: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning">Página en construcción: ' . ucfirst($page) . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Página no encontrada</div>';
        }
    }
    
    public function obtenerUsuarioActual() {
        if ($this->verificarSesion()) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol' => $_SESSION['usuario_rol']
            ];
        }
        return null;
    }
    
    public function cerrarSesion() {
        session_destroy();
        session_start();
    }
} 