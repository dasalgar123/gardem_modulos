<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorEntradas.php';

// Crear instancia del controlador
$controladorEntradas = new ControladorEntradas($pdo);

// Obtener datos
$entradas = $controladorEntradas->obtenerEntradas();
$proveedores = $controladorEntradas->obtenerProveedores();
$productos = $controladorEntradas->obtenerProductos();
$bodegas = $controladorEntradas->obtenerBodegas();
$colores = $controladorEntradas->obtenerColores();
$tallas = $controladorEntradas->obtenerTallas();

// Calcular estadísticas
$total_entradas = count($entradas);
$entradas_confirmadas = count(array_filter($entradas, function($e) { return $e['estado'] == 'confirmada'; }));
$entradas_pendientes = count(array_filter($entradas, function($e) { return $e['estado'] == 'pendiente'; }));
$entradas_hoy = count(array_filter($entradas, function($e) { return date('Y-m-d', strtotime($e['fecha'])) == date('Y-m-d'); }));

// Verificar mensajes
$mensaje_exito = isset($_GET['success']) && $_GET['success'] == 1;
$mensaje_error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-arrow-down text-success me-2"></i>Entradas de Inventario</h1>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevaEntradaModal">
            <i class="fas fa-plus me-2"></i>Nueva Entrada
        </button>
    </div>

    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La entrada ha sido registrada correctamente.
        </div>
    <?php endif; ?>
    
    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Entradas</h6>
                            <h4 class="mb-0"><?php echo $total_entradas; ?></h4>
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
                            <h4 class="mb-0"><?php echo $entradas_confirmadas; ?></h4>
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
                            <h4 class="mb-0"><?php echo $entradas_pendientes; ?></h4>
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
                            <h4 class="mb-0"><?php echo $entradas_hoy; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de entradas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Entradas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($entradas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-arrow-down fa-3x mb-3"></i>
                    <h5>No se encontraron entradas</h5>
                    <p>No hay registros de entradas en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Bodega</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entradas as $entrada): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($entrada['numero_documento']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($entrada['proveedor_nombre'] ?? 'Sin proveedor'); ?></td>
                                <td><?php echo htmlspecialchars($entrada['bodega_nombre']); ?></td>
                                <td>$<?php echo number_format($entrada['total'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $entrada['estado'] == 'confirmada' ? 'success' : 
                                            ($entrada['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($entrada['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($entrada['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleEntrada(<?php echo $entrada['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($entrada['estado'] == 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="confirmarEntrada(<?php echo $entrada['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="anularEntrada(<?php echo $entrada['id']; ?>)">
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

<!-- Modal Nueva Entrada -->
<div class="modal fade" id="nuevaEntradaModal" tabindex="-1" aria-labelledby="nuevaEntradaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="nuevaEntradaModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Entrada de Inventario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaEntrada" method="POST" action="procesar_entrada.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="numero_documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" 
                                   value="<?php echo generarNumeroDocumento('entrada'); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="proveedor_id" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_id" name="proveedor_id">
                                <option value="">Seleccionar proveedor...</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?php echo $proveedor['id']; ?>">
                                        <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
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
                            <button type="button" class="btn btn-sm btn-success" onclick="agregarProducto()">
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
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Guardar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pasar datos de PHP a JavaScript -->
<script>
// Datos de productos
window.productosData = `<?php 
    foreach ($productos as $producto): 
        echo '<option value="' . $producto['id'] . '" data-precio="' . $producto['precio'] . '">' . 
             htmlspecialchars($producto['nombre']) . '</option>';
    endforeach; 
?>`;

// Datos de colores
window.coloresData = `<?php 
    foreach ($colores as $color): 
        echo '<option value="' . $color['id'] . '">' . htmlspecialchars($color['nombre']) . '</option>';
    endforeach; 
?>`;

// Datos de tallas
window.tallasData = `<?php 
    foreach ($tallas as $talla): 
        echo '<option value="' . $talla['id'] . '">' . htmlspecialchars($talla['nombre']) . '</option>';
    endforeach; 
?>`;
</script>

<!-- JavaScript se carga desde app.js en index.php --> 