<?php
// Vista para ver ventas realizadas - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener ventas
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono,
               COUNT(vp.id) as total_productos,
               SUM(vp.cantidad * vp.precio_unitario) as total_venta
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

// Obtener ventas pendientes de entrega
try {
    $stmt = $pdo->prepare("
        SELECT vp.*, v.fecha as fecha_venta, v.cliente_id,
               c.nombre as cliente_nombre, c.telefono as cliente_telefono,
               p.nombre as producto_nombre, p.imagen as producto_imagen
        FROM ventas_productos vp
        JOIN ventas v ON vp.venta_id = v.id
        JOIN cliente c ON v.cliente_id = c.id
        JOIN productos p ON vp.producto_id = p.id
        WHERE vp.estado = 'pendiente_entrega'
        ORDER BY v.fecha ASC
    ");
    $stmt->execute();
    $pendientes_entrega = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pendientes_entrega = [];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-chart-line me-2"></i>Ver Ventas
        </h1>
    </div>
</div>

<!-- Ventas pendientes de entrega -->
<?php if (!empty($pendientes_entrega)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning shadow">
            <div class="card-header bg-warning text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ventas Pendientes de Entrega (<?php echo count($pendientes_entrega); ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>Fecha Venta</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes_entrega as $pendiente): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pendiente['fecha_venta'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pendiente['cliente_nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($pendiente['cliente_telefono']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($pendiente['producto_imagen']): ?>
                                                <img src="<?php echo htmlspecialchars($pendiente['producto_imagen']); ?>" 
                                                     alt="Producto" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($pendiente['producto_nombre']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $pendiente['cantidad']; ?></td>
                                    <td>$<?php echo number_format($pendiente['precio_unitario'], 2); ?></td>
                                    <td>$<?php echo number_format($pendiente['cantidad'] * $pendiente['precio_unitario'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="entregarProducto(<?php echo $pendiente['venta_id']; ?>, <?php echo $pendiente['producto_id']; ?>, <?php echo $pendiente['cantidad']; ?>)">
                                            <i class="fas fa-truck me-1"></i>Entregar
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
<?php endif; ?>

<!-- Todas las ventas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Todas las Ventas
                </h6>
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
                                    <th>Teléfono</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td>#<?php echo $venta['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['cliente_telefono']); ?></td>
                                        <td><?php echo $venta['total_productos']; ?> productos</td>
                                        <td>$<?php echo number_format($venta['total_venta'], 2); ?></td>
                                        <td>
                                            <?php
                                            $estado = 'Completada';
                                            $badge_class = 'success';
                                            if ($venta['total_productos'] > 0) {
                                                $estado = 'Pendiente';
                                                $badge_class = 'warning';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo $estado; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="verDetalleVenta(<?php echo $venta['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Ver
                                            </button>
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

<!-- Modal para ver detalle de venta -->
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
        </div>
    </div>
</div>

<script>
function entregarProducto(ventaId, productoId, cantidad) {
    if (confirm('¿Confirmar entrega de este producto?')) {
        // Aquí se haría la llamada AJAX para procesar la entrega
        fetch('controlador/ControladorIndex.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'entregar_producto',
                venta_id: ventaId,
                producto_id: productoId,
                cantidad: cantidad
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto entregado exitosamente');
                location.reload();
            } else {
                alert('Error al entregar el producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la entrega');
        });
    }
}

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

// Inicializar DataTable
$(document).ready(function() {
    $('#tablaVentas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[1, 'desc']]
    });
});
</script> 