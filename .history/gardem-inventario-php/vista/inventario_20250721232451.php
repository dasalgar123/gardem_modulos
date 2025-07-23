<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener inventario con stock calculado - CONSULTA SIMPLE
try {
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.tipo_producto,
            COALESCE(entradas.total_entradas, 0) as total_entradas,
            COALESCE(salidas.total_salidas, 0) as total_salidas,
            (COALESCE(entradas.total_entradas, 0) - COALESCE(salidas.total_salidas, 0)) as stock_actual
        FROM productos p 
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_entradas 
            FROM productos_entradas 
            GROUP BY producto_id
        ) entradas ON p.id = entradas.producto_id
        LEFT JOIN (
            SELECT producto_id, SUM(cantidad) as total_salidas 
            FROM productos_salidas 
            GROUP BY producto_id
        ) salidas ON p.id = salidas.producto_id
        ORDER BY p.nombre
    ");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Calcular estadísticas
$total_productos = count($productos);
$disponible = 0;
$agotado = 0;

foreach ($productos as $producto) {
    if ($producto['stock_actual'] > 0) {
        $disponible++;
    } else {
        $agotado++;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario</h1>
    </div>

    <!-- Debug info -->
    <div class="alert alert-info">
        <strong>Debug:</strong> Encontrados <?php echo count($productos); ?> productos en inventario
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
                    <h5><i class="fas fa-check-circle"></i> Disponible</h5>
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
                    <h5><i class="fas fa-times-circle"></i> Agotado</h5>
                    <h3><?php echo $agotado; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Inventario Detallado</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                            <th>Stock Actual</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay productos en inventario
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                        <?php if (!empty($producto['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($producto['tipo_producto'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $producto['total_entradas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo $producto['total_salidas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo $producto['stock_actual']; ?></strong>
                                        <br><small class="text-muted">E: <?php echo $producto['total_entradas']; ?> | S: <?php echo $producto['total_salidas']; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($producto['stock_actual'] > 0): ?>
                                            <span class="badge bg-success">DISPONIBLE</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">AGOTADO</span>
                                        <?php endif; ?>
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