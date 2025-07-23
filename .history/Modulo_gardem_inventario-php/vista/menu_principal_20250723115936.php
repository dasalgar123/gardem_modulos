<?php
// Menú Principal del Sistema de Almacenista

// Obtener estadísticas generales usando las funciones de database.php
require_once __DIR__ . '/../config/database.php';
$stats = obtenerEstadisticas();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-tachometer-alt me-2"></i>Panel de Control - Almacenista
        </h1>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Productos
                        </div>
                        <div class="text-xs text-muted">
                            En Catálogo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_productos']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Proveedores
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_proveedores']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Entradas Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['entradas_hoy']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Salidas Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['salidas_hoy']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de inventario -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Productos Agotados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['agotados']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Stock Bajo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['stock_bajo']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>
                    Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=entradas" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-arrow-down me-2"></i>
                            Nueva Entrada
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=salidas" class="btn btn-danger btn-lg w-100">
                            <i class="fas fa-arrow-up me-2"></i>
                            Nueva Salida
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=traslados" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Nuevo Traslado
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=inventario" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Ver Inventario
                        </a>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=productos" class="btn btn-secondary btn-lg w-100">
                            <i class="fas fa-box me-2"></i>
                            Gestionar Productos
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=proveedores" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-truck me-2"></i>
                            Gestionar Proveedores
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=garantias" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-shield-alt me-2"></i>
                            Garantías
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=reportes" class="btn btn-dark btn-lg w-100">
                            <i class="fas fa-chart-bar me-2"></i>
                            Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información del sistema -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user me-2"></i>Usuario Actual</h6>
                        <p class="mb-2">
                            <strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['usuario_email']); ?><br>
                            <strong>Rol:</strong> <?php echo ucfirst($_SESSION['usuario_rol']); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar me-2"></i>Información del Sistema</h6>
                        <p class="mb-2">
                            <strong>Fecha:</strong> <?php echo date('d/m/Y'); ?><br>
                            <strong>Hora:</strong> <?php echo date('H:i:s'); ?><br>
                            <strong>Versión:</strong> Gardem Inventario v1.0
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 