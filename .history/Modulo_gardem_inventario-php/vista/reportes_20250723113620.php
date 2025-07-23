<?php
// Página de Reportes del Sistema de Almacenista

// Obtener estadísticas para los reportes
$stats = obtenerEstadisticas();

// Obtener datos reales de la base de datos
$productos_mas_vendidos = obtenerProductosMasVendidos(5);
$movimientos_mensuales = obtenerMovimientosMensuales(6);
$stock_por_categoria = obtenerStockPorCategoria();
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
                <h5><i class="fas fa-box"></i> En Catalogo</h5>
                <h3><?php echo $stats['productos']; ?></h3>
                <small>En Catálogo</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5><i class="fas fa-arrow-down"></i> Entradas Mes</h5>
                <h3><?php echo obtenerEntradasDelMes(); ?></h3>
                <small>Este mes</small>
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
                <h3><?php echo obtenerProveedoresActivos(); ?></h3>
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
                <?php foreach ($stock_por_categoria as $categoria): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span><?php echo htmlspecialchars($categoria['categoria']); ?></span>
                        <span class="fw-bold"><?php echo $categoria['stock']; ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php 
                        $max_stock = max(array_column($stock_por_categoria, 'stock')) ?: 1;
                        $porcentaje = ($categoria['stock'] / $max_stock) * 100;
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

<!-- Tabla detallada de movimientos mensuales -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table me-2"></i>Detalle de Movimientos Mensuales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">
                                    <i class="fas fa-arrow-down text-success me-1"></i>Entradas
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-arrow-up text-danger me-1"></i>Salidas
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-calculator text-info me-1"></i>Balance
                                </th>
                                <th class="text-center">
                                    <i class="fas fa-chart-pie text-warning me-1"></i>% Entradas
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos_mensuales as $movimiento): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($movimiento['mes']); ?></strong></td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6"><?php echo $movimiento['entradas']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger fs-6"><?php echo $movimiento['salidas']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $balance = $movimiento['entradas'] - $movimiento['salidas'];
                                    $balance_class = $balance >= 0 ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $balance_class; ?> fs-6">
                                        <?php echo $balance >= 0 ? '+' . $balance : $balance; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $total = $movimiento['entradas'] + $movimiento['salidas'];
                                    $porcentaje = $total > 0 ? round(($movimiento['entradas'] / $total) * 100, 1) : 0;
                                    ?>
                                    <span class="badge bg-info fs-6"><?php echo $porcentaje; ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td><strong>TOTALES</strong></td>
                                <td class="text-center">
                                    <strong class="text-success">
                                        <?php echo array_sum(array_column($movimientos_mensuales, 'entradas')); ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-danger">
                                        <?php echo array_sum(array_column($movimientos_mensuales, 'salidas')); ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $total_entradas = array_sum(array_column($movimientos_mensuales, 'entradas'));
                                    $total_salidas = array_sum(array_column($movimientos_mensuales, 'salidas'));
                                    $balance_total = $total_entradas - $total_salidas;
                                    $balance_total_class = $balance_total >= 0 ? 'text-success' : 'text-danger';
                                    ?>
                                    <strong class="<?php echo $balance_total_class; ?>">
                                        <?php echo $balance_total >= 0 ? '+' . $balance_total : $balance_total; ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $total_movimientos = $total_entradas + $total_salidas;
                                    $porcentaje_total = $total_movimientos > 0 ? round(($total_entradas / $total_movimientos) * 100, 1) : 0;
                                    ?>
                                    <strong class="text-info"><?php echo $porcentaje_total; ?>%</strong>
                                </td>
                            </tr>
                        </tfoot>
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
const movimientosData = <?php echo json_encode($movimientos_mensuales); ?>;

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
    
    // Mostrar mensaje de carga
    const mensaje = `Generando reporte de ${tipo} desde ${desde} hasta ${hasta}`;
    
    // Crear un modal de carga
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const contenido = document.createElement('div');
    contenido.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    `;
    
    contenido.innerHTML = `
        <div style="margin-bottom: 20px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2em; color: #007bff;"></i>
        </div>
        <h4>Generando Reporte</h4>
        <p>${mensaje}</p>
        <div style="margin-top: 20px;">
            <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                    style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Cerrar
            </button>
        </div>
    `;
    
    modal.appendChild(contenido);
    document.body.appendChild(modal);
    
    // Simular procesamiento y luego actualizar la página
    setTimeout(() => {
        // Guardar los filtros en localStorage para mantenerlos
        localStorage.setItem('reporte_tipo', tipo);
        localStorage.setItem('reporte_desde', desde);
        localStorage.setItem('reporte_hasta', hasta);
        
        // Recargar la página con los nuevos filtros
        window.location.reload();
    }, 2000);
}

// Función para exportar reporte
function exportarReporte() {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const contenido = document.createElement('div');
    contenido.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    `;
    
    contenido.innerHTML = `
        <div style="margin-bottom: 20px;">
            <i class="fas fa-file-pdf" style="font-size: 2em; color: #dc3545;"></i>
        </div>
        <h4>Exportando a PDF</h4>
        <p>Preparando el reporte para descarga...</p>
        <div style="margin-top: 20px;">
            <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                    style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Cerrar
            </button>
        </div>
    `;
    
    modal.appendChild(contenido);
    document.body.appendChild(modal);
    
    // Simular exportación
    setTimeout(() => {
        alert('Reporte exportado exitosamente (simulado)');
        modal.remove();
    }, 3000);
}

// Cargar filtros guardados al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const tipo = localStorage.getItem('reporte_tipo');
    const desde = localStorage.getItem('reporte_desde');
    const hasta = localStorage.getItem('reporte_hasta');
    
    if (tipo) document.getElementById('tipoReporte').value = tipo;
    if (desde) document.getElementById('fechaDesde').value = desde;
    if (hasta) document.getElementById('fechaHasta').value = hasta;
});
</script> 