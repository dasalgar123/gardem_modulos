<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener productos
try {
    $stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre, t.nombre as talla_nombre, co.nombre as color_nombre 
                         FROM productos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         LEFT JOIN tallas t ON p.tallas_id = t.id 
                         LEFT JOIN colores co ON p.color_id = co.id 
                         ORDER BY p.nombre");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-box text-primary me-2"></i>Productos</h1>
    </div>

    <!-- Tabla de Productos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Productos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Tipo</th>
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
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $desc = $producto['descripcion'] ?? '';
                                        echo htmlspecialchars(substr($desc, 0, 50)) . (strlen($desc) > 50 ? '...' : ''); 
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?php echo htmlspecialchars($producto['talla_nombre'] ?? 'Sin talla'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($producto['color_nombre'] ?? 'Sin color'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst($producto['tipo_producto'] ?? 'N/A'); ?>
                                        </span>
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