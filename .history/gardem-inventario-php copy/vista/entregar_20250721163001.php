<?php
// Vista para gestionar entregas - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener entregas pendientes
try {
    $stmt = $pdo->prepare("
        SELECT vp.*, v.fecha as fecha_venta, v.cliente_id,
               c.nombre as cliente_nombre, c.telefono as cliente_telefono, c.direccion as cliente_direccion,
               p.nombre as producto_nombre, p.imagen as producto_imagen, p.codigo as producto_codigo,
               b.nombre as bodega_nombre
        FROM ventas_productos vp
        JOIN ventas v ON vp.venta_id = v.id
        JOIN cliente c ON v.cliente_id = c.id
        JOIN productos p ON vp.producto_id = p.id
        LEFT JOIN bodega b ON vp.bodega_id = b.id
        WHERE vp.estado = 'pendiente_entrega'
        ORDER BY v.fecha ASC
    ");
    $stmt->execute();
    $entregas_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $entregas_pendientes = [];
}

// Obtener entregas recientes
try {
    $stmt = $pdo->prepare("
        SELECT vp.*, v.fecha as fecha_venta, vp.fecha_entrega,
               c.nombre as cliente_nombre, c.telefono as cliente_telefono,
               p.nombre as producto_nombre, p.imagen as producto_imagen,
               u.nombre as almacenista_nombre
        FROM ventas_productos vp
        JOIN ventas v ON vp.venta_id = v.id
        JOIN cliente c ON v.cliente_id = c.id
        JOIN productos p ON vp.producto_id = p.id
        LEFT JOIN usuario u ON vp.almacenista_id = u.id
        WHERE vp.estado = 'entregado'
        ORDER BY vp.fecha_entrega DESC
        LIMIT 20
    ");
    $stmt->execute();
    $entregas_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $entregas_recientes = [];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-truck me-2"></i>Gestionar Entregas
        </h1>
    </div>
</div>

<!-- Estadísticas de entregas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo count($entregas_pendientes); ?>
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
        <div class="card border-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Entregadas Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $entregadas_hoy = array_filter($entregas_recientes, function($entrega) {
                                return date('Y-m-d', strtotime($entrega['fecha_entrega'])) === date('Y-m-d');
                            });
                            echo count($entregadas_hoy);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
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
                            Total Entregadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo count($entregas_recientes); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Mi Bodega
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo htmlspecialchars($_SESSION['usuario_bodega_nombre'] ?? 'Principal'); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-warehouse fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entregas pendientes -->
<?php if (!empty($entregas_pendientes)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning shadow">
            <div class="card-header bg-warning text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Entregas Pendientes (<?php echo count($entregas_pendientes); ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaEntregasPendientes">
                        <thead class="table-warning">
                            <tr>
                                <th>Fecha Venta</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Bodega</th>
                                <th>Dirección</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entregas_pendientes as $entrega): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($entrega['fecha_venta'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($entrega['cliente_nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($entrega['cliente_telefono']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($entrega['producto_imagen']): ?>
                                                <img src="<?php echo htmlspecialchars($entrega['producto_imagen']); ?>" 
                                                     alt="Producto" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($entrega['producto_nombre']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($entrega['producto_codigo']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $entrega['cantidad']; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($entrega['bodega_nombre'] ?? 'Principal'); ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($entrega['cliente_direccion']); ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="confirmarEntrega(<?php echo $entrega['venta_id']; ?>, <?php echo $entrega['producto_id']; ?>, <?php echo $entrega['cantidad']; ?>, <?php echo $entrega['bodega_id'] ?? 'null'; ?>)">
                                            <i class="fas fa-truck me-1"></i>Entregar
                                        </button>
                                        <button class="btn btn-info btn-sm ms-1" 
                                                onclick="verDetalleEntrega(<?php echo $entrega['venta_id']; ?>, <?php echo $entrega['producto_id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success shadow">
            <div class="card-body text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-success">¡Excelente trabajo!</h5>
                <p class="text-muted">No hay entregas pendientes. Todas las ventas han sido procesadas.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Entregas recientes -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>
                    Entregas Recientes
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($entregas_recientes)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaEntregasRecientes">
                            <thead class="table-primary">
                                <tr>
                                    <th>Fecha Entrega</th>
                                    <th>Cliente</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Almacenista</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entregas_recientes as $entrega): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></td>
                                        <td><?php echo htmlspecialchars($entrega['cliente_nombre']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($entrega['producto_imagen']): ?>
                                                    <img src="<?php echo htmlspecialchars($entrega['producto_imagen']); ?>" 
                                                         alt="Producto" class="me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($entrega['producto_nombre']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $entrega['cantidad']; ?></td>
                                        <td><?php echo htmlspecialchars($entrega['almacenista_nombre'] ?? 'Sistema'); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Entregado
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay entregas registradas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar entrega -->
<div class="modal fade" id="modalConfirmarEntrega" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-truck me-2"></i>Confirmar Entrega
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea marcar este producto como entregado?</p>
                <p><strong>Esta acción:</strong></p>
                <ul>
                    <li>Marcará el producto como entregado</li>
                    <li>Descontará el stock del inventario físico</li>
                    <li>Sincronizará con el inventario en línea</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEntrega">
                    <i class="fas fa-truck me-1"></i>Confirmar Entrega
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let entregaActual = null;

function confirmarEntrega(ventaId, productoId, cantidad, bodegaId) {
    entregaActual = { ventaId, productoId, cantidad, bodegaId };
    new bootstrap.Modal(document.getElementById('modalConfirmarEntrega')).show();
}

document.getElementById('btnConfirmarEntrega').addEventListener('click', function() {
    if (!entregaActual) return;
    
    // Procesar la entrega
    fetch('controlador/ControladorIndex.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'gestionar_entrega',
            venta_id: entregaActual.ventaId,
            producto_id: entregaActual.productoId,
            cantidad: entregaActual.cantidad,
            bodega_id: entregaActual.bodegaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Entrega procesada exitosamente');
            location.reload();
        } else {
            alert('Error al procesar la entrega: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la entrega');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEntrega')).hide();
        entregaActual = null;
    });
});

function verDetalleEntrega(ventaId, productoId) {
    // Cargar detalle de la entrega
    fetch(`controlador/ControladorIndex.php?action=detalle_entrega&venta_id=${ventaId}&producto_id=${productoId}`)
        .then(response => response.text())
        .then(html => {
            // Mostrar en un modal o alerta
            alert('Detalle de entrega cargado');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el detalle de la entrega');
        });
}

// Inicializar DataTables
$(document).ready(function() {
    $('#tablaEntregasPendientes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'asc']]
    });
    
    $('#tablaEntregasRecientes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'desc']]
    });
});
</script> 