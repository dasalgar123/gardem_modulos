<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Obtener proveedores
try {
    $stmt = $pdo->query("SELECT * FROM proveedor ORDER BY nombre");
    $proveedores = $stmt->fetchAll();
} catch (Exception $e) {
    $proveedores = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-truck text-primary me-2"></i>Proveedores</h1>
    </div>

    <!-- Debug info -->
    <div class="alert alert-info">
        <strong>Debug:</strong> Encontrados <?php echo count($proveedores); ?> proveedores
    </div>

    <!-- Tabla de Proveedores -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Proveedores</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Dirección</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proveedores)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-truck fa-2x mb-2"></i><br>
                                    No hay proveedores registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <tr>
                                    <td><?php echo $proveedor['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($proveedor['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($proveedor['telefono']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($proveedor['correo']); ?>">
                                            <?php echo htmlspecialchars($proveedor['correo']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($proveedor['direccion']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo date('d/m/Y', strtotime($proveedor['fecha_creacion'])); ?>
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