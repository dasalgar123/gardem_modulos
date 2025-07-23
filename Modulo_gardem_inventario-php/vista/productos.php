<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener productos - CONSULTA SIMPLE
try {
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY nombre");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-box text-primary me-2"></i>Productos</h1>
    </div>

    <!-- Debug info -->
    <div class="alert alert-info">
        <strong>Debug:</strong> Encontrados <?php echo count($productos); ?> productos
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
                            <th>Categoría ID</th>
                            <th>Talla ID</th>
                            <th>Color ID</th>
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
                                    <td><?php echo $producto['categoria_id'] ?? 'N/A'; ?></td>
                                    <td><?php echo $producto['tallas_id'] ?? 'N/A'; ?></td>
                                    <td><?php echo $producto['color_id'] ?? 'N/A'; ?></td>
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