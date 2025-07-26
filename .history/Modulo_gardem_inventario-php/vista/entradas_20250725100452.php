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

// Eliminar entrada si corresponde
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $stmt = $pdo->prepare("DELETE FROM productos_entradas WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>alert('Entrada eliminada correctamente'); window.location.href='index.php?page=entradas';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error al eliminar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=entradas';</script>";
        exit;
    }
}

// Editar entrada si corresponde
$editando = false;
$entrada_editar = null;
if (isset($_GET['editar'])) {
    $editando = true;
    $id = intval($_GET['editar']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM productos_entradas WHERE id = ?");
        $stmt->execute([$id]);
        $entrada_editar = $stmt->fetch();
    } catch (Exception $e) {
        $entrada_editar = null;
    }
}

// Guardar edición
if (isset($_POST['editar_id'])) {
    $id = intval($_POST['editar_id']);
    $producto_id = $_POST['producto_id'] ?? '';
    $cantidad = $_POST['cantidad'] ?? 0;
    $bodega_id = $_POST['bodega_id'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $motivo = $_POST['motivo'] ?? null;
    $beneficiario_tipo = $_POST['beneficiario_tipo'] ?? null;
    $beneficiario_id = $_POST['beneficiario_id'] ?? null;
    $factura_remision = $_POST['factura_remision'] ?? null;
    $beneficiario = $_POST['beneficiario'] ?? null;
    $color_id = $_POST['color_id'] ?? null;
    $talla_id = $_POST['talla_id'] ?? null;
    try {
        $stmt = $pdo->prepare("UPDATE productos_entradas SET producto_id=?, cantidad=?, bodega_id=?, fecha=?, motivo=?, beneficiario_tipo=?, beneficiario_id=?, factura_remision=?, beneficiario=?, color_id=?, talla_id=? WHERE id=?");
        $stmt->execute([$producto_id, $cantidad, $bodega_id, $fecha, $motivo, $beneficiario_tipo, $beneficiario_id, $factura_remision, $beneficiario, $color_id, $talla_id, $id]);
        echo "<script>alert('Entrada actualizada correctamente'); window.location.href='index.php?page=entradas';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error al actualizar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?page=entradas';</script>";
        exit;
    }
}

// Obtener historial de entradas con color y talla desde productos_entradas
try {
    $stmt = $pdo->query("SELECT pe.id, pe.producto_id, pe.cantidad, pe.fecha, pe.motivo, pe.beneficiario, pe.factura_remision, 
        p.nombre as producto, p.referencia, c.nombre as color, t.nombre as talla
        FROM productos_entradas pe
        LEFT JOIN productos p ON pe.producto_id = p.id
        LEFT JOIN colores c ON pe.color_id = c.id
        LEFT JOIN tallas t ON pe.talla_id = t.id
        ORDER BY pe.fecha DESC");
    $entradas = $stmt->fetchAll();
} catch (Exception $e) {
    $entradas = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

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
        <h1 class="h2"><i class="fas fa-arrow-down text-primary me-2"></i>Entradas - Historial</h1>
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                    </div>
                    <div class="col-md-3">
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
                </div>
                <div class="row mt-2">
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
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Guardar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulario de edición si corresponde -->
    <?php if ($editando && $entrada_editar): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Entrada</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info text-center" style="font-size:1.3em;">
                <strong>Editando registro ID: <?php echo $entrada_editar['id']; ?></strong>
            </div>
            <form method="POST" action="index.php?page=entradas">
                <input type="hidden" name="editar_id" value="<?php echo $entrada_editar['id']; ?>">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Producto *</label>
                        <select class="form-select" name="producto_id" required>
                            <option value="">Seleccionar producto...</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" <?php if ($producto['id'] == $entrada_editar['producto_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bodega</label>
                        <select class="form-select" name="bodega_id">
                            <option value="">Seleccionar bodega...</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>" <?php if ($bodega['id'] == $entrada_editar['bodega_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" name="cantidad" required value="<?php echo $entrada_editar['cantidad']; ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="datetime-local" class="form-control" name="fecha" value="<?php echo date('Y-m-d\TH:i', strtotime($entrada_editar['fecha'])); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Motivo</label>
                        <input type="text" class="form-control" name="motivo" value="<?php echo htmlspecialchars($entrada_editar['motivo']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Factura/Remisión</label>
                        <input type="text" class="form-control" name="factura_remision" value="<?php echo htmlspecialchars($entrada_editar['factura_remision']); ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario_tipo" value="<?php echo htmlspecialchars($entrada_editar['beneficiario_tipo']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ID Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario_id" value="<?php echo htmlspecialchars($entrada_editar['beneficiario_id']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre Beneficiario</label>
                        <input type="text" class="form-control" name="beneficiario" value="<?php echo htmlspecialchars($entrada_editar['beneficiario']); ?>">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
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
                                <option value="<?php echo $color['id']; ?>" <?php if ($color['id'] == $entrada_editar['color_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($color['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                                <option value="<?php echo $talla['id']; ?>" <?php if ($talla['id'] == $entrada_editar['talla_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($talla['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                    <a href="index.php?page=entradas" class="btn btn-secondary ms-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

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
                            <th>Referencia</th>
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
                        <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay entradas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entradas as $entrada): ?>
                                <tr>
                                    <td><?php echo $entrada['id']; ?></td>
                                    <td><strong><?php echo !empty($entrada['producto']) ? htmlspecialchars($entrada['producto']) : 'N/A'; ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo !empty($entrada['color']) ? $entrada['color'] : 'N/A'; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo !empty($entrada['talla']) ? $entrada['talla'] : 'N/A'; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $entrada['cantidad']; ?></span></td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($entrada['motivo'] ?? 'N/A'); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($entrada['beneficiario'] ?? 'N/A'); ?></small></td>
                                    <td><code><?php echo htmlspecialchars($entrada['factura_remision'] ?? 'N/A'); ?></code></td>
                                    <td>
                                        <a href="?page=entradas&editar=<?php echo $entrada['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?page=entradas&eliminar=<?php echo $entrada['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta entrada?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
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