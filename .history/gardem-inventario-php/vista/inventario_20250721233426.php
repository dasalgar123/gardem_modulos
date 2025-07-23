<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener entradas individuales - SIN SUMAR
try {
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.tipo_producto,
            pe.id as entrada_id,
            pe.cantidad,
            pe.fecha,
            pe.motivo,
            pe.beneficiario,
            pe.factura_remision
        FROM productos p 
        LEFT JOIN productos_entradas pe ON p.id = pe.producto_id
        ORDER BY p.nombre, pe.fecha DESC
    ");
    $entradas = $stmt->fetchAll();
} catch (Exception $e) {
    $entradas = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Agrupar por producto para estadísticas
$productos_unicos = [];
$total_productos = 0;
$disponible = 0;
$agotado = 0;

foreach ($entradas as $entrada) {
    $producto_id = $entrada['id'];
    if (!isset($productos_unicos[$producto_id])) {
        $productos_unicos[$producto_id] = $entrada;
        $total_productos++;
        if ($entrada['cantidad'] > 0) {
            $disponible++;
        } else {
            $agotado++;
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario - Entradas Individuales</h1>
    </div>

    <!-- Debug info -->
    <div class="alert alert-info">
        <strong>Debug:</strong> Encontradas <?php echo count($entradas); ?> entradas individuales
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5><i class="fas fa-box"></i> Total Productos</h5>
                    <h3><?php echo $total_productos; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5><i class="fas fa-check-circle"></i> Con Entradas</h5>
                    <h3><?php echo $disponible; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5><i class="fas fa-times-circle"></i> Sin Entradas</h5>
                    <h3><?php echo $agotado; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Entradas Individuales -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Entradas Individuales por Producto</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto ID</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Entrada ID</th>
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
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay entradas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entradas as $entrada): ?>
                                <tr>
                                    <td><?php echo $entrada['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($entrada['nombre']); ?></strong>
                                        <?php if (!empty($entrada['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($entrada['descripcion'], 0, 30)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($entrada['tipo_producto'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            $<?php echo number_format($entrada['precio'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $entrada['entrada_id']; ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $entrada['cantidad']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($entrada['motivo'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($entrada['beneficiario'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($entrada['factura_remision'] ?? 'N/A'); ?></code>
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