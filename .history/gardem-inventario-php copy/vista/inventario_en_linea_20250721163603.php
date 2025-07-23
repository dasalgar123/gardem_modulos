<?php
// Vista para inventario en línea - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener estado de sincronización del inventario
try {
    $stmt = $pdo->prepare("
        SELECT p.*, ib.stock_actual, ib.stock_minimo, ib.stock_maximo,
               b.nombre as bodega_nombre, b.id as bodega_id,
               COALESCE(iel.ultima_actualizacion, ib.ultima_actualizacion) as ultima_sincronizacion,
               COALESCE(iel.sincronizado, 0) as sincronizado,
               COALESCE(iel.version, 1) as version_linea
        FROM productos p
        JOIN inventario_bodega ib ON p.id = ib.producto_id
        JOIN bodega b ON ib.bodega_id = b.id
        LEFT JOIN inventario_en_linea iel ON p.id = iel.producto_id AND ib.bodega_id = iel.bodega_id
        ORDER BY p.nombre ASC
    ");
    $stmt->execute();
    $inventario_en_linea = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $inventario_en_linea = [];
}

// Obtener estadísticas de sincronización
$stats = [
    'total' => count($inventario_en_linea),
    'sincronizado' => 0,
    'pendiente' => 0,
    'desactualizado' => 0
];

foreach ($inventario_en_linea as $item) {
    if ($item['sincronizado']) {
        $stats['sincronizado']++;
    } else {
        $stats['pendiente']++;
    }
    
    // Verificar si está desactualizado (más de 1 hora sin sincronizar)
    $ultima_sync = strtotime($item['ultima_sincronizacion']);
    $hace_1_hora = time() - 3600;
    if ($ultima_sync < $hace_1_hora) {
        $stats['desactualizado']++;
    }
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-database me-2"></i>Inventario en Línea
        </h1>
    </div>
</div>

<!-- Estado de sincronización -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Productos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-primary"></i>
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
                            Sincronizados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['sincronizado']; ?>
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
        <div class="card border-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['pendiente']; ?>
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
        <div class="card border-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Desactualizados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['desactualizado']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de progreso de sincronización -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>Progreso de Sincronización
                </h6>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 25px;">
                    <?php 
                    $porcentaje = $stats['total'] > 0 ? ($stats['sincronizado'] / $stats['total']) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: <?php echo $porcentaje; ?>%" 
                         aria-valuenow="<?php echo $porcentaje; ?>" 
                         aria-valuemin="0" aria-valuemax="100">
                        <?php echo round($porcentaje, 1); ?>%
                    </div>
                </div>
                <small class="text-muted">
                    <?php echo $stats['sincronizado']; ?> de <?php echo $stats['total']; ?> productos sincronizados correctamente
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Acciones de sincronización -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cogs me-2"></i>Acciones de Sincronización
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-success w-100 mb-2" onclick="sincronizarTodo()">
                            <i class="fas fa-sync me-2"></i>Sincronizar Todo
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-warning w-100 mb-2" onclick="sincronizarPendientes()">
                            <i class="fas fa-clock me-2"></i>Sincronizar Pendientes
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info w-100 mb-2" onclick="verificarConexion()">
                            <i class="fas fa-wifi me-2"></i>Verificar Conexión
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100 mb-2" onclick="exportarEstado()">
                            <i class="fas fa-download me-2"></i>Exportar Estado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de inventario en línea -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>Estado de Sincronización por Producto
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($inventario_en_linea)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaInventarioEnLinea">
                            <thead class="table-primary">
                                <tr>
                                    <th>Producto</th>
                                    <th>Bodega</th>
                                    <th>Stock Local</th>
                                    <th>Última Sincronización</th>
                                    <th>Estado</th>
                                    <th>Versión</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventario_en_linea as $item): ?>
                                    <?php
                                    $estado = 'sincronizado';
                                    $badge_class = 'success';
                                    $icon = 'check';
                                    
                                    if (!$item['sincronizado']) {
                                        $estado = 'pendiente';
                                        $badge_class = 'warning';
                                        $icon = 'clock';
                                    }
                                    
                                    $ultima_sync = strtotime($item['ultima_sincronizacion']);
                                    $hace_1_hora = time() - 3600;
                                    if ($ultima_sync < $hace_1_hora) {
                                        $estado = 'desactualizado';
                                        $badge_class = 'danger';
                                        $icon = 'exclamation-triangle';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['imagen']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                         alt="Producto" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['bodega_nombre']); ?></td>
                                        <td>
                                            <span class="badge bg-primary fs-6"><?php echo $item['stock_actual']; ?></span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y H:i', $ultima_sync); ?>
                                                <?php if ($ultima_sync < $hace_1_hora): ?>
                                                    <br><span class="text-danger">Hace más de 1 hora</span>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <i class="fas fa-<?php echo $icon; ?> me-1"></i>
                                                <?php echo ucfirst($estado); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">v<?php echo $item['version_linea']; ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="sincronizarProducto(<?php echo $item['id']; ?>, <?php echo $item['bodega_id']; ?>)">
                                                <i class="fas fa-sync me-1"></i>Sincronizar
                                            </button>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="verDetalleSincronizacion(<?php echo $item['id']; ?>, <?php echo $item['bodega_id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Detalle
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-database fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay productos en el inventario en línea.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalle de sincronización -->
<div class="modal fade" id="modalDetalleSincronizacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Detalle de Sincronización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleSincronizacionContent">
                <!-- Contenido del detalle -->
            </div>
        </div>
    </div>
</div>

<script>
function sincronizarTodo() {
    if (confirm('¿Desea sincronizar todo el inventario con la base de datos en línea?')) {
        mostrarProgreso('Sincronizando todo el inventario...');
        
        fetch('controlador/ControladorIndex.php?action=sincronizar_todo', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            ocultarProgreso();
            if (data.success) {
                alert('Sincronización completada exitosamente');
                location.reload();
            } else {
                alert('Error en la sincronización: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            ocultarProgreso();
            console.error('Error:', error);
            alert('Error al sincronizar el inventario');
        });
    }
}

function sincronizarPendientes() {
    if (confirm('¿Desea sincronizar solo los productos pendientes?')) {
        mostrarProgreso('Sincronizando productos pendientes...');
        
        fetch('controlador/ControladorIndex.php?action=sincronizar_pendientes', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            ocultarProgreso();
            if (data.success) {
                alert('Sincronización de pendientes completada');
                location.reload();
            } else {
                alert('Error en la sincronización: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            ocultarProgreso();
            console.error('Error:', error);
            alert('Error al sincronizar productos pendientes');
        });
    }
}

function sincronizarProducto(productoId, bodegaId) {
    mostrarProgreso('Sincronizando producto...');
    
    fetch('controlador/ControladorIndex.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'sincronizar_producto',
            producto_id: productoId,
            bodega_id: bodegaId
        })
    })
    .then(response => response.json())
    .then(data => {
        ocultarProgreso();
        if (data.success) {
            alert('Producto sincronizado exitosamente');
            location.reload();
        } else {
            alert('Error al sincronizar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        ocultarProgreso();
        console.error('Error:', error);
        alert('Error al sincronizar el producto');
    });
}

function verificarConexion() {
    mostrarProgreso('Verificando conexión...');
    
    fetch('controlador/ControladorIndex.php?action=verificar_conexion')
        .then(response => response.json())
        .then(data => {
            ocultarProgreso();
            if (data.success) {
                alert('Conexión exitosa. Latencia: ' + data.latencia + 'ms');
            } else {
                alert('Error de conexión: ' + (data.message || 'No se pudo conectar'));
            }
        })
        .catch(error => {
            ocultarProgreso();
            console.error('Error:', error);
            alert('Error al verificar la conexión');
        });
}

function verDetalleSincronizacion(productoId, bodegaId) {
    fetch(`controlador/ControladorIndex.php?action=detalle_sincronizacion&producto_id=${productoId}&bodega_id=${bodegaId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalleSincronizacionContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalleSincronizacion')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el detalle de sincronización');
        });
}

function exportarEstado() {
    window.location.href = 'controlador/ControladorIndex.php?action=exportar_estado_sincronizacion';
}

function mostrarProgreso(mensaje) {
    // Crear overlay de progreso
    const overlay = document.createElement('div');
    overlay.id = 'progresoOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    overlay.innerHTML = `
        <div class="card" style="min-width: 300px;">
            <div class="card-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <h6>${mensaje}</h6>
                <small class="text-muted">Por favor espere...</small>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

function ocultarProgreso() {
    const overlay = document.getElementById('progresoOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Inicializar DataTable
$(document).ready(function() {
    $('#tablaInventarioEnLinea').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[4, 'asc'], [3, 'desc']],
        pageLength: 25
    });
});
</script> 