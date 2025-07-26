<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once '../controlador/ControladorInventario.php';

// Crear instancia del controlador
$controladorInventario = new ControladorInventario($pdo);

// Obtener datos usando el controlador
$datos = $controladorInventario->obtenerDatosInventario();

$inventario = $datos['inventario'];
$error_inventario = $datos['error_inventario'];
$filtros = $datos['filtros'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Sistema de Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid bg-light min-vh-100">
    <div class="row">
        <div class="col-12">
            <div class="bg-white shadow-sm border-bottom mb-4">
                <div class="container-fluid py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h3 mb-0 text-primary">
                                <i class="fas fa-warehouse me-2"></i> Inventario
                            </h1>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary btn-sm" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="exportToPDF()">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="exportToCSV()">
                                    <i class="fas fa-file-csv me-1"></i> CSV
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="printInventario()">
                                    <i class="fas fa-print me-1"></i> Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <?php if ($error_inventario): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error_inventario; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filtros -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i> Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="producto" class="form-label">Producto:</label>
                                <input type="text" class="form-control" name="producto" id="producto" 
                                       value="<?php echo htmlspecialchars($filtros['producto']); ?>" 
                                       placeholder="Buscar producto...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="stock" class="form-label">Estado del Stock:</label>
                                <select class="form-select" name="stock" id="stock">
                                    <option value="">Todos</option>
                                    <option value="disponible" <?php echo $filtros['stock'] === 'disponible' ? 'selected' : ''; ?>>Disponible (>10)</option>
                                    <option value="bajo" <?php echo $filtros['stock'] === 'bajo' ? 'selected' : ''; ?>>Stock Bajo (1-10)</option>
                                    <option value="agotado" <?php echo $filtros['stock'] === 'agotado' ? 'selected' : ''; ?>>Agotado (0)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Filtrar
                                </button>
                                <a href="?page=inventario" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Productos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count($inventario); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Disponible
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($inventario, function($item) { return $item['saldo'] > 10; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Stock Bajo
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($inventario, function($item) { return $item['saldo'] <= 10 && $item['saldo'] > 0; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Agotado
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo count(array_filter($inventario, function($item) { return $item['saldo'] <= 0; })); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Tabla de Inventario -->
        <div class="inventario-card">
            <?php
            // Lógica de verificación para el inventario
            $total_variantes = count($inventario);
            $productos_con_datos = 0;
            $productos_sin_datos = 0;
            $productos_con_nombres_completos = 0;
            $productos_con_nombres_cortados = 0;
            
            foreach ($inventario as $item) {
                if (!empty($item['producto_nombre']) && $item['producto_nombre'] !== 'N/A') {
                    $productos_con_datos++;
                    
                    // Verificar si el nombre del producto es largo (más de 15 caracteres)
                    if (strlen($item['producto_nombre']) > 15) {
                        $productos_con_nombres_completos++;
                    } else {
                        $productos_con_nombres_cortados++;
                    }
                } else {
                    $productos_sin_datos++;
                }
            }
            
            $estado_funcionamiento = 'OK';
            $mensaje_estado = '';
            
            if ($total_variantes == 0) {
                $estado_funcionamiento = 'ERROR';
                $mensaje_estado = 'No hay datos de inventario';
            } elseif ($productos_sin_datos > $productos_con_datos) {
                $estado_funcionamiento = 'ADVERTENCIA';
                $mensaje_estado = 'Muchos productos sin datos';
            } elseif ($productos_con_nombres_cortados > $productos_con_nombres_completos) {
                $estado_funcionamiento = 'INFO';
                $mensaje_estado = 'Algunos nombres pueden estar cortados';
            } else {
                $mensaje_estado = 'Funcionando correctamente';
            }
            ?>
            
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Inventario - Por Variante</h2>
                <div class="card-actions">
                    <span class="total-items"><?php echo count($inventario); ?> variantes</span>
                    <span class="estado-funcionamiento <?php echo strtolower($estado_funcionamiento); ?>">
                        <i class="fas fa-<?php echo $estado_funcionamiento === 'OK' ? 'check-circle' : ($estado_funcionamiento === 'ERROR' ? 'times-circle' : 'info-circle'); ?>"></i>
                        <?php echo $estado_funcionamiento; ?>
                    </span>
                </div>
            </div>
            
            <!-- Información de verificación -->
            <div class="verificacion-info" style="background: #f8f9fa; padding: 10px 20px; border-bottom: 1px solid #e9ecef; font-size: 12px; color: #666;">
                <strong>Verificación:</strong> 
                Total: <?php echo $total_variantes; ?> | 
                Con datos: <?php echo $productos_con_datos; ?> | 
                Sin datos: <?php echo $productos_sin_datos; ?> | 
                Nombres largos: <?php echo $productos_con_nombres_completos; ?> | 
                Estado: <?php echo $mensaje_estado; ?>
            </div>
            
            <div class="table-container">
                <table class="inventario-table" id="inventarioTable">
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
                        <?php if (empty($inventario)): ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>No se encontraron productos con los filtros aplicados</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($inventario as $item): ?>
                            <tr class="<?php echo ControladorInventario::getStockColor($item['saldo']); ?>">
                                <td class="product-name">
                                    <strong><?php echo htmlspecialchars($item['producto_nombre']); ?></strong>
                                </td>
                                <td class="product-type">
                                    <span class="badge bg-info"><?php echo ucfirst($item['tipo_producto'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="price">
                                    <span class="badge bg-success">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></span>
                                </td>
                                <td class="color">
                                    <span class="badge bg-secondary"><?php echo $item['color'] ?? 'N/A'; ?></span>
                                </td>
                                <td class="size">
                                    <span class="badge bg-secondary"><?php echo $item['talla'] ?? 'N/A'; ?></span>
                                </td>
                                <td class="total-entradas">
                                    <span class="badge bg-primary"><?php echo $item['total_entradas']; ?></span>
                                </td>
                                <td class="total-salidas">
                                    <span class="badge bg-warning"><?php echo $item['total_salidas']; ?></span>
                                </td>
                                <td class="saldo">
                                    <span class="badge bg-light text-dark"><?php echo $item['saldo']; ?></span>
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

<!-- Scripts para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/autotable.min.js"></script>

<script src="../js/inventario.js"></script>
</body>
</html> 