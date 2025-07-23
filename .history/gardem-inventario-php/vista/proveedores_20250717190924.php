<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorProveedores.php';

// Crear instancia del controlador
$controladorProveedores = new ControladorProveedores($pdo);

// Obtener datos
$proveedores = $controladorProveedores->obtenerProveedores();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-truck text-primary me-2"></i>Gestión de Proveedores</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoProveedorModal">
            <i class="fas fa-plus me-2"></i>Nuevo Proveedor
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> El proveedor ha sido registrado correctamente.
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
                            <i class="fas fa-truck fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Proveedores</h6>
                            <h4 class="mb-0"><?php echo count($proveedores); ?></h4>
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
                            <h6 class="card-title mb-0">Activos</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($proveedores, function($p) { return $p['activo']; })); ?></h4>
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
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Con Productos</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($proveedores, function($p) { return !empty($p['total_productos']); })); ?></h4>
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
                            <i class="fas fa-phone fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Con Contacto</h6>
                            <h4 class="mb-0"><?php echo count(array_filter($proveedores, function($p) { return !empty($p['telefono']) || !empty($p['email']); })); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de proveedores -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Proveedores</h5>
        </div>
        <div class="card-body">
            <?php if (empty($proveedores)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-truck fa-3x mb-3"></i>
                    <h5>No se encontraron proveedores</h5>
                    <p>La tabla proveedores está vacía o no existe.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>NIT</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?php echo $proveedor['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($proveedor['nombre']); ?></div>
                                    <?php if (!empty($proveedor['direccion'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($proveedor['direccion']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($proveedor['nit'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['contacto'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($proveedor['telefono'])): ?>
                                        <a href="tel:<?php echo $proveedor['telefono']; ?>" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($proveedor['telefono']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($proveedor['email'])): ?>
                                        <a href="mailto:<?php echo $proveedor['email']; ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($proveedor['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $proveedor['total_productos'] ?? 0; ?> productos</span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $proveedor['activo'] ? 'success' : 'danger'; ?>">
                                        <?php echo $proveedor['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editarProveedor(<?php echo $proveedor['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalleProveedor(<?php echo $proveedor['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="nuevaCompra(<?php echo $proveedor['id']; ?>)">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="eliminarProveedor(<?php echo $proveedor['id']; ?>)">
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

<!-- Modal Nuevo Proveedor -->
<div class="modal fade" id="nuevoProveedorModal" tabindex="-1" aria-labelledby="nuevoProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevoProveedorModalLabel">
                    <i class="fas fa-plus me-2"></i>Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoProveedor" method="POST" action="procesar_proveedor.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Proveedor *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nit" class="form-label">NIT</label>
                                <input type="text" class="form-control" id="nit" name="nit" placeholder="900123456-7">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="contacto" name="contacto">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
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
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript se carga desde app.js en index.php --> 