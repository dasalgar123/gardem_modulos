<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorProductos.php';

// Crear instancia del controlador
$controladorProductos = new ControladorProductos($pdo);

// Obtener datos
$productos = $controladorProductos->obtenerProductos();
$proveedores = $controladorProductos->obtenerProveedores();
$colores = $controladorProductos->obtenerColores();
$tallas = $controladorProductos->obtenerTallas();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-box text-primary me-2"></i>Gestión de Productos</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoProductoModal">
            <i class="fas fa-plus me-2"></i>Nuevo Producto
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> El producto ha sido registrado correctamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Tabla de productos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Productos (<?php echo count($productos); ?> registros)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($productos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box fa-3x mb-3"></i>
                    <h5>No se encontraron productos</h5>
                    <p>La tabla productos está vacía o no existe.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Costo</th>
                                <th>Proveedor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo $producto['id']; ?></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                        <?php if (!empty($producto['descripcion'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($producto['tipo_producto']); ?></span>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td>$<?php echo number_format($producto['costo'], 2); ?></td>
                                <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?? 'Sin proveedor'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $producto['activo'] ? 'success' : 'danger'; ?>">
                                        <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalleProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="nuevoProductoModal" tabindex="-1" aria-labelledby="nuevoProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevoProductoModalLabel">
                    <i class="fas fa-plus me-2"></i>Nuevo Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoProducto" method="POST" action="procesar_producto.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo_producto" class="form-label">Tipo de Producto *</label>
                                <select class="form-select" id="tipo_producto" name="tipo_producto" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="caballero">Caballero</option>
                                    <option value="dama">Dama</option>
                                    <option value="unisex">Unisex</option>
                                    <option value="accesorio">Accesorio</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio de Venta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="costo" class="form-label">Costo *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="costo" name="costo" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
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
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="activo" class="form-label">Estado</label>
                                <select class="form-select" id="activo" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarProducto(productoId) {
    // Implementar edición de producto
    alert('Función de edición en desarrollo para producto ID: ' + productoId);
}

function verDetalleProducto(productoId) {
    // Implementar vista detallada
    alert('Función de detalle en desarrollo para producto ID: ' + productoId);
}

function eliminarProducto(productoId) {
    if (confirm('¿Está seguro de que desea eliminar este producto?')) {
        // Implementar eliminación
        alert('Función de eliminación en desarrollo para producto ID: ' + productoId);
    }
}

// Validación del formulario
document.getElementById('formNuevoProducto').addEventListener('submit', function(e) {
    const precio = parseFloat(document.getElementById('precio').value);
    const costo = parseFloat(document.getElementById('costo').value);
    
    if (precio < costo) {
        e.preventDefault();
        alert('El precio de venta no puede ser menor al costo.');
        return false;
    }
});
</script> 