<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorCompras.php';

// Crear instancia del controlador
$controladorCompras = new ControladorCompras($pdo);

// Obtener datos
$compras = $controladorCompras->obtenerCompras();
$proveedores = $controladorCompras->obtenerProveedores();
$productos = $controladorCompras->obtenerProductos();
$bodegas = $controladorCompras->obtenerBodegas();
$colores = $controladorCompras->obtenerColores();
$tallas = $controladorCompras->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-shopping-cart text-primary me-2"></i>Gestión de Compras</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaCompraModal">
            <i class="fas fa-plus me-2"></i>Nueva Compra
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La compra ha sido registrada correctamente.
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
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Compras</h6>
                            <h4 class="mb-0"><?php echo count($compras); ?></h4>
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
                            <h6 class="card-title mb-0">Confirmadas</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($compras, function($c) { return $c['estado'] == 'confirmada'; })); ?></h4>
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
                            <h4 class="mb-0"><?php echo count(array_filter($compras, function($c) { return $c['estado'] == 'pendiente'; })); ?></h4>
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
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Valor Total</h6>
                            <h4 class="mb-0">$<?php echo number_format(array_sum(array_column(array_filter($compras, function($c) { return $c['estado'] == 'confirmada'; }), 'total')), 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de compras -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Compras</h5>
        </div>
        <div class="card-body">
            <?php if (empty($compras)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <h5>No se encontraron compras</h5>
                    <p>No hay registros de compras en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Orden</th>
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
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($compra['numero_orden']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($compra['proveedor_nombre'] ?? 'Sin proveedor'); ?></td>
                                <td><?php echo htmlspecialchars($compra['bodega_nombre']); ?></td>
                                <td>$<?php echo number_format($compra['total'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $compra['estado'] == 'confirmada' ? 'success' : 
                                            ($compra['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($compra['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleCompra(<?php echo $compra['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($compra['estado'] == 'pendiente'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="confirmarCompra(<?php echo $compra['id']; ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelarCompra(<?php echo $compra['id']; ?>)">
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

<!-- Modal Nueva Compra -->
<div class="modal fade" id="nuevaCompraModal" tabindex="-1" aria-labelledby="nuevaCompraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevaCompraModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Orden de Compra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaCompra" method="POST" action="procesar_compra.php">
                <div class="modal-body">
                    <!-- Información básica -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="numero_orden" class="form-label">Número de Orden</label>
                            <input type="text" class="form-control" id="numero_orden" name="numero_orden" 
                                   value="<?php echo generarNumeroDocumento('compra'); ?>" readonly>
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
                            <label for="bodega_id" class="form-label">Bodega Destino *</label>
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
                            <h6 class="mb-0">Productos a Comprar</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="agregarProductoCompra()">
                                <i class="fas fa-plus me-1"></i>Agregar Producto
                            </button>
                        </div>
                        <div id="productosContainer">
                            <!-- Los productos se agregarán dinámicamente -->
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_entrega_esperada" class="form-label">Fecha de Entrega Esperada</label>
                            <input type="date" class="form-control" id="fecha_entrega_esperada" name="fecha_entrega_esperada">
                        </div>
                        <div class="col-md-6">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago">
                                <option value="">Seleccionar método...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" 
                                  rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript se carga desde app.js en index.php --> 