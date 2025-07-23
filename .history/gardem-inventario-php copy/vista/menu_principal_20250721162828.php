<?php
// Menú Principal del Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener estadísticas generales
$stats = $controlador->obtenerEstadisticasGenerales();
$movimientos_recientes = $controlador->obtenerMovimientosRecientes(5);
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-warehouse me-2"></i>Panel de Control - Almacenista
        </h1>
    </div>
</div>

<!-- Tarjetas de estadísticas principales -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Productos
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

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pendientes Entrega
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['pendientes_entrega']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
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

<!-- Acciones principales según el flujo del almacenista -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-route me-2"></i>
                    Flujo Principal del Almacenista
                </h6>
            </div>
            <div class="card-body">
                <!-- Primera fila: Entradas -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-arrow-down me-2"></i>ENTRADAS
                        </h6>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=compras" class="btn btn-success w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Compras
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=garantias" class="btn btn-success w-100">
                            <i class="fas fa-shield-alt me-2"></i>Garantías
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=devoluciones" class="btn btn-success w-100">
                            <i class="fas fa-undo me-2"></i>Devoluciones
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=traslados" class="btn btn-success w-100">
                            <i class="fas fa-exchange-alt me-2"></i>Traslados
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=ver_todo" class="btn btn-success w-100">
                            <i class="fas fa-list me-2"></i>Ver Todo
                        </a>
                    </div>
                </div>

                <!-- Segunda fila: Salidas -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-danger mb-3">
                            <i class="fas fa-arrow-up me-2"></i>SALIDAS
                        </h6>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=ventas" class="btn btn-danger w-100">
                            <i class="fas fa-cash-register me-2"></i>Ventas
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=garantias" class="btn btn-danger w-100">
                            <i class="fas fa-shield-alt me-2"></i>Garantías
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=devoluciones" class="btn btn-danger w-100">
                            <i class="fas fa-undo me-2"></i>Devoluciones
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=traslados" class="btn btn-danger w-100">
                            <i class="fas fa-exchange-alt me-2"></i>Traslados
                        </a>
                    </div>
                    <div class="col-md-2 mb-3">
                        <a href="index.php?page=ver_todo" class="btn btn-danger w-100">
                            <i class="fas fa-list me-2"></i>Ver Todo
                        </a>
                    </div>
                </div>

                <!-- Tercera fila: Otras funciones -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=inventario" class="btn btn-primary w-100">
                            <i class="fas fa-clipboard-list me-2"></i>Inventario
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=ver_ventas" class="btn btn-info w-100">
                            <i class="fas fa-chart-line me-2"></i>Ver Ventas
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=entregar" class="btn btn-warning w-100">
                            <i class="fas fa-truck me-2"></i>Entregas
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=actualizar_inventario" class="btn btn-secondary w-100">
                            <i class="fas fa-sync me-2"></i>Actualizar Inventario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movimientos recientes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>
                    Movimientos Recientes
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($movimientos_recientes)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos_recientes as $movimiento): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $movimiento['tipo'] === 'entrada' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($movimiento['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($movimiento['producto_nombre']); ?></td>
                                        <td><?php echo $movimiento['cantidad']; ?></td>
                                        <td><?php echo htmlspecialchars($movimiento['usuario_nombre']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay movimientos recientes.</p>
                <?php endif; ?>
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