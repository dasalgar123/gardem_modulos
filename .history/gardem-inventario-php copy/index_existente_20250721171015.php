<?php
session_start();

// Incluir configuración de base de datos existente
require_once 'config/database_existing.php';

// Incluir controlador
require_once 'controlador/ControladorExistente.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_existente.php');
    exit();
}

// Crear instancia del controlador
$controlador = new ControladorExistente($pdo);

// Verificar si el usuario tiene permisos de almacenista
if (!$controlador->verificarSesion()) {
    session_destroy();
    header('Location: login_existente.php?error=no_permisos');
    exit();
}

// Obtener usuario actual
$usuario = $controlador->obtenerUsuarioActual();

// Obtener página solicitada
$page = $controlador->obtenerPaginaSolicitada();

// Procesar logout
if ($page === 'logout' || $page === 'cerrar_sesion') {
    $controlador->cerrarSesion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - GARDEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.0/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #27ae60;
            --secondary-color: #2ecc71;
            --accent-color: #3498db;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--accent-color) 0%, #2980b9 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stats-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 10px;
            margin-left: 15px;
        }
        
        .user-info small {
            color: rgba(255,255,255,0.8);
        }
        
        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .floating-action .btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index_existente.php">
                <i class="fas fa-warehouse"></i> GARDEM Inventario
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="user-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-2"></i>
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                            <small><?php echo ucfirst($usuario['rol']); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="nav-item dropdown ms-3">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=perfil"><i class="fas fa-user"></i> Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?page=logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="?page=menu_principal" class="nav-link <?php echo $page === 'menu_principal' ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt"></i> Panel Principal
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=ver_ventas" class="nav-link <?php echo $page === 'ver_ventas' ? 'active' : ''; ?>">
                                <i class="fas fa-shopping-cart"></i> Ver Ventas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=entregar" class="nav-link <?php echo $page === 'entregar' ? 'active' : ''; ?>">
                                <i class="fas fa-truck"></i> Gestionar Entregas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=ventas" class="nav-link <?php echo $page === 'ventas' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-line"></i> Todas las Ventas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=ver_todo" class="nav-link <?php echo $page === 'ver_todo' ? 'active' : ''; ?>">
                                <i class="fas fa-list"></i> Ver Todo
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=actualizar_inventario" class="nav-link <?php echo $page === 'actualizar_inventario' ? 'active' : ''; ?>">
                                <i class="fas fa-edit"></i> Actualizar Inventario
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="?page=inventario_en_linea" class="nav-link <?php echo $page === 'inventario_en_linea' ? 'active' : ''; ?>">
                                <i class="fas fa-sync"></i> Inventario en Línea
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php $controlador->incluirPagina($page); ?>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickActionsModal">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Quick Actions Modal -->
    <div class="modal fade" id="quickActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Acciones Rápidas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="?page=entregar" class="btn btn-outline-primary w-100">
                                <i class="fas fa-truck"></i><br>Entregas
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="?page=actualizar_inventario" class="btn btn-outline-success w-100">
                                <i class="fas fa-edit"></i><br>Actualizar Stock
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="?page=ver_ventas" class="btn btn-outline-info w-100">
                                <i class="fas fa-shopping-cart"></i><br>Ver Ventas
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="?page=inventario_en_linea" class="btn btn-outline-warning w-100">
                                <i class="fas fa-sync"></i><br>Sincronizar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.0/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.0/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.0/i18n/es-ES.json'
                },
                responsive: true,
                pageLength: 25
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Confirm actions
        function confirmAction(message) {
            return confirm(message || '¿Estás seguro de realizar esta acción?');
        }
    </script>
</body>
</html> 