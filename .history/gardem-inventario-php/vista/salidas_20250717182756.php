<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorSalidas.php';

// Crear instancia del controlador
$controladorSalidas = new ControladorSalidas($pdo);

// Obtener datos
$salidas = $controladorSalidas->obtenerSalidas();
$productos = $controladorSalidas->obtenerProductos();
$bodegas = $controladorSalidas->obtenerBodegas();
$colores = $controladorSalidas->obtenerColores();
$tallas = $controladorSalidas->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-arrow-up text-danger me-2"></i>Salidas de Inventario</h1>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#nuevaSalidaModal">
            <i class="fas fa-plus me-2"></i>Nueva Salida
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La salida ha sido registrada correctamente.
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
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Salidas</h6>
                            <h4 class="mb-0"><?php echo count($salidas); ?></h4>
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
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Confirmadas</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($salidas, function($s) { return $s['estado'] == 'confirmada'; })); ?></h4>
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
                            <h4 class="mb-0"><?php echo count(array_filter($salidas, function($s) { return $s['estado'] == 'pendiente'; })); ?></h4>
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
                            <h4 class="mb-0"><?php echo count(array_filter($salidas, function($s) { return date('Y-m-d', strtotime($s['fecha'])) == date('Y-m-d'); })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de salidas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Salidas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($salidas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-arrow-up fa-3x mb-3"></i>
                    <h5>No se encontraron salidas</h5>
                    <p>No hay registros de salidas en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Bodega</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($salidas as $salida): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($salida['numero_documento']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $salida['tipo_salida'] == 'venta' ? 'success' : 
                                            ($salida['tipo_salida'] == 'garantia' ? 'warning' : 
                                            ($salida['tipo_salida'] == 'devolucion' ? 'info' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($salida['tipo_salida']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($salida['bodega_nombre']); ?></td>
                                <td>$<?php echo number_format($salida['total'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $salida['estado'] == 'confirmada' ? 'success' : 
                                            ($salida['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($salida['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($salida['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleSalida(<?php echo $salida['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($salida['estado'] == 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="confirmarSalida(<?php echo $salida['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="anularSalida(<?php echo $salida['id']; ?>)">
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

<!-- Modal Nueva Salida -->
<div class="modal fade" id="nuevaSalidaModal" tabindex="-1" aria-labelledby="nuevaSalidaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="nuevaSalidaModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Salida de Inventario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaSalida" method="POST" action="procesar_salida.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                   value="<?php echo generarNumeroDocumento('salida'); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_salida" class="form-label">Tipo de Salida *</label>
                            <select class="form-select" id="tipo_salida" name="tipo_salida" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="venta">Venta</option>
                                <option value="garantia">Garantía</option>
                                <option value="devolucion">Devolución</option>
                                <option value="perdida">Pérdida</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bodega_id" class="form-label">Bodega *</label>
                            <select class="form-select" id="bodega_id" name="bodega_id" required>
                                <option value="">Seleccionar bodega...</option>
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
                            <h6 class="mb-0">Productos</h6>
                            <button type="button" class="btn btn-sm btn-danger" onclick="agregarProducto()">
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
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-2"></i>Guardar Salida
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
                <select class="form-select producto-select" name="productos[${contadorProductos}][producto_id]" required onchange="cargarInventario(this, ${contadorProductos})">
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
                       placeholder="Cantidad" min="1" required onchange="validarStock(this, ${contadorProductos})">
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

function cargarInventario(select, productoId) {
    const productoIdValue = select.value;
    const bodegaId = document.getElementById('bodega_id').value;
    
    if (!productoIdValue || !bodegaId) {
        document.getElementById(`stock-${productoId}`).textContent = '';
        return;
    }
    
    // Aquí se haría una llamada AJAX para obtener el stock disponible
    // Por ahora mostraremos un mensaje genérico
    document.getElementById(`stock-${productoId}`).textContent = 'Stock disponible: Verificando...';
}

function validarStock(input, productoId) {
    const cantidad = parseInt(input.value);
    const stockElement = document.getElementById(`stock-${productoId}`);
    
    // Aquí se validaría contra el stock real
    // Por ahora es una validación básica
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

function verDetalleSalida(salidaId) {
    // Implementar vista detallada
    alert('Función de detalle en desarrollo para salida ID: ' + salidaId);
}

function confirmarSalida(salidaId) {
    if (confirm('¿Está seguro de que desea confirmar esta salida?')) {
        // Implementar confirmación
        alert('Función de confirmación en desarrollo para salida ID: ' + salidaId);
    }
}

function anularSalida(salidaId) {
    if (confirm('¿Está seguro de que desea anular esta salida?')) {
        // Implementar anulación
        alert('Función de anulación en desarrollo para salida ID: ' + salidaId);
    }
}

// Agregar primer producto al cargar
document.addEventListener('DOMContentLoaded', function() {
    agregarProducto();
});
</script> 