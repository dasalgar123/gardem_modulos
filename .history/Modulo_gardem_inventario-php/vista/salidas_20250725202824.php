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

// Procesar edición si es POST con editar_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $controladorSalidas->editarSalida();
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $controladorSalidas->eliminarSalida($_GET['eliminar']);
}

// Verificar si estamos editando
$editando = isset($_GET['editar']);
$salida_editar = null;

if ($editando) {
    $salida_editar = $controladorSalidas->obtenerSalida($_GET['editar']);
}

// Obtener datos con color y talla
try {
    $stmt = $pdo->query("
        SELECT 
            ps.id,
            p.nombre as producto,
            c.nombre as color,
            t.nombre as talla,
            ps.cantidad,
            ps.fecha,
            ps.motivo,
            ps.beneficiario,
            ps.factura_remision as factura,
            ps.producto_id,
            ps.color_id,
            ps.talla_id,
            ps.bodega_id,
            ps.beneficiario_id
        FROM productos_salidas ps
        LEFT JOIN productos p ON ps.producto_id = p.id
        LEFT JOIN colores c ON ps.color_id = c.id
        LEFT JOIN tallas t ON ps.talla_id = t.id
        ORDER BY ps.fecha DESC
    ");
    $salidas = $stmt->fetchAll();
} catch (Exception $e) {
    $salidas = [];
    echo "<div class='alert alert-danger'>Error al cargar salidas: " . $e->getMessage() . "</div>";
}

// Obtener productos con códigos automáticos
try {
    $stmt = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre");
    $productos_raw = $stmt->fetchAll();
    
    // Generar códigos automáticos (solo el ID)
    $productos = [];
    foreach ($productos_raw as $producto) {
        $producto['codigo'] = $producto['id'];
        $productos[] = $producto;
    }
    
    echo "<div class='alert alert-info'>Salidas registradas: " . count($salidas) . "</div>";
} catch (Exception $e) {
    $productos = [];
    echo "<div class='alert alert-danger'>Error productos: " . $e->getMessage() . "</div>";
}

// Obtener clientes
try {
    $stmt = $pdo->query("SELECT id, nombre FROM cliente ORDER BY nombre");
    $clientes = $stmt->fetchAll();
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

    <!-- Formulario de Edición -->
    <?php if ($editando && $salida_editar): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Salida</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info text-center" style="font-size:1.3em;">
                <strong>Editando registro ID: <?php echo $salida_editar['id']; ?></strong>
            </div>
            <form method="POST" action="index.php?page=salidas">
                <input type="hidden" name="editar_id" value="<?php echo $salida_editar['id']; ?>">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Producto *</label>
                        <select class="form-select" name="producto_id" required>
                            <option value="">Seleccionar producto...</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" <?php if ($producto['id'] == $salida_editar['producto_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Referencia</label>
                        <input type="text" class="form-control" name="referencia" value="<?php echo htmlspecialchars($salida_editar['referencia'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bodega</label>
                        <select class="form-select" name="bodega_id">
                            <option value="">Seleccionar bodega...</option>
                            <?php
                            $bodegas = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM bodega ORDER BY nombre");
                                $bodegas = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>" <?php if ($bodega['id'] == $salida_editar['bodega_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" name="cantidad" required value="<?php echo $salida_editar['cantidad']; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha</label>
                        <input type="datetime-local" class="form-control" name="fecha" value="<?php echo date('Y-m-d\TH:i', strtotime($salida_editar['fecha'])); ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Motivo</label>
                        <select class="form-select" name="motivo">
                            <option value="venta" <?php if ($salida_editar['motivo'] == 'venta') echo 'selected'; ?>>Venta</option>
                            <option value="devolución cliente" <?php if ($salida_editar['motivo'] == 'devolución cliente') echo 'selected'; ?>>Devolución Cliente</option>
                            <option value="ajuste" <?php if ($salida_editar['motivo'] == 'ajuste') echo 'selected'; ?>>Ajuste</option>
                            <option value="traslado" <?php if ($salida_editar['motivo'] == 'traslado') echo 'selected'; ?>>Traslado</option>
                            <option value="garantía" <?php if ($salida_editar['motivo'] == 'garantía') echo 'selected'; ?>>Garantía</option>
                            <option value="otro" <?php if ($salida_editar['motivo'] == 'otro') echo 'selected'; ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario_tipo" value="<?php echo htmlspecialchars($salida_editar['beneficiario_tipo'] ?? 'cliente'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ID Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario_id" value="<?php echo htmlspecialchars($salida_editar['beneficiario_id']); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nombre Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario" value="<?php echo htmlspecialchars($salida_editar['beneficiario']); ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Color</label>
                        <select class="form-select" name="color_id">
                            <option value="">Seleccionar color...</option>
                            <?php
                            $colores = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM colores ORDER BY nombre");
                                $colores = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($colores as $color): ?>
                                <option value="<?php echo $color['id']; ?>" <?php if ($color['id'] == $salida_editar['color_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($color['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Talla</label>
                        <select class="form-select" name="talla_id">
                            <option value="">Seleccionar talla...</option>
                            <?php
                            $tallas = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM tallas ORDER BY nombre");
                                $tallas = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($tallas as $talla): ?>
                                <option value="<?php echo $talla['id']; ?>" <?php if ($talla['id'] == $salida_editar['talla_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($talla['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Factura/Remisión</label>
                        <input type="text" class="form-control" name="factura_remision" value="<?php echo htmlspecialchars($salida_editar['factura'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                    <a href="index.php?page=salidas" class="btn btn-secondary ms-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario Simple -->
    <?php if (!$editando): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Nueva Salida</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=salidas">
                <div class="row">
                    <div class="col-md-3">
                        <label for="codigo_producto" class="form-label">Código Producto *</label>
                        <input type="text" class="form-control" id="codigo_producto" name="codigo_producto" 
                               placeholder="Ej: 1, 10, 25" required>
                        <small class="text-muted">Escribe el ID del producto</small>
                    </div>
                    <div class="col-md-3">
                        <label for="producto_info" class="form-label">Información del Producto</label>
                        <input type="text" class="form-control" id="producto_info" readonly 
                               placeholder="Se cargará automáticamente">
                        <input type="hidden" id="producto_id" name="producto_id">
                    </div>
                    <div class="col-md-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                    </div>
                    <div class="col-md-3">
                        <label for="bodega_id" class="form-label">Bodega</label>
                        <select class="form-select" id="bodega_id" name="bodega_id">
                            <option value="">Seleccionar bodega...</option>
                            <?php
                            $bodegas = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM bodega ORDER BY nombre");
                                $bodegas = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>">
                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="color_id" class="form-label">Color</label>
                        <select class="form-select" id="color_id" name="color_id">
                            <option value="">Seleccionar color...</option>
                            <?php
                            $colores = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM colores ORDER BY nombre");
                                $colores = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($colores as $color): ?>
                                <option value="<?php echo $color['id']; ?>">
                                    <?php echo htmlspecialchars($color['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="talla_id" class="form-label">Talla</label>
                        <select class="form-select" id="talla_id" name="talla_id">
                            <option value="">Seleccionar talla...</option>
                            <?php
                            $tallas = [];
                            try {
                                $stmt = $pdo->query("SELECT id, nombre FROM tallas ORDER BY nombre");
                                $tallas = $stmt->fetchAll();
                            } catch (Exception $e) {}
                            ?>
                            <?php foreach ($tallas as $talla): ?>
                                <option value="<?php echo $talla['id']; ?>">
                                    <?php echo htmlspecialchars($talla['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="venta">Venta</option>
                            <option value="devolución cliente">Devolución Cliente</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="traslado">Traslado</option>
                            <option value="garantía">Garantía</option>
                            <option value="otro">Otro</option>
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
    <?php endif; ?>

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
                            <th>Color</th>
                            <th>Talla</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th>Beneficiario</th>
                            <th>Factura</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salidas)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay salidas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salidas as $salida): ?>
                                <tr>
                                    <td><?php echo $salida['id'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($salida['producto'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($salida['color']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($salida['color']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin color</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($salida['talla']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($salida['talla']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin talla</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $salida['cantidad'] ?? 'N/A'; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst($salida['motivo'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($salida['beneficiario'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($salida['factura'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?page=salidas&editar=<?php echo $salida['id']; ?>" 
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=salidas&eliminar=<?php echo $salida['id']; ?>" 
                                               class="btn btn-danger btn-sm" title="Eliminar"
                                               onclick="return confirm('¿Estás seguro de eliminar esta salida?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

<script>
// Datos de productos para autocompletar
const productos = <?php echo json_encode($productos); ?>;

// Función para buscar producto por código
function buscarProductoPorCodigo(codigo) {
    return productos.find(producto => producto.codigo == codigo);
}

// Event listener para el campo de código
document.getElementById('codigo_producto').addEventListener('input', function() {
    const codigo = this.value; // No toUpperCase() si es numérico
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