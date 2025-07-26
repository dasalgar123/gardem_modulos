<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Parámetros de paginación y filtros
$por_pagina = 50;
$pagina = $_GET['pagina'] ?? 1;
$busqueda = $_GET['busqueda'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';
$orden = $_GET['orden'] ?? 'nombre';
$direccion = $_GET['direccion'] ?? 'ASC';

$offset = ($pagina - 1) * $por_pagina;

// Construir consulta optimizada
$where_conditions = [];
$params = [];

// Filtro de búsqueda
if (!empty($busqueda)) {
    $where_conditions[] = "p.nombre LIKE ?";
    $params[] = "%$busqueda%";
}

// Filtro de categoría
if (!empty($categoria)) {
    $where_conditions[] = "p.categoria_id = ?";
    $params[] = $categoria;
}

// Filtro de estado
if (!empty($estado)) {
    switch ($estado) {
        case 'agotado':
            $where_conditions[] = "(COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) = 0";
            break;
        case 'stock_bajo':
            $where_conditions[] = "(COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) < 10";
            break;
        case 'disponible':
            $where_conditions[] = "(COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) >= 10";
            break;
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Consulta optimizada con subconsultas
$query = "
    SELECT 
        p.id,
        p.nombre,
        p.tipo_producto,
        p.precio,
        COALESCE(pe.total_entradas, 0) as total_entradas,
        COALESCE(ps.total_salidas, 0) as total_salidas,
        (COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) as saldo,
        CASE 
            WHEN (COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) = 0 THEN 'Agotado'
            WHEN (COALESCE(pe.total_entradas, 0) - COALESCE(ps.total_salidas, 0)) < 10 THEN 'Stock Bajo'
            ELSE 'Disponible'
        END as estado
    FROM productos p
    LEFT JOIN (
        SELECT producto_id, SUM(cantidad) as total_entradas 
        FROM productos_entradas 
        GROUP BY producto_id
    ) pe ON p.id = pe.producto_id
    LEFT JOIN (
        SELECT producto_id, SUM(cantidad) as total_salidas 
        FROM productos_salidas 
        GROUP BY producto_id
    ) ps ON p.id = ps.producto_id
    $where_clause
    ORDER BY p.$orden $direccion
    LIMIT $por_pagina OFFSET $offset
";

// Consulta para contar total de registros
$count_query = "
    SELECT COUNT(*) as total
    FROM productos p
    LEFT JOIN (
        SELECT producto_id, SUM(cantidad) as total_entradas 
        FROM productos_entradas 
        GROUP BY producto_id
    ) pe ON p.id = pe.producto_id
    LEFT JOIN (
        SELECT producto_id, SUM(cantidad) as total_salidas 
        FROM productos_salidas 
        GROUP BY producto_id
    ) ps ON p.id = ps.producto_id
    $where_clause
";

try {
    // Ejecutar consulta de conteo
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_registros = $stmt->fetch()['total'];
    
    // Calcular total de páginas
    $total_paginas = ceil($total_registros / $por_pagina);
    
    // Ejecutar consulta principal
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $inventario = $stmt->fetchAll();
    
    // Obtener categorías para filtro
    $stmt = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
} catch (Exception $e) {
    $inventario = [];
    $total_registros = 0;
    $total_paginas = 0;
    $categorias = [];
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="exportarInventario()">
                <i class="fas fa-download me-1"></i>Exportar
            </button>
            <button class="btn btn-info" onclick="actualizarInventario()">
                <i class="fas fa-sync-alt me-1"></i>Actualizar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="inventario">
                
                <div class="col-md-3">
                    <label class="form-label">Buscar Producto</label>
                    <input type="text" class="form-control" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Nombre del producto...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="">Todos</option>
                        <option value="disponible" <?php echo $estado == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="stock_bajo" <?php echo $estado == 'stock_bajo' ? 'selected' : ''; ?>>Stock Bajo</option>
                        <option value="agotado" <?php echo $estado == 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Ordenar por</label>
                    <select class="form-select" name="orden">
                        <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                        <option value="precio" <?php echo $orden == 'precio' ? 'selected' : ''; ?>>Precio</option>
                        <option value="saldo" <?php echo $orden == 'saldo' ? 'selected' : ''; ?>>Stock</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Dirección</label>
                    <select class="form-select" name="direccion">
                        <option value="ASC" <?php echo $direccion == 'ASC' ? 'selected' : ''; ?>>Ascendente</option>
                        <option value="DESC" <?php echo $direccion == 'DESC' ? 'selected' : ''; ?>>Descendente</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Productos</h6>
                    <h4><?php echo number_format($total_registros); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Disponibles</h6>
                    <h4><?php echo number_format(array_reduce($inventario, function($carry, $item) { return $carry + ($item['estado'] == 'Disponible' ? 1 : 0); }, 0)); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6>Stock Bajo</h6>
                    <h4><?php echo number_format(array_reduce($inventario, function($carry, $item) { return $carry + ($item['estado'] == 'Stock Bajo' ? 1 : 0); }, 0)); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Agotados</h6>
                    <h4><?php echo number_format(array_reduce($inventario, function($carry, $item) { return $carry + ($item['estado'] == 'Agotado' ? 1 : 0); }, 0)); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de inventario -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Inventario Detallado
                <small class="text-muted ms-2">(<?php echo number_format($total_registros); ?> productos)</small>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventario)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No se encontraron productos
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventario as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['nombre']); ?></strong>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo number_format($item['total_entradas']); ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo number_format($item['total_salidas']); ?></span></td>
                                    <td>
                                        <?php 
                                        $saldo_class = match(true) {
                                            $item['saldo'] == 0 => 'bg-danger',
                                            $item['saldo'] < 10 => 'bg-warning',
                                            default => 'bg-success'
                                        };
                                        ?>
                                        <span class="badge <?php echo $saldo_class; ?>"><?php echo number_format($item['saldo']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación de inventario">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=inventario&pagina=<?php echo $pagina-1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado=<?php echo $estado; ?>&orden=<?php echo $orden; ?>&direccion=<?php echo $direccion; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagina-2); $i <= min($total_paginas, $pagina+2); $i++): ?>
                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=inventario&pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado=<?php echo $estado; ?>&orden=<?php echo $orden; ?>&direccion=<?php echo $direccion; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagina < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=inventario&pagina=<?php echo $pagina+1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado=<?php echo $estado; ?>&orden=<?php echo $orden; ?>&direccion=<?php echo $direccion; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportarInventario() {
    // Simular exportación
    alert('Exportando inventario...');
    // Aquí se implementaría la exportación real
}

function actualizarInventario() {
    location.reload();
}

// Auto-refresh cada 5 minutos
setTimeout(function() {
    if (confirm('¿Desea actualizar el inventario automáticamente?')) {
        location.reload();
    }
}, 300000); // 5 minutos
</script> 