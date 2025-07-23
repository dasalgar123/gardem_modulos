<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorDevoluciones.php';

// Crear instancia del controlador
$controladorDevoluciones = new ControladorDevoluciones($pdo);

// Obtener datos
$devoluciones = $controladorDevoluciones->obtenerDevoluciones();
$productos = $controladorDevoluciones->obtenerProductos();
$bodegas = $controladorDevoluciones->obtenerBodegas();
$colores = $controladorDevoluciones->obtenerColores();
$tallas = $controladorDevoluciones->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-undo text-secondary me-2"></i>Gestión de Devoluciones</h1>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#nuevaDevolucionModal">
            <i class="fas fa-plus me-2"></i>Nueva Devolución
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La devolución ha sido registrada correctamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-undo fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Devoluciones</h6>
                            <h4 class="mb-0"><?php echo count($devoluciones); ?></h4>
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
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Procesadas</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($devoluciones, function($d) { return $d['estado'] == 'procesada'; })); ?></h4>
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
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Pendientes</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($devoluciones, function($d) { return $d['estado'] == 'pendiente'; })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Hoy</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($devoluciones, function($d) { return date('Y-m-d', strtotime($d['fecha'])) == date('Y-m-d'); })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de devoluciones -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Devoluciones</h5>
        </div>
        <div class="card-body">
            <?php if (empty($devoluciones)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-undo fa-3x mb-3"></i>
                    <h5>No se encontraron devoluciones</h5>
                    <p>No hay registros de devoluciones en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($devoluciones as $devolucion): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($devolucion['numero_devolucion']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($devolucion['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['cliente_nombre'] ?? 'Sin cliente'); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['producto_nombre']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $devolucion['tipo_devolucion'] == 'defecto' ? 'danger' : 
                                            ($devolucion['tipo_devolucion'] == 'insatisfaccion' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($devolucion['tipo_devolucion']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(substr($devolucion['motivo'], 0, 50)) . (strlen($devolucion['motivo']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $devolucion['estado'] == 'procesada' ? 'success' : 
                                            ($devolucion['estado'] == 'en_revision' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $devolucion['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($devolucion['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleDevolucion(<?php echo $devolucion['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($devolucion['estado'] == 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="revisarDevolucion(<?php echo $devolucion['id']; ?>)">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($devolucion['estado'] == 'en_revision'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="procesarDevolucion(<?php echo $devolucion['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="rechazarDevolucion(<?php echo $devolucion['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
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

<!-- Modal Nueva Devolución -->
<div class="modal fade" id="nuevaDevolucionModal" tabindex="-1" aria-labelledby="nuevaDevolucionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="nuevaDevolucionModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Devolución
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaDevolucion" method="POST" action="procesar_devolucion.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="numero_devolucion" class="form-label">Número de Devolución</label>
                            <input type="text" class="form-control" id="numero_devolucion" name="numero_devolucion" 
                                   value="<?php echo generarNumeroDocumento('devolucion'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="cliente_nombre" class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" 
                                   placeholder="Nombre del cliente" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="producto_id" class="form-label">Producto *</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?php echo $producto['id']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_devolucion" class="form-label">Tipo de Devolución *</label>
                            <select class="form-select" id="tipo_devolucion" name="tipo_devolucion" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="defecto">Defecto de Fábrica</option>
                                <option value="insatisfaccion">Insatisfacción del Cliente</option>
                                <option value="error_envio">Error en Envío</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                   placeholder="Cantidad a devolver" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_compra" class="form-label">Fecha de Compra</label>
                            <input type="date" class="form-control" id="fecha_compra" name="fecha_compra">
                        </div>
                    </div>
                    
                    <!-- Motivo de la devolución -->
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo de la Devolución *</label>
                        <textarea class="form-control" id="motivo" name="motivo" 
                                  rows="4" placeholder="Describa el motivo de la devolución..." required></textarea>
                    </div>
                    
                    <!-- Condición del producto -->
                    <div class="mb-3">
                        <label for="condicion_producto" class="form-label">Condición del Producto *</label>
                        <select class="form-select" id="condicion_producto" name="condicion_producto" required>
                            <option value="">Seleccionar condición...</option>
                            <option value="nuevo">Nuevo (Sin usar)</option>
                            <option value="usado_bueno">Usado en Buen Estado</option>
                            <option value="usado_regular">Usado en Estado Regular</option>
                            <option value="deteriorado">Deteriorado</option>
                        </select>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-save me-2"></i>Guardar Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verDetalleDevolucion(devolucionId) {
    // Implementar vista detallada
    alert('Función de detalle en desarrollo para devolución ID: ' + devolucionId);
}

function revisarDevolucion(devolucionId) {
    if (confirm('¿Está seguro de que desea marcar esta devolución para revisión?')) {
        // Implementar revisión de devolución
        alert('Función de revisión en desarrollo para devolución ID: ' + devolucionId);
    }
}

function procesarDevolucion(devolucionId) {
    if (confirm('¿Está seguro de que desea procesar esta devolución?')) {
        // Implementar procesamiento de devolución
        alert('Función de procesamiento en desarrollo para devolución ID: ' + devolucionId);
    }
}

function rechazarDevolucion(devolucionId) {
    if (confirm('¿Está seguro de que desea rechazar esta devolución?')) {
        // Implementar rechazo de devolución
        alert('Función de rechazo en desarrollo para devolución ID: ' + devolucionId);
    }
}

// Validar que la fecha de compra no sea futura
document.getElementById('fecha_compra').addEventListener('change', function() {
    const fechaCompra = new Date(this.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaCompra > hoy) {
        alert('La fecha de compra no puede ser futura');
        this.value = '';
    }
});

// Auto-completar fecha de compra con fecha actual si está vacía
document.getElementById('fecha_compra').addEventListener('blur', function() {
    if (!this.value) {
        this.value = new Date().toISOString().split('T')[0];
    }
});
</script> 