<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir los controladores necesarios
require_once __DIR__ . '/../controlador/ControladorInventario.php';
require_once __DIR__ . '/../controlador/ControladorEntradas.php';
require_once __DIR__ . '/../controlador/ControladorSalidas.php';
require_once __DIR__ . '/../controlador/ControladorTraslados.php';
require_once __DIR__ . '/../controlador/ControladorGarantias.php';
require_once __DIR__ . '/../controlador/ControladorDevoluciones.php';
require_once __DIR__ . '/../controlador/ControladorCompras.php';

// Crear instancias de los controladores
$controladorInventario = new ControladorInventario($pdo);
$controladorEntradas = new ControladorEntradas($pdo);
$controladorSalidas = new ControladorSalidas($pdo);
$controladorTraslados = new ControladorTraslados($pdo);
$controladorGarantias = new ControladorGarantias($pdo);
$controladorDevoluciones = new ControladorDevoluciones($pdo);
$controladorCompras = new ControladorCompras($pdo);

// Obtener estadísticas generales
$statsInventario = $controladorInventario->obtenerEstadisticasInventario();
$statsEntradas = $controladorEntradas->obtenerEstadisticasEntradas();
$statsSalidas = $controladorSalidas->obtenerEstadisticasSalidas();
$statsTraslados = $controladorTraslados->obtenerEstadisticasTraslados();
$statsGarantias = $controladorGarantias->obtenerEstadisticasGarantias();
$statsDevoluciones = $controladorDevoluciones->obtenerEstadisticasDevoluciones();
$statsCompras = $controladorCompras->obtenerEstadisticasCompras();

// Obtener datos para reportes específicos
$productosAgotados = $controladorInventario->obtenerProductosAgotados();
$productosStockBajo = $controladorInventario->obtenerProductosStockBajo();
$alertasStock = $controladorInventario->obtenerAlertasStock();
$garantiasVencidas = $controladorGarantias->obtenerGarantiasVencidas();
$comprasVencidas = $controladorCompras->obtenerComprasVencidas();
$movimientosRecientes = $controladorInventario->obtenerMovimientosRecientes();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-chart-bar text-info me-2"></i>Reportes y Estadísticas</h1>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-info" onclick="exportarReporte('inventario')">
                <i class="fas fa-download me-2"></i>Exportar Inventario
            </button>
            <button type="button" class="btn btn-outline-info" onclick="exportarReporte('movimientos')">
                <i class="fas fa-download me-2"></i>Exportar Movimientos
            </button>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Productos</h6>
                            <h4 class="mb-0"><?php echo $statsInventario['total_productos']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Valor Inventario</h6>
                            <h4 class="mb-0">$<?php echo number_format($statsInventario['valor_total'], 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Stock Bajo</h6>
                            <h4 class="mb-0"><?php echo $statsInventario['productos_stock_bajo']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Agotados</h6>
                            <h4 class="mb-0"><?php echo $statsInventario['productos_agotados']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y Notificaciones -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Alertas de Stock</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($alertasStock)): ?>
                        <p class="text-muted mb-0">No hay alertas de stock.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Bodega</th>
                                        <th>Stock</th>
                                        <th>Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (array_slice($alertasStock, 0, 5) as $alerta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($alerta['producto_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($alerta['bodega_nombre']); ?></td>
                                        <td><span class="badge bg-danger"><?php echo $alerta['stock_actual']; ?></span></td>
                                        <td><?php echo $alerta['stock_minimo']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($alertasStock) > 5): ?>
                            <small class="text-muted">Y <?php echo count($alertasStock) - 5; ?> más...</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Garantías Vencidas</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($garantiasVencidas)): ?>
                        <p class="text-muted mb-0">No hay garantías vencidas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Garantía</th>
                                        <th>Cliente</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach (array_slice($garantiasVencidas, 0, 5) as $garantia): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($garantia['numero_garantia']); ?></td>
                                        <td><?php echo htmlspecialchars($garantia['cliente_nombre']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($garantia['fecha_limite'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($garantiasVencidas) > 5): ?>
                            <small class="text-muted">Y <?php echo count($garantiasVencidas) - 5; ?> más...</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Módulo -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-arrow-down me-2"></i>Entradas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="text-success"><?php echo $statsEntradas['total']; ?></h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-primary"><?php echo $statsEntradas['confirmadas']; ?></h5>
                            <small class="text-muted">Confirmadas</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-warning"><?php echo $statsEntradas['pendientes']; ?></h5>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-arrow-up me-2"></i>Salidas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="text-danger"><?php echo $statsSalidas['total']; ?></h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-primary"><?php echo $statsSalidas['confirmadas']; ?></h5>
                            <small class="text-muted">Confirmadas</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-warning"><?php echo $statsSalidas['pendientes']; ?></h5>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Traslados</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="text-warning"><?php echo $statsTraslados['total']; ?></h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-success"><?php echo $statsTraslados['completados']; ?></h5>
                            <small class="text-muted">Completados</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-info"><?php echo $statsTraslados['en_proceso']; ?></h5>
                            <small class="text-muted">En Proceso</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos Recientes -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Movimientos Recientes</h6>
        </div>
        <div class="card-body">
            <?php if (empty($movimientosRecientes)): ?>
                <p class="text-muted mb-0">No hay movimientos recientes.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Bodega</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($movimientosRecientes as $movimiento): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo $movimiento['tipo'] == 'entrada' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($movimiento['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movimiento['numero_documento']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($movimiento['producto_nombre']); ?></td>
                                <td><?php echo $movimiento['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($movimiento['bodega_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($movimiento['usuario_nombre']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos Agotados -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Productos Agotados</h6>
        </div>
        <div class="card-body">
            <?php if (empty($productosAgotados)): ?>
                <p class="text-muted mb-0">No hay productos agotados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Proveedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($productosAgotados as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['producto_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['tipo_producto']); ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?? 'Sin proveedor'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="crearCompraRapida(<?php echo $producto['producto_id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarReporte(tipo) {
    // Implementar exportación de reportes
    alert('Función de exportación en desarrollo para: ' + tipo);
}

function crearCompraRapida(productoId) {
    // Implementar creación rápida de compra
    alert('Función de compra rápida en desarrollo para producto ID: ' + productoId);
}

// Actualizar reportes cada 5 minutos
setInterval(function() {
    // Aquí se podrían actualizar las estadísticas dinámicamente
    console.log('Actualizando reportes...');
}, 300000);
</script> 