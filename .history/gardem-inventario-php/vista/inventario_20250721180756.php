<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorInventario.php';

// Crear instancia del controlador
$controladorInventario = new ControladorInventario($pdo);

// Obtener datos usando el controlador
$inventario = $controladorInventario->obtenerInventario();
$stats = $controladorInventario->obtenerEstadisticasInventario();
$total_productos = $stats['total_productos'];

// Obtener productos del inventario con categorías y bodegas
$stmt = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre, b.nombre as bodega_nombre, 0 as stock_actual, 
           0 as stock_minimo,
           1000 as stock_maximo
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodega b ON p.bodega_id = b.id
    ORDER BY p.nombre
");
$productos = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-clipboard-list"></i> Inventario</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>ID</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Stock Máximo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                        No hay productos en el inventario
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td><?php echo $producto['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                            <?php if ($producto['descripcion']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($producto['id']); ?></code></td>
                                        <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $producto['stock_actual'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $producto['stock_actual']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $producto['stock_minimo']; ?></td>
                                        <td><?php echo $producto['stock_maximo']; ?></td>
                                        <td>
                                            <?php if ($producto['stock_actual'] <= 0): ?>
                                                <span class="badge bg-danger">Sin Stock</span>
                                            <?php elseif ($producto['stock_actual'] <= $producto['stock_minimo']): ?>
                                                <span class="badge bg-warning">Stock Bajo</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="ajustarStock(<?php echo $producto['id']; ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Producto -->
<div class="modal fade" id="agregarProductoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAgregarProducto">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" name="categoria">
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="precio" name="precio" step="0.01">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock_actual" class="form-label">Stock Actual</label>
                                <input type="number" class="form-control" id="stock_actual" name="stock_actual" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock_maximo" class="form-label">Stock Máximo</label>
                                <input type="number" class="form-control" id="stock_maximo" name="stock_maximo" value="1000" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarProducto(id) {
    alert('Editar producto ' + id + ' - Funcionalidad en desarrollo');
}

function ajustarStock(id) {
    alert('Ajustar stock del producto ' + id + ' - Funcionalidad en desarrollo');
}

function eliminarProducto(id) {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        alert('Eliminar producto ' + id + ' - Funcionalidad en desarrollo');
    }
}

document.getElementById('formAgregarProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Agregar producto - Funcionalidad en desarrollo');
});
</script> 