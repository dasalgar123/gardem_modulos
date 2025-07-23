<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener parámetros del reporte
$tipo = $_GET['tipo'] ?? 'general';
$fecha_desde = $_GET['desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['hasta'] ?? date('Y-m-d');

// Obtener datos según el tipo de reporte
$datos_reporte = [];
$titulo_reporte = '';

switch ($tipo) {
    case 'inventario':
        $titulo_reporte = 'Reporte de Inventario';
        $datos_reporte = obtenerInventarioDetallado();
        break;
    case 'ventas':
        $titulo_reporte = 'Reporte de Ventas';
        $datos_reporte = obtenerProductosMasVendidos(10);
        break;
    case 'movimientos':
        $titulo_reporte = 'Reporte de Movimientos';
        $datos_reporte = obtenerMovimientosMensuales(6);
        break;
    default:
        $titulo_reporte = 'Reporte General';
        $stats = obtenerEstadisticas();
        $datos_reporte = [
            'productos' => $stats['productos'],
            'proveedores' => obtenerProveedoresActivos(),
            'entradas_mes' => obtenerEntradasDelMes(),
            'salidas_mes' => obtenerSalidasDelMes(),
            'stock_bajo' => $stats['stock_bajo'],
            'agotados' => $stats['agotados']
        ];
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_reporte; ?> - Gardem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
        }
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .report-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .table-report {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header del Reporte -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-chart-bar me-3"></i>
                        <?php echo $titulo_reporte; ?>
                    </h1>
                    <p class="mb-0 mt-2">Sistema de Gestión de Almacén Gardem</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-light me-2">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                        <a href="index.php?page=reportes" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Reporte -->
        <div class="report-info">
            <div class="row">
                <div class="col-md-4">
                    <strong>Tipo de Reporte:</strong> <?php echo ucfirst($tipo); ?>
                </div>
                <div class="col-md-4">
                    <strong>Fecha Desde:</strong> <?php echo date('d/m/Y', strtotime($fecha_desde)); ?>
                </div>
                <div class="col-md-4">
                    <strong>Fecha Hasta:</strong> <?php echo date('d/m/Y', strtotime($fecha_hasta)); ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <strong>Generado por:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </div>
                <div class="col-md-4">
                    <strong>Fecha de Generación:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                </div>
                <div class="col-md-4">
                    <strong>Total Registros:</strong> <?php echo count($datos_reporte); ?>
                </div>
            </div>
        </div>

        <!-- Contenido del Reporte -->
        <?php if ($tipo == 'general'): ?>
            <!-- Reporte General -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="fas fa-box fa-3x text-primary mb-3"></i>
                        <h3><?php echo $datos_reporte['productos']; ?></h3>
                        <p class="text-muted">Productos en Catálogo</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="fas fa-truck fa-3x text-success mb-3"></i>
                        <h3><?php echo $datos_reporte['proveedores']; ?></h3>
                        <p class="text-muted">Proveedores Activos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="fas fa-arrow-down fa-3x text-info mb-3"></i>
                        <h3><?php echo $datos_reporte['entradas_mes']; ?></h3>
                        <p class="text-muted">Entradas del Mes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <i class="fas fa-arrow-up fa-3x text-warning mb-3"></i>
                        <h3><?php echo $datos_reporte['salidas_mes']; ?></h3>
                        <p class="text-muted">Salidas del Mes</p>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo == 'inventario'): ?>
            <!-- Reporte de Inventario -->
            <div class="card table-report">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Inventario Detallado</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Valor Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos_reporte as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $producto['stock'] == 0 ? 'bg-danger' : ($producto['stock'] < 10 ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $producto['stock']; ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                                    <td>$<?php echo number_format($producto['valor_total'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $producto['estado'] == 'Agotado' ? 'bg-danger' : ($producto['estado'] == 'Stock Bajo' ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $producto['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo == 'movimientos'): ?>
            <!-- Reporte de Movimientos -->
            <div class="card table-report">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Movimientos Mensuales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-center">Entradas</th>
                                    <th class="text-center">Salidas</th>
                                    <th class="text-center">Balance</th>
                                    <th class="text-center">% Entradas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos_reporte as $movimiento): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($movimiento['mes']); ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $movimiento['entradas']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?php echo $movimiento['salidas']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $balance = $movimiento['entradas'] - $movimiento['salidas'];
                                        $balance_class = $balance >= 0 ? 'bg-success' : 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $balance_class; ?>">
                                            <?php echo $balance >= 0 ? '+' . $balance : $balance; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $total = $movimiento['entradas'] + $movimiento['salidas'];
                                        $porcentaje = $total > 0 ? round(($movimiento['entradas'] / $total) * 100, 1) : 0;
                                        ?>
                                        <span class="badge bg-info"><?php echo $porcentaje; ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo == 'ventas'): ?>
            <!-- Reporte de Ventas -->
            <div class="card table-report">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Productos Más Vendidos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Ventas</th>
                                    <th class="text-center">Stock Actual</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos_reporte as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $producto['ventas']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $producto['stock'] == 0 ? 'bg-danger' : ($producto['stock'] < 10 ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $producto['stock']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $producto['stock'] == 0 ? 'bg-danger' : ($producto['stock'] < 10 ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $producto['stock'] == 0 ? 'Agotado' : ($producto['stock'] < 10 ? 'Stock Bajo' : 'Disponible'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pie del Reporte -->
        <div class="print-break"></div>
        <div class="mt-5 pt-4 border-top">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <strong>Generado el:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
                        <strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Sistema de Gestión de Almacén Gardem<br>
                        Versión 1.0
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 