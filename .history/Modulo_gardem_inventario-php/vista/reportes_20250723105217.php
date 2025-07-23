<?php
// Página de Reportes del Sistema de Almacenista

// Obtener estadísticas para los reportes
$stats = obtenerEstadisticas();

// Obtener datos reales de la base de datos
$productos_mas_vendidos = obtenerProductosMasVendidos(5);
$movimientos_mensuales = obtenerMovimientosMensuales(6);
$stock_por_categoria = obtenerStockPorCategoria();
$inventario_detallado = obtenerInventarioDetallado();
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-chart-bar me-2"></i>Reportes del Sistema
        </h1>
    </div>
</div>

<!-- Filtros de reportes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-filter me-2"></i>Filtros de Reportes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Reporte</label>
                        <select class="form-select" id="tipoReporte">
                            <option value="general">Reporte General</option>
                            <option value="ventas">Reporte de Ventas</option>
                            <option value="inventario">Reporte de Inventario</option>
                            <option value="movimientos">Reporte de Movimientos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="fechaDesde" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="fechaHasta" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="generarReporte()">
                            <i class="fas fa-search me-2"></i>Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5><i class="fas fa-box"></i> Total Productos</h5>
                <h3><?php echo $stats['productos']; ?></h3>
                <small>En inventario</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-arrow-down"></i> Entradas Mes</h5>
                <h3><?php echo $stats['movimientos_mes']; ?></h3>
                <small>Movimientos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5><i class="fas fa-exclamation-triangle"></i> Alertas</h5>
                <h3><?php echo $stats['agotados'] + $stats['stock_bajo']; ?></h3>
                <small>Productos críticos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5><i class="fas fa-truck"></i> Proveedores</h5>
                <h3><?php echo $stats['proveedores']; ?></h3>
                <small>Activos</small>
            </div>
        </div>
    </div>
</div>

<!-- Reporte de productos más vendidos -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-trophy me-2"></i>Productos Más Vendidos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Ventas</th>
                                <th>Stock Actual</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_mas_vendidos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $producto['ventas']; ?></span>
                                </td>
                                <td><?php echo $producto['stock']; ?></td>
                                <td>
                                    <?php if ($producto['stock'] == 0): ?>
                                        <span class="badge bg-danger">Agotado</span>
                                    <?php elseif ($producto['stock'] < 10): ?>
                                        <span class="badge bg-warning">Stock Bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie me-2"></i>Stock por Categoría</h5>
            </div>
            <div class="card-body">
                <?php foreach ($datos_reportes['stock_por_categoria'] as $categoria): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span><?php echo $categoria['categoria']; ?></span>
                        <span class="fw-bold"><?php echo $categoria['stock']; ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php 
                        $porcentaje = ($categoria['stock'] / 300) * 100; // 300 es el máximo estimado
                        ?>
                        <div class="progress-bar bg-primary" style="width: <?php echo $porcentaje; ?>%"></div>
                    </div>
                    <small class="text-muted">$<?php echo number_format($categoria['valor'], 0, ',', '.'); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de movimientos mensuales -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line me-2"></i>Movimientos Mensuales</h5>
            </div>
            <div class="card-body">
                <canvas id="movimientosChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Reporte detallado de inventario -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-clipboard-list me-2"></i>Reporte Detallado de Inventario</h5>
                <button class="btn btn-success btn-sm" onclick="exportarReporte()">
                    <i class="fas fa-download me-2"></i>Exportar PDF
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Valor Total</th>
                                <th>Estado</th>
                                <th>Último Movimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>001</td>
                                <td>Boxer Clásico</td>
                                <td>Ropa Interior</td>
                                <td>23</td>
                                <td>$25,000</td>
                                <td>$575,000</td>
                                <td><span class="badge bg-success">Disponible</span></td>
                                <td>Hoy 10:30</td>
                            </tr>
                            <tr>
                                <td>002</td>
                                <td>Boxer Deportivo</td>
                                <td>Ropa Deportiva</td>
                                <td>15</td>
                                <td>$35,000</td>
                                <td>$525,000</td>
                                <td><span class="badge bg-warning">Stock Bajo</span></td>
                                <td>Hoy 09:15</td>
                            </tr>
                            <tr>
                                <td>003</td>
                                <td>Camiseta Básica</td>
                                <td>Ropa Casual</td>
                                <td>67</td>
                                <td>$15,000</td>
                                <td>$1,005,000</td>
                                <td><span class="badge bg-success">Disponible</span></td>
                                <td>Ayer 16:45</td>
                            </tr>
                            <tr>
                                <td>004</td>
                                <td>Pantalón Deportivo</td>
                                <td>Ropa Deportiva</td>
                                <td>12</td>
                                <td>$45,000</td>
                                <td>$540,000</td>
                                <td><span class="badge bg-warning">Stock Bajo</span></td>
                                <td>Hoy 11:20</td>
                            </tr>
                            <tr>
                                <td>005</td>
                                <td>Calcetines Pack 3</td>
                                <td>Accesorios</td>
                                <td>0</td>
                                <td>$12,000</td>
                                <td>$0</td>
                                <td><span class="badge bg-danger">Agotado</span></td>
                                <td>Hoy 08:30</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de movimientos mensuales
const ctx = document.getElementById('movimientosChart').getContext('2d');
const movimientosData = <?php echo json_encode($datos_reportes['movimientos_mensuales']); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: movimientosData.map(item => item.mes),
        datasets: [{
            label: 'Entradas',
            data: movimientosData.map(item => item.entradas),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Salidas',
            data: movimientosData.map(item => item.salidas),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Movimientos Mensuales'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Función para generar reporte
function generarReporte() {
    const tipo = document.getElementById('tipoReporte').value;
    const desde = document.getElementById('fechaDesde').value;
    const hasta = document.getElementById('fechaHasta').value;
    
    alert(`Generando reporte de ${tipo} desde ${desde} hasta ${hasta}`);
    // Aquí se implementaría la lógica real de generación de reportes
}

// Función para exportar reporte
function exportarReporte() {
    alert('Exportando reporte a PDF...');
    // Aquí se implementaría la lógica de exportación a PDF
}
</script> 