<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controlador/ControladorInventario.php';

// Obtener datos usando el controlador
$controladorInventario = new ControladorInventario($pdo);
$datos = $controladorInventario->obtenerDatosInventario();

$inventario = $datos['inventario'];
$bodegas = $datos['bodegas'];
$bodegas_exists = $datos['bodegas_exists'];
$error_inventario = $datos['error_inventario'];
$filtros = $datos['filtros'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Sistema de Administración</title>

    <link rel="stylesheet" href="../css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Librerías para exportación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/autotable.min.js"></script>
</head>
<body>
    <div class="main-content">
        <header class="content-header">
            <div class="header-content">
                <h1><i class="fas fa-warehouse"></i> Inventario</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button class="btn btn-secondary" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-secondary" onclick="exportToCSV()">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                    <button class="btn btn-secondary" onclick="printInventario()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </header>
        
        <div class="content content-inventario">
            <?php if ($error_inventario): ?>
                <div class="alert alert-danger" style="margin:20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error_inventario; ?>
                </div>
            <?php endif; ?>
            <!-- Filtros -->
            <div class="filters-card">
                <h3><i class="fas fa-filter"></i> Filtros</h3>
                <form method="GET" class="filters-form">
                    <?php if ($bodegas_exists): ?>
                    <div class="filter-group">
                        <label for="bodega">Bodega:</label>
                        <select name="bodega" id="bodega">
                            <option value="">Todas las bodegas</option>
                            <?php foreach ($bodegas as $bodega): ?>
                            <option value="<?php echo $bodega['id']; ?>" 
                                    <?php echo $filtros['bodega'] == $bodega['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bodega['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <label for="producto">Producto:</label>
                        <input type="text" name="producto" id="producto" 
                               value="<?php echo htmlspecialchars($filtros['producto']); ?>" 
                               placeholder="Buscar producto...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="stock">Estado del Stock:</label>
                        <select name="stock" id="stock">
                            <option value="">Todos</option>
                            <option value="disponible" <?php echo $filtros['stock'] === 'disponible' ? 'selected' : ''; ?>>Disponible (>10)</option>
                            <option value="bajo" <?php echo $filtros['stock'] === 'bajo' ? 'selected' : ''; ?>>Stock Bajo (1-10)</option>
                            <option value="agotado" <?php echo $filtros['stock'] === 'agotado' ? 'selected' : ''; ?>>Agotado (0)</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?page=inventario" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Resumen -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Total Productos</h3>
                        <p><?php echo count($inventario); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon stock-disponible">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Disponible</h3>
                        <p><?php echo count(array_filter($inventario, function($item) { return $item['stock_actual'] > 10; })); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon stock-bajo">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Stock Bajo</h3>
                        <p><?php echo count(array_filter($inventario, function($item) { return $item['stock_actual'] <= 10 && $item['stock_actual'] > 0; })); ?></p>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon stock-agotado">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Agotado</h3>
                        <p><?php echo count(array_filter($inventario, function($item) { return $item['stock_actual'] <= 0; })); ?></p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Inventario -->
            <div class="inventario-card">
                <div class="card-header">
                    <h2>Inventario Detallado</h2>
                    <div class="card-actions">
                        <span class="total-items"><?php echo count($inventario); ?> productos</span>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="inventario-table" id="inventarioTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <?php if ($bodegas_exists): ?>
                                <th>Bodega</th>
                                <?php endif; ?>
                                <th>Precio</th>
                                <th>Stock Actual</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inventario)): ?>
                            <tr>
                                <td colspan="<?php echo $bodegas_exists ? 6 : 5; ?>" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>No se encontraron productos con los filtros aplicados</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($inventario as $item): ?>
                                <tr class="<?php echo ControladorInventario::getStockColor($item['stock_actual']); ?>">
                                    <td class="product-id"><?php echo htmlspecialchars($item['producto_id']); ?></td>
                                    <td class="product-name">
                                        <div class="product-info">
                                            <strong><?php echo htmlspecialchars($item['producto_nombre']); ?></strong>
                                            <?php if (!empty($item['descripcion'])): ?>
                                            <small><?php echo htmlspecialchars($item['descripcion']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php if ($bodegas_exists): ?>
                                    <td class="bodega-name"><?php echo htmlspecialchars($item['bodega_nombre']); ?></td>
                                    <?php endif; ?>
                                    <td class="price"><?php echo ControladorInventario::formatPrice($item['precio']); ?></td>
                                    <td class="stock-actual">
                                        <span class="stock-number <?php echo ControladorInventario::getStockColor($item['stock_actual']); ?>">
                                            <?php echo htmlspecialchars($item['stock_actual']); ?>
                                        </span>
                                    </td>
                                    <td class="status">
                                        <span class="status-badge <?php echo ControladorInventario::getStockColor($item['stock_actual']); ?>">
                                            <?php
                                            if ($item['stock_actual'] <= 0) {
                                                echo 'Agotado';
                                            } elseif ($item['stock_actual'] <= 10) {
                                                echo 'Stock Bajo';
                                            } else {
                                                echo 'Disponible';
                                            }
                                            ?>
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

    <script src="../js/inventario.js"></script>
</body>
</html> 