<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener la página solicitada
$page = $_GET['page'] ?? 'menu_principal';
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
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'productos' ? 'active' : ''; ?>" href="index.php?page=productos">
                            <i class="fas fa-box me-1"></i>Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'proveedores' ? 'active' : ''; ?>" href="index.php?page=proveedores">
                            <i class="fas fa-truck me-1"></i>Proveedores
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="movimientosDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-exchange-alt me-1"></i>Movimientos
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=entradas">
                                <i class="fas fa-arrow-down me-2"></i>Entradas</a></li>
                            <li><a class="dropdown-item" href="index.php?page=salidas">
                                <i class="fas fa-arrow-up me-2"></i>Salidas</a></li>
                            <li><a class="dropdown-item" href="index.php?page=traslados">
                                <i class="fas fa-exchange-alt me-2"></i>Traslados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=garantias">
                                <i class="fas fa-shield-alt me-2"></i>Garantías</a></li>
                            <li><a class="dropdown-item" href="index.php?page=devoluciones">
                                <i class="fas fa-undo me-2"></i>Devoluciones</a></li>
                            <li><a class="dropdown-item" href="index.php?page=compras">
                                <i class="fas fa-shopping-cart me-2"></i>Compras</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'inventario' ? 'active' : ''; ?>" href="index.php?page=inventario">
                            <i class="fas fa-clipboard-list me-1"></i>Inventario
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'reportes' ? 'active' : ''; ?>" href="index.php?page=reportes">
                            <i class="fas fa-chart-bar me-1"></i>Reportes
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
        <?php if ($page == 'menu_principal' || $page == ''): ?>
            <!-- Panel Principal -->
            <div class="row">
                <div class="col-12">
                    <h2><i class="fas fa-tachometer-alt"></i> Panel Principal</h2>
                    <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> (<?php echo ucfirst($_SESSION['usuario_rol']); ?>)</p>
                </div>
            </div>
            
            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-box"></i> Productos</h5>
                            <h3>0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-clipboard-list"></i> Inventario</h5>
                            <h3>0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-truck"></i> Entregas</h5>
                            <h3>0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5><i class="fas fa-chart-bar"></i> Reportes</h5>
                            <h3>0</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <a href="?page=productos" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-box fa-2x mb-2"></i><br>Productos
                                    </a>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <a href="?page=inventario" class="btn btn-outline-success w-100">
                                        <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>Inventario
                                    </a>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <a href="?page=entradas" class="btn btn-outline-info w-100">
                                        <i class="fas fa-arrow-down fa-2x mb-2"></i><br>Entradas
                                    </a>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <a href="?page=salidas" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-arrow-up fa-2x mb-2"></i><br>Salidas
                                    </a>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <a href="?page=reportes" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i><br>Reportes
                                    </a>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <a href="?page=proveedores" class="btn btn-outline-dark w-100">
                                        <i class="fas fa-truck fa-2x mb-2"></i><br>Proveedores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($page == 'productos'): ?>
            <?php include 'vista/productos.php'; ?>
        <?php elseif ($page == 'proveedores'): ?>
            <?php include 'vista/proveedores.php'; ?>
        <?php elseif ($page == 'salidas'): ?>
            <?php include 'vista/salidas.php'; ?>
        <?php elseif ($page == 'inventario'): ?>
            <?php include 'vista/inventario.php'; ?>
        <?php elseif ($page == 'entradas'): ?>
            <?php include 'vista/entradas.php'; ?>
        <?php else: ?>
            <!-- Otras páginas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-cog"></i> <?php echo ucfirst($page); ?></h4>
                        </div>
                        <div class="card-body">
                            <p>Página: <strong><?php echo ucfirst($page); ?></strong></p>
                            <p>Esta funcionalidad estará disponible pronto.</p>
                            <a href="?page=menu_principal" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Volver al Menú Principal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html> 