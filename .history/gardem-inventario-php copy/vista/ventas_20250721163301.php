<?php
// Vista para gestionar ventas - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener ventas
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono,
               COUNT(vp.id) as total_productos,
               SUM(vp.cantidad * vp.precio_unitario) as total_venta,
               SUM(CASE WHEN vp.estado = 'pendiente_entrega' THEN 1 ELSE 0 END) as pendientes_entrega
        FROM ventas v
        LEFT JOIN cliente c ON v.cliente_id = c.id
        LEFT JOIN ventas_productos vp ON v.id = vp.venta_id
        GROUP BY v.id
        ORDER BY v.fecha DESC
    ");
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ventas = [];
}

// Obtener productos con stock bajo para alertas
try {
    $stmt = $pdo->prepare("
        SELECT p.*, ib.stock_actual, ib.stock_minimo, b.nombre as bodega_nombre
        FROM productos p
        JOIN inventario_bodega ib ON p.id = ib.producto_id
        JOIN bodega b ON ib.bodega_id = b.id
        WHERE ib.stock_actual <= ib.stock_minimo
        ORDER BY ib.stock_actual ASC
    ");
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos_stock_bajo = [];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-cash-register me-2"></i>Gestionar Ventas
        </h1>
    </div>
</div>

<!-- Alertas de stock bajo -->
<?php if (!empty($productos_stock_bajo)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Productos con Stock Bajo
            </h6>
            <div class="row">
                <?php foreach (array_slice($productos_stock_bajo, 0, 4) as $producto): ?>
                    <div class="col-md-3 mb-2">
                        <small>
                            <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                            Stock: <?php echo $producto['stock_actual']; ?> / <?php echo $producto['stock_minimo']; ?>
                            (<?php echo $producto['bodega_nombre']; ?>)
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($productos_stock_bajo) > 4): ?>
                <small class="text-muted">Y <?php echo count($productos_stock_bajo) - 4; ?> productos más...</small>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Estadísticas de ventas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Ventas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo count($ventas); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Ventas Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $ventas_hoy = array_filter($ventas, function($venta) {
                                return date('Y-m-d', strtotime($venta['fecha'])) === date('Y-m-d');
                            });
                            echo count($ventas_hoy);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendientes Entrega
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $total_pendientes = array_sum(array_column($ventas, 'pendientes_entrega'));
                            echo $total_pendientes;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Facturado
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php 
                            $total_facturado = array_sum(array_column($ventas, 'total_venta'));
                            echo number_format($total_facturado, 2);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de ventas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Historial de Ventas
                </h6>
                <div>
                    <button class="btn btn-success btn-sm" onclick="exportarVentas()">
                        <i class="fas fa-download me-1"></i>Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($ventas)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaVentas">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Estado Entrega</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $venta['id']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?>
                                            <?php if (date('Y-m-d', strtotime($venta['fecha'])) === date('Y-m-d')): ?>
                                                <span class="badge bg-success ms-1">Hoy</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($venta['cliente_nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($venta['cliente_telefono']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $venta['total_productos']; ?> productos</span>
                                        </td>
                                        <td>
                                            <strong>$<?php echo number_format($venta['total_venta'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($venta['pendientes_entrega'] > 0): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i><?php echo $venta['pendientes_entrega']; ?> pendientes
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Completada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-info btn-sm" 
                                                        onclick="verDetalleVenta(<?php echo $venta['id']; ?>)">
                                                    <i class="fas fa-eye me-1"></i>Ver
                                                </button>
                                                <?php if ($venta['pendientes_entrega'] > 0): ?>
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="irAEntregas(<?php echo $venta['id']; ?>)">
                                                        <i class="fas fa-truck me-1"></i>Entregar
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-secondary btn-sm" 
                                                        onclick="imprimirFactura(<?php echo $venta['id']; ?>)">
                                                    <i class="fas fa-print me-1"></i>Factura
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay ventas registradas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalle de venta -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Detalle de Venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleVentaContent">
                <!-- Contenido del detalle -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirFactura()">
                    <i class="fas fa-print me-1"></i>Imprimir Factura
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalleVenta(ventaId) {
    // Cargar detalle de la venta en el modal
    fetch(`controlador/ControladorIndex.php?action=detalle_venta&id=${ventaId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalleVentaContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalleVenta')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el detalle de la venta');
        });
}

function irAEntregas(ventaId) {
    // Redirigir a la página de entregas con filtro por venta
    window.location.href = `index.php?page=entregar&venta_id=${ventaId}`;
}

function imprimirFactura(ventaId) {
    // Abrir ventana de impresión de factura
    window.open(`controlador/ControladorIndex.php?action=imprimir_factura&id=${ventaId}`, '_blank');
}

function exportarVentas() {
    // Exportar ventas a Excel/CSV
    window.location.href = 'controlador/ControladorIndex.php?action=exportar_ventas';
}

// Inicializar DataTable
$(document).ready(function() {
    $('#tablaVentas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[1, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script> 