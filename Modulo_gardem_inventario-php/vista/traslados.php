<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorTraslados.php';

// Crear instancia del controlador
$controladorTraslados = new ControladorTraslados($pdo);

// Obtener datos
$traslados = $controladorTraslados->obtenerTraslados();
$productos = $controladorTraslados->obtenerProductos();
$bodegas = $controladorTraslados->obtenerBodegas();
$colores = $controladorTraslados->obtenerColores();
$tallas = $controladorTraslados->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-exchange-alt text-warning me-2"></i>Traslados de Inventario</h1>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#nuevoTrasladoModal">
            <i class="fas fa-plus me-2"></i>Nuevo Traslado
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> El traslado ha sido registrado correctamente.
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
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Traslados</h6>
                            <h4 class="mb-0"><?php echo count($traslados); ?></h4>
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
                            <h6 class="card-title mb-0">Completados</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($traslados, function($t) { return $t['estado'] == 'completado'; })); ?></h4>
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
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">En Proceso</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($traslados, function($t) { return $t['estado'] == 'en_proceso'; })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Hoy</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($traslados, function($t) { return date('Y-m-d', strtotime($t['fecha'])) == date('Y-m-d'); })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de traslados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Traslados</h5>
        </div>
        <div class="card-body">
            <?php if (empty($traslados)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                    <h5>No se encontraron traslados</h5>
                    <p>No hay registros de traslados en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($traslados as $traslado): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-warning"><?php echo htmlspecialchars($traslado['numero_documento']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($traslado['fecha'])); ?></td>
                                <td>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($traslado['bodega_origen_nombre']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($traslado['bodega_destino_nombre']); ?></span>
                                </td>
                                <td>$<?php echo number_format($traslado['total'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $traslado['estado'] == 'completado' ? 'success' : 
                                            ($traslado['estado'] == 'en_proceso' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $traslado['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($traslado['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleTraslado(<?php echo $traslado['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($traslado['estado'] == 'en_proceso'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="completarTraslado(<?php echo $traslado['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelarTraslado(<?php echo $traslado['id']; ?>)">
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

<!-- Modal Nuevo Traslado -->
<div class="modal fade" id="nuevoTrasladoModal" tabindex="-1" aria-labelledby="nuevoTrasladoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="nuevoTrasladoModalLabel">
                    <i class="fas fa-plus me-2"></i>Nuevo Traslado de Inventario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoTraslado" method="POST" action="procesar_traslado.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                   value="<?php echo generarNumeroDocumento('traslado'); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="bodega_origen_id" class="form-label">Bodega Origen *</label>
                            <select class="form-select" id="bodega_origen_id" name="bodega_origen_id" required onchange="cargarInventarioOrigen()">
                                <option value="">Seleccionar bodega origen...</option>
                                <?php foreach ($bodegas as $bodega): ?>
                                    <option value="<?php echo $bodega['id']; ?>">
                                        <?php echo htmlspecialchars($bodega['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bodega_destino_id" class="form-label">Bodega Destino *</label>
                            <select class="form-select" id="bodega_destino_id" name="bodega_destino_id" required>
                                <option value="">Seleccionar bodega destino...</option>
                                <?php foreach ($bodegas as $bodega): ?>
                                    <option value="<?php echo $bodega['id']; ?>">
                                        <?php echo htmlspecialchars($bodega['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Productos -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Productos a Trasladar</h6>
                            <button type="button" class="btn btn-sm btn-warning" onclick="agregarProducto()">
                                <i class="fas fa-plus me-1"></i>Agregar Producto
                            </button>
                        </div>
                        <div id="productosContainer">
                            <!-- Los productos se agregarán dinámicamente -->
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Guardar Traslado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let contadorProductos = 0;

function agregarProducto() {
    contadorProductos++;
    const container = document.getElementById('productosContainer');
    
    const productoHtml = `
        <div class="row mb-2 producto-row" data-producto-id="${contadorProductos}">
            <div class="col-md-3">
                <select class="form-select producto-select" name="productos[${contadorProductos}][producto_id]" required onchange="cargarInventarioProducto(this, ${contadorProductos})">
                    <option value="">Seleccionar producto...</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][color_id]">
                    <option value="">Color...</option>
                    <?php foreach ($colores as $color): ?>
                        <option value="<?php echo $color['id']; ?>">
                            <?php echo htmlspecialchars($color['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][talla_id]">
                    <option value="">Talla...</option>
                    <?php foreach ($tallas as $talla): ?>
                        <option value="<?php echo $talla['id']; ?>">
                            <?php echo htmlspecialchars($talla['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="productos[${contadorProductos}][cantidad]" 
                       placeholder="Cantidad" min="1" required onchange="validarStockTraslado(this, ${contadorProductos})">
                <small class="text-muted stock-disponible" id="stock-${contadorProductos}"></small>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="productos[${contadorProductos}][precio_unitario]" 
                       placeholder="Precio" step="0.01" min="0" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerProducto(${contadorProductos})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', productoHtml);
}

function removerProducto(productoId) {
    const elemento = document.querySelector(`[data-producto-id="${productoId}"]`);
    if (elemento) {
        elemento.remove();
    }
}

function cargarInventarioOrigen() {
    const bodegaOrigenId = document.getElementById('bodega_origen_id').value;
    if (bodegaOrigenId) {
        // Aquí se cargaría el inventario disponible en la bodega origen
        console.log('Cargando inventario de bodega origen:', bodegaOrigenId);
    }
}

function cargarInventarioProducto(select, productoId) {
    const productoIdValue = select.value;
    const bodegaOrigenId = document.getElementById('bodega_origen_id').value;
    
    if (!productoIdValue || !bodegaOrigenId) {
        document.getElementById(`stock-${productoId}`).textContent = '';
        return;
    }
    
    // Aquí se haría una llamada AJAX para obtener el stock disponible
    document.getElementById(`stock-${productoId}`).textContent = 'Stock disponible: Verificando...';
}

function validarStockTraslado(input, productoId) {
    const cantidad = parseInt(input.value);
    const stockElement = document.getElementById(`stock-${productoId}`);
    
    // Aquí se validaría contra el stock real
    if (cantidad > 100) {
        stockElement.textContent = 'Cantidad muy alta';
        stockElement.className = 'text-danger';
        input.setCustomValidity('Cantidad excede el stock disponible');
    } else {
        stockElement.textContent = 'Stock disponible: OK';
        stockElement.className = 'text-success';
        input.setCustomValidity('');
    }
}

function verDetalleTraslado(trasladoId) {
    // Implementar vista detallada
    alert('Función de detalle en desarrollo para traslado ID: ' + trasladoId);
}

function completarTraslado(trasladoId) {
    if (confirm('¿Está seguro de que desea completar este traslado?')) {
        // Implementar completación
        alert('Función de completación en desarrollo para traslado ID: ' + trasladoId);
    }
}

function cancelarTraslado(trasladoId) {
    if (confirm('¿Está seguro de que desea cancelar este traslado?')) {
        // Implementar cancelación
        alert('Función de cancelación en desarrollo para traslado ID: ' + trasladoId);
    }
}

// Validar que origen y destino sean diferentes
document.getElementById('bodega_destino_id').addEventListener('change', function() {
    const origen = document.getElementById('bodega_origen_id').value;
    const destino = this.value;
    
    if (origen && destino && origen === destino) {
        alert('La bodega origen y destino no pueden ser la misma');
        this.value = '';
    }
});

// Agregar primer producto al cargar
document.addEventListener('DOMContentLoaded', function() {
    agregarProducto();
});
</script> 