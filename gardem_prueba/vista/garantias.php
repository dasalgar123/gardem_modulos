<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorGarantias.php';

// Crear instancia del controlador
$controladorGarantias = new ControladorGarantias($pdo);

// Obtener datos
$garantias = $controladorGarantias->obtenerGarantias();
$productos = $controladorGarantias->obtenerProductos();
$bodegas = $controladorGarantias->obtenerBodegas();
$colores = $controladorGarantias->obtenerColores();
$tallas = $controladorGarantias->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-shield-alt text-info me-2"></i>Gestión de Garantías</h1>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#nuevaGarantiaModal">
            <i class="fas fa-plus me-2"></i>Nueva Garantía
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La garantía ha sido registrada correctamente.
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
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Garantías</h6>
                            <h4 class="mb-0"><?php echo count($garantias); ?></h4>
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
                            <h6 class="card-title mb-0">Resueltas</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($garantias, function($g) { return $g['estado'] == 'resuelta'; })); ?></h4>
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
                            <h4 class="mb-0"><?php echo count(array_filter($garantias, function($g) { return $g['estado'] == 'pendiente'; })); ?></h4>
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
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Urgentes</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($garantias, function($g) { return $g['prioridad'] == 'alta'; })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de garantías -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Garantías</h5>
        </div>
        <div class="card-body">
            <?php if (empty($garantias)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shield-alt fa-3x mb-3"></i>
                    <h5>No se encontraron garantías</h5>
                    <p>No hay registros de garantías en el sistema.</p>
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
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($garantias as $garantia): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($garantia['numero_garantia']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($garantia['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($garantia['cliente_nombre'] ?? 'Sin cliente'); ?></td>
                                <td><?php echo htmlspecialchars($garantia['producto_nombre']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $garantia['tipo_garantia'] == 'reparacion' ? 'warning' : 
                                            ($garantia['tipo_garantia'] == 'reposicion' ? 'success' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($garantia['tipo_garantia']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $garantia['prioridad'] == 'alta' ? 'danger' : 
                                            ($garantia['prioridad'] == 'media' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo ucfirst($garantia['prioridad']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $garantia['estado'] == 'resuelta' ? 'success' : 
                                            ($garantia['estado'] == 'en_proceso' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $garantia['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($garantia['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleGarantia(<?php echo $garantia['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($garantia['estado'] == 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="iniciarGarantia(<?php echo $garantia['id']; ?>)">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($garantia['estado'] == 'en_proceso'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="resolverGarantia(<?php echo $garantia['id']; ?>)">
                                                <i class="fas fa-check"></i>
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

<!-- Modal Nueva Garantía -->
<div class="modal fade" id="nuevaGarantiaModal" tabindex="-1" aria-labelledby="nuevaGarantiaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="nuevaGarantiaModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Garantía
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaGarantia" method="POST" action="procesar_garantia.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="numero_garantia" class="form-label">Número de Garantía</label>
                            <input type="text" class="form-control" id="numero_garantia" name="numero_garantia" 
                                   value="<?php echo generarNumeroDocumento('garantia'); ?>" readonly>
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
                            <label for="tipo_garantia" class="form-label">Tipo de Garantía *</label>
                            <select class="form-select" id="tipo_garantia" name="tipo_garantia" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="reparacion">Reparación</option>
                                <option value="reposicion">Reposición</option>
                                <option value="devolucion">Devolución</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prioridad" class="form-label">Prioridad *</label>
                            <select class="form-select" id="prioridad" name="prioridad" required>
                                <option value="">Seleccionar prioridad...</option>
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_limite" class="form-label">Fecha Límite</label>
                            <input type="date" class="form-control" id="fecha_limite" name="fecha_limite">
                        </div>
                    </div>
                    
                    <!-- Descripción del problema -->
                    <div class="mb-3">
                        <label for="descripcion_problema" class="form-label">Descripción del Problema *</label>
                        <textarea class="form-control" id="descripcion_problema" name="descripcion_problema" 
                                  rows="4" placeholder="Describa el problema del producto..." required></textarea>
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
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-2"></i>Guardar Garantía
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function verDetalleGarantia(garantiaId) {
    // Implementar vista detallada
    alert('Función de detalle en desarrollo para garantía ID: ' + garantiaId);
}

function iniciarGarantia(garantiaId) {
    if (confirm('¿Está seguro de que desea iniciar el proceso de esta garantía?')) {
        // Implementar inicio de garantía
        alert('Función de inicio en desarrollo para garantía ID: ' + garantiaId);
    }
}

function resolverGarantia(garantiaId) {
    if (confirm('¿Está seguro de que desea marcar esta garantía como resuelta?')) {
        // Implementar resolución de garantía
        alert('Función de resolución en desarrollo para garantía ID: ' + garantiaId);
    }
}

// Auto-calcular fecha límite basada en prioridad
document.getElementById('prioridad').addEventListener('change', function() {
    const prioridad = this.value;
    const fechaLimite = document.getElementById('fecha_limite');
    const hoy = new Date();
    
    if (prioridad === 'alta') {
        // 3 días para prioridad alta
        const fecha = new Date(hoy.getTime() + (3 * 24 * 60 * 60 * 1000));
        fechaLimite.value = fecha.toISOString().split('T')[0];
    } else if (prioridad === 'media') {
        // 7 días para prioridad media
        const fecha = new Date(hoy.getTime() + (7 * 24 * 60 * 60 * 1000));
        fechaLimite.value = fecha.toISOString().split('T')[0];
    } else if (prioridad === 'baja') {
        // 15 días para prioridad baja
        const fecha = new Date(hoy.getTime() + (15 * 24 * 60 * 60 * 1000));
        fechaLimite.value = fecha.toISOString().split('T')[0];
    }
});

// Validar que la fecha límite no sea anterior a hoy
document.getElementById('fecha_limite').addEventListener('change', function() {
    const fechaLimite = new Date(this.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaLimite < hoy) {
        alert('La fecha límite no puede ser anterior a hoy');
        this.value = '';
    }
});
</script> 