<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener productos con color y talla
try {
    $stmt = $pdo->query("SELECT p.id, p.nombre, p.descripcion, p.precio, p.tipo_producto, c.nombre AS color, t.nombre AS talla FROM productos p
        LEFT JOIN colores c ON p.color_id = c.id
        LEFT JOIN tallas t ON p.tallas_id = t.id
        ORDER BY p.nombre");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Obtener totales de entradas por producto
try {
    $stmt = $pdo->query("SELECT producto_id, SUM(cantidad) as total_entradas FROM productos_entradas GROUP BY producto_id");
    $entradas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $entradas = [];
}

// Obtener totales de salidas por producto
try {
    $stmt = $pdo->query("SELECT producto_id, SUM(cantidad) as total_salidas FROM productos_salidas GROUP BY producto_id");
    $salidas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    $salidas = [];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario - Resumen por Producto</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Inventario General</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Color</th>
                            <th>Talla</th>
                            <th>Total Entradas</th>
                            <th>Total Salidas</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay productos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $producto): ?>
                                <?php
                                    $id = $producto['id'];
                                    $total_entradas = isset($entradas[$id]) ? (int)$entradas[$id] : 0;
                                    $total_salidas = isset($salidas[$id]) ? (int)$salidas[$id] : 0;
                                    $saldo = $total_entradas - $total_salidas;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                        <?php if (!empty($producto['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 30)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo ucfirst($producto['tipo_producto'] ?? 'N/A'); ?></span></td>
                                    <td><span class="badge bg-success">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $producto['color'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $producto['talla'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $total_entradas; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $total_salidas; ?></span></td>
                                    <td><span class="badge bg-dark"><?php echo $saldo; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 