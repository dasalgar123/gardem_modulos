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

// Procesar guardado si es POST
$controladorSalidas->guardarSalida();

// Obtener datos
$salidas = $controladorSalidas->obtenerSalidas();

// Obtener productos con códigos automáticos
try {
    $stmt = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre");
    $productos_raw = $stmt->fetchAll();
    
    // Generar códigos automáticos
    $productos = [];
    foreach ($productos_raw as $producto) {
        $producto['codigo'] = 'PROD-' . str_pad($producto['id'], 3, '0', STR_PAD_LEFT);
        $productos[] = $producto;
    }
    
    echo "<div class='alert alert-info'>Productos encontrados: " . count($productos) . "</div>";
} catch (Exception $e) {
    $productos = [];
    echo "<div class='alert alert-danger'>Error productos: " . $e->getMessage() . "</div>";
}

// Obtener clientes
try {
    $stmt = $pdo->query("SELECT id, nombre FROM cliente ORDER BY nombre");
    $clientes = $stmt->fetchAll();
    echo "<div class='alert alert-info'>Clientes encontrados: " . count($clientes) . "</div>";
} catch (Exception $e) {
    $clientes = [];
    echo "<div class='alert alert-danger'>Error clientes: " . $e->getMessage() . "</div>";
}

// Verificar mensajes
$mensaje_exito = isset($_GET['success']) && $_GET['success'] == 1;
$mensaje_error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-arrow-up text-danger me-2"></i>Salidas de Inventario</h1>
    </div>

    <!-- Mensajes de éxito y error -->
    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> La salida ha sido registrada correctamente.
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
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Nueva Salida</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=salidas">
                <div class="row">
                    <div class="col-md-4">
                        <label for="codigo_producto" class="form-label">Código Producto *</label>
                        <input type="text" class="form-control" id="codigo_producto" name="codigo_producto" 
                               placeholder="Ej: PROD-010" required>
                        <small class="text-muted">Escribe el código del producto</small>
                    </div>
                    <div class="col-md-4">
                        <label for="producto_info" class="form-label">Información del Producto</label>
                        <input type="text" class="form-control" id="producto_info" readonly 
                               placeholder="Se cargará automáticamente">
                        <input type="hidden" id="producto_id" name="producto_id">
                    </div>
                    <div class="col-md-4">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>">
                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-2"></i>Registrar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Salidas -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Salidas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Cliente</th>
                            <th>Motivo</th>
                            <th>Factura</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salidas)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay salidas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salidas as $salida): ?>
                                <tr>
                                    <td><?php echo $salida['id'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($salida['producto_nombre'] ?? 'N/A'); ?></td>
                                    <td><?php echo $salida['cantidad'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($salida['cliente_nombre'] ?? 'N/A'); ?></td>
                                    <td><?php echo ucfirst($salida['motivo'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($salida['factura_remision'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 

<script>
// Datos de productos para autocompletar
const productos = <?php echo json_encode($productos); ?>;

// Función para buscar producto por código
function buscarProductoPorCodigo(codigo) {
    return productos.find(producto => producto.codigo === codigo.toUpperCase());
}

// Event listener para el campo de código
document.getElementById('codigo_producto').addEventListener('input', function() {
    const codigo = this.value.toUpperCase();
    const producto = buscarProductoPorCodigo(codigo);
    
    if (producto) {
        // Mostrar información del producto
        document.getElementById('producto_info').value = producto.nombre;
        document.getElementById('producto_id').value = producto.id;
        
        // Cambiar color del campo a verde (encontrado)
        document.getElementById('producto_info').style.backgroundColor = '#d4edda';
        document.getElementById('producto_info').style.borderColor = '#28a745';
    } else {
        // Limpiar información
        document.getElementById('producto_info').value = '';
        document.getElementById('producto_id').value = '';
        
        // Cambiar color del campo a rojo (no encontrado)
        document.getElementById('producto_info').style.backgroundColor = '#f8d7da';
        document.getElementById('producto_info').style.borderColor = '#dc3545';
    }
});

// Event listener para Enter en el campo de código
document.getElementById('codigo_producto').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('cantidad').focus();
    }
});

// Event listener para Enter en cantidad
document.getElementById('cantidad').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('cliente_id').focus();
    }
});
</script> 