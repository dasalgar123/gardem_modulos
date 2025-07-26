<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener inventario detallado por producto+color+talla
try {
    $stmt = $pdo->query("
        SELECT 
            p.id as producto_id,
            p.nombre as producto,
            p.referencia,
            p.tipo_producto,
            p.precio,
            c.nombre as color,
            t.nombre as talla,
            COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) as total_entradas,
            COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0) as total_salidas,
            (COALESCE(SUM(CASE WHEN pe.id IS NOT NULL THEN pe.cantidad ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN ps.id IS NOT NULL THEN ps.cantidad ELSE 0 END), 0)) as saldo
        FROM productos p
        LEFT JOIN productos_entradas pe ON p.id = pe.producto_id
        LEFT JOIN productos_salidas ps ON p.id = ps.producto_id 
            AND (pe.color_id = ps.color_id OR (pe.color_id IS NULL AND ps.color_id IS NULL))
            AND (pe.talla_id = ps.talla_id OR (pe.talla_id IS NULL AND ps.talla_id IS NULL))
        LEFT JOIN colores c ON pe.color_id = c.id
        LEFT JOIN tallas t ON pe.talla_id = t.id
        WHERE pe.id IS NOT NULL
        GROUP BY p.id, p.nombre, p.referencia, p.tipo_producto, p.precio, c.nombre, t.nombre, pe.color_id, pe.talla_id
        ORDER BY p.nombre, c.nombre, t.nombre
    ");
    $inventario = $stmt->fetchAll();
} catch (Exception $e) {
    $inventario = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario - Por Variante</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Inventario Detallado por Producto+Color+Talla</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Referencia</th>
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
                        <?php if (empty($inventario)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay productos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventario as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['producto']); ?></strong>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo ucfirst($item['tipo_producto'] ?? 'N/A'); ?></span></td>
                                    <td><span class="badge bg-success">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $item['color'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $item['talla'] ?? 'N/A'; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $item['total_entradas']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $item['total_salidas']; ?></span></td>
                                    <td><span class="badge bg-dark"><?php echo $item['saldo']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 