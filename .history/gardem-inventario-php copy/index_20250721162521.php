<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'controlador/ControladorIndex.php';
require_once 'controlador/ControladorAuth.php';

$controlador = new ControladorIndex($pdo);

// Verificar si el usuario está logueado como almacenista
if (!$controlador->verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Obtener la página solicitada
$page = $controlador->obtenerPaginaSolicitada();

// Manejar logout
if ($page === 'logout') {
    $controlador->cerrarSesion();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Almacenista - <?php echo ucfirst($page); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-warehouse me-2"></i>
                Sistema de Almacenista
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'menu_principal' ? 'active' : ''; ?>" href="index.php?page=menu_principal">
                            <i class="fas fa-tachometer-alt me-1"></i>Menú Principal
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($page, ['entradas', 'compras', 'garantias', 'devoluciones', 'traslados', 'ver_todo']) ? 'active' : ''; ?>" href="#" id="entradasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-arrow-down me-1"></i>Entradas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=compras">
                                <i class="fas fa-shopping-cart me-2"></i>Compras</a></li>
                            <li><a class="dropdown-item" href="index.php?page=garantias">
                                <i class="fas fa-shield-alt me-2"></i>Garantías</a></li>
                            <li><a class="dropdown-item" href="index.php?page=devoluciones">
                                <i class="fas fa-undo me-2"></i>Devoluciones</a></li>
                            <li><a class="dropdown-item" href="index.php?page=traslados">
                                <i class="fas fa-exchange-alt me-2"></i>Traslados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=ver_todo">
                                <i class="fas fa-list me-2"></i>Ver Todo</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($page, ['salidas', 'ventas']) ? 'active' : ''; ?>" href="#" id="salidasDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-arrow-up me-1"></i>Salidas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=ventas">
                                <i class="fas fa-cash-register me-2"></i>Ventas</a></li>
                            <li><a class="dropdown-item" href="index.php?page=garantias">
                                <i class="fas fa-shield-alt me-2"></i>Garantías</a></li>
                            <li><a class="dropdown-item" href="index.php?page=devoluciones">
                                <i class="fas fa-undo me-2"></i>Devoluciones</a></li>
                            <li><a class="dropdown-item" href="index.php?page=traslados">
                                <i class="fas fa-exchange-alt me-2"></i>Traslados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=ver_todo">
                                <i class="fas fa-list me-2"></i>Ver Todo</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'inventario' ? 'active' : ''; ?>" href="index.php?page=inventario">
                            <i class="fas fa-clipboard-list me-1"></i>Inventario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'ver_ventas' ? 'active' : ''; ?>" href="index.php?page=ver_ventas">
                            <i class="fas fa-chart-line me-1"></i>Ver Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'entregar' ? 'active' : ''; ?>" href="index.php?page=entregar">
                            <i class="fas fa-truck me-1"></i>Entregas
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=perfil">
                                <i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?page=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid mt-4">
        <?php $controlador->incluirPagina($page); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html> 