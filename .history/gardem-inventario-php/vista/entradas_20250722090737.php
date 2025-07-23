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

// Procesar guardado si es POST
$controladorEntradas->guardarEntrada();

// Obtener datos
$entradas = $controladorEntradas->obtenerEntradas();

// Obtener productos
try {
    $stmt = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
}

// Obtener bodegas
try {
    $stmt = $pdo->query("SELECT id, nombre FROM bodega ORDER BY nombre");
    $bodegas = $stmt->fetchAll();
} catch (Exception $e) {
    $bodegas = [];
}

// Verificar mensajes
$mensaje_exito = isset($_GET['success']) && $_GET['success'] == 1;
$mensaje_error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-plus-circle text-success me-2"></i>Entradas de Inventario</h1>
    </div>

    <!-- Mensajes de éxito y error -->
    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> La entrada ha sido registrada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario Simple -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Nueva Entrada</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=entradas">
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                    </div>
                    <div class="col-md-4">
                        <label for="bodega_id" class="form-label">Bodega</label>
                        <select class="form-select" id="bodega_id" name="bodega_id">
                            <option value="">Seleccionar bodega...</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>">
                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Guardar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Entradas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Entradas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Color</th>
                            <th>Talla</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th>Beneficiario</th>
                            <th>Factura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay entradas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entradas as $entrada): ?>
                                <tr>
                                    <td><?php echo $entrada['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($entrada['producto']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo $entrada['color'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $entrada['talla'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $entrada['cantidad']; ?></span></td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($entrada['motivo'] ?? 'N/A'); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($entrada['beneficiario'] ?? 'N/A'); ?></small></td>
                                    <td><code><?php echo htmlspecialchars($entrada['factura_remision'] ?? 'N/A'); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>