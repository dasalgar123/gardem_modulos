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
    <style>
        .border-start {
            border-left-width: 4px !important;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            border-bottom: 1px solid #e3e6f0;
        }
        
        .btn-group .btn {
            border-radius: 0.35rem;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 0.35rem;
            border-bottom-left-radius: 0.35rem;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 0.35rem;
            border-bottom-right-radius: 0.35rem;
        }
    </style>
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
                        <div class="card border-start border-primary shadow h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Total Productos
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
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
                        <div class="card border-start border-success shadow h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                            Disponible
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
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
                        <div class="card border-start border-warning shadow h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                            Stock Bajo
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
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
                        <div class="card border-start border-danger shadow h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                            Agotado
                                        </div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
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
                <div class="card shadow-sm">
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
                    
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i> Inventario - Por Variante
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary"><?php echo count($inventario); ?> variantes</span>
                            <span class="badge bg-<?php echo $estado_funcionamiento === 'OK' ? 'success' : ($estado_funcionamiento === 'ERROR' ? 'danger' : ($estado_funcionamiento === 'ADVERTENCIA' ? 'warning' : 'info')); ?>">
                                <i class="fas fa-<?php echo $estado_funcionamiento === 'OK' ? 'check-circle' : ($estado_funcionamiento === 'ERROR' ? 'times-circle' : 'info-circle'); ?> me-1"></i>
                                <?php echo $estado_funcionamiento; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Información de verificación -->
                    <div class="card-body py-2 bg-light border-bottom">
                        <small class="text-muted">
                            <strong>Verificación:</strong> 
                            Total: <?php echo $total_variantes; ?> | 
                            Con datos: <?php echo $productos_con_datos; ?> | 
                            Sin datos: <?php echo $productos_sin_datos; ?> | 
                            Nombres largos: <?php echo $productos_con_nombres_completos; ?> | 
                            Estado: <?php echo $mensaje_estado; ?>
                        </small>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="inventarioTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Precio</th>
                                    <th>Color</th>
                                    <th>Talla</th>
                                    <th>Stock Disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inventario)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">No se encontraron productos con los filtros aplicados</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($inventario as $item): ?>
                                    <tr class="<?php echo ControladorInventario::getStockColor($item['saldo']); ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['producto_nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($item['tipo_producto'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $item['color'] ?? 'N/A'; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $item['talla'] ?? 'N/A'; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($item['saldo'] > 10): ?>
                                                <span class="badge bg-success"><?php echo $item['saldo']; ?></span>
                                            <?php elseif ($item['saldo'] > 0): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $item['saldo']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Agotado</span>
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
    </div>
</div>

<!-- Scripts para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/autotable.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/inventario.js"></script>
</body>
</html> 