<?php
// Vista para ver todos los movimientos - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener todos los movimientos consolidados
try {
    $stmt = $pdo->prepare("
        SELECT 
            'entrada' as tipo_movimiento,
            e.fecha,
            e.id as movimiento_id,
            'Entrada' as descripcion,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            ed.cantidad,
            ed.precio_unitario,
            u.nombre as usuario_nombre,
            pr.nombre as proveedor_nombre,
            NULL as cliente_nombre,
            b.nombre as bodega_nombre
        FROM entradas e
        JOIN entradas_detalle ed ON e.id = ed.entrada_id
        JOIN productos p ON ed.producto_id = p.id
        JOIN usuario u ON e.usuario_id = u.id
        LEFT JOIN proveedor pr ON e.proveedor_id = pr.id
        LEFT JOIN bodega b ON ed.bodega_id = b.id
        
        UNION ALL
        
        SELECT 
            'salida' as tipo_movimiento,
            s.fecha,
            s.id as movimiento_id,
            'Salida' as descripcion,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            sd.cantidad,
            sd.precio_unitario,
            u.nombre as usuario_nombre,
            NULL as proveedor_nombre,
            c.nombre as cliente_nombre,
            b.nombre as bodega_nombre
        FROM salidas s
        JOIN salidas_detalle sd ON s.id = sd.salida_id
        JOIN productos p ON sd.producto_id = p.id
        JOIN usuario u ON s.usuario_id = u.id
        LEFT JOIN cliente c ON s.cliente_id = c.id
        LEFT JOIN bodega b ON sd.bodega_id = b.id
        
        UNION ALL
        
        SELECT 
            'traslado' as tipo_movimiento,
            t.fecha,
            t.id as movimiento_id,
            CONCAT('Traslado de ', b1.nombre, ' a ', b2.nombre) as descripcion,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            td.cantidad,
            NULL as precio_unitario,
            u.nombre as usuario_nombre,
            NULL as proveedor_nombre,
            NULL as cliente_nombre,
            CONCAT(b1.nombre, ' → ', b2.nombre) as bodega_nombre
        FROM traslados t
        JOIN traslados_detalle td ON t.id = td.traslado_id
        JOIN productos p ON td.producto_id = p.id
        JOIN usuario u ON t.usuario_id = u.id
        JOIN bodega b1 ON t.bodega_origen_id = b1.id
        JOIN bodega b2 ON t.bodega_destino_id = b2.id
        
        UNION ALL
        
        SELECT 
            'garantia' as tipo_movimiento,
            g.fecha,
            g.id as movimiento_id,
            CASE WHEN g.tipo = 'entrada' THEN 'Garantía Entrada' ELSE 'Garantía Salida' END as descripcion,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            gd.cantidad,
            gd.precio_unitario,
            u.nombre as usuario_nombre,
            NULL as proveedor_nombre,
            c.nombre as cliente_nombre,
            b.nombre as bodega_nombre
        FROM garantias g
        JOIN garantias_detalle gd ON g.id = gd.garantia_id
        JOIN productos p ON gd.producto_id = p.id
        JOIN usuario u ON g.usuario_id = u.id
        LEFT JOIN cliente c ON g.cliente_id = c.id
        LEFT JOIN bodega b ON gd.bodega_id = b.id
        
        UNION ALL
        
        SELECT 
            'devolucion' as tipo_movimiento,
            d.fecha,
            d.id as movimiento_id,
            CASE WHEN d.tipo = 'entrada' THEN 'Devolución Entrada' ELSE 'Devolución Salida' END as descripcion,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            dd.cantidad,
            dd.precio_unitario,
            u.nombre as usuario_nombre,
            NULL as proveedor_nombre,
            c.nombre as cliente_nombre,
            b.nombre as bodega_nombre
        FROM devoluciones d
        JOIN devoluciones_detalle dd ON d.id = dd.devolucion_id
        JOIN productos p ON dd.producto_id = p.id
        JOIN usuario u ON d.usuario_id = u.id
        LEFT JOIN cliente c ON d.cliente_id = c.id
        LEFT JOIN bodega b ON dd.bodega_id = b.id
        
        ORDER BY fecha DESC
        LIMIT 1000
    ");
    $stmt->execute();
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $movimientos = [];
}

// Obtener estadísticas por tipo
$stats = [
    'entradas' => 0,
    'salidas' => 0,
    'traslados' => 0,
    'garantias' => 0,
    'devoluciones' => 0
];

foreach ($movimientos as $mov) {
    $stats[$mov['tipo_movimiento']]++;
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-list me-2"></i>Ver Todos los Movimientos
        </h1>
    </div>
</div>

<!-- Estadísticas por tipo de movimiento -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card border-success shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Entradas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['entradas']; ?></div>
                <i class="fas fa-arrow-down fa-2x text-success mt-2"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card border-danger shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Salidas</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['salidas']; ?></div>
                <i class="fas fa-arrow-up fa-2x text-danger mt-2"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card border-info shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Traslados</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['traslados']; ?></div>
                <i class="fas fa-exchange-alt fa-2x text-info mt-2"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card border-warning shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Garantías</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['garantias']; ?></div>
                <i class="fas fa-shield-alt fa-2x text-warning mt-2"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card border-secondary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Devoluciones</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['devoluciones']; ?></div>
                <i class="fas fa-undo fa-2x text-secondary mt-2"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card border-primary shadow h-100 py-2">
            <div class="card-body text-center">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo array_sum($stats); ?></div>
                <i class="fas fa-chart-bar fa-2x text-primary mt-2"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filtros
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" id="filtroTipo">
                            <option value="">Todos</option>
                            <option value="entrada">Entradas</option>
                            <option value="salida">Salidas</option>
                            <option value="traslado">Traslados</option>
                            <option value="garantia">Garantías</option>
                            <option value="devolucion">Devoluciones</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="fechaDesde">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="fechaHasta">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="filtroProducto" placeholder="Buscar producto...">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button class="btn btn-primary" onclick="aplicarFiltros()">
                            <i class="fas fa-search me-1"></i>Aplicar Filtros
                        </button>
                        <button class="btn btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </button>
                        <button class="btn btn-success" onclick="exportarMovimientos()">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>Movimientos del Inventario
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($movimientos)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaMovimientos">
                            <thead class="table-primary">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Usuario</th>
                                    <th>Cliente/Proveedor</th>
                                    <th>Bodega</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr class="movimiento-row" 
                                        data-tipo="<?php echo $mov['tipo_movimiento']; ?>"
                                        data-fecha="<?php echo $mov['fecha']; ?>"
                                        data-producto="<?php echo strtolower($mov['producto_nombre']); ?>">
                                        <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'secondary';
                                            $icon = 'question';
                                            switch ($mov['tipo_movimiento']) {
                                                case 'entrada':
                                                    $badge_class = 'success';
                                                    $icon = 'arrow-down';
                                                    break;
                                                case 'salida':
                                                    $badge_class = 'danger';
                                                    $icon = 'arrow-up';
                                                    break;
                                                case 'traslado':
                                                    $badge_class = 'info';
                                                    $icon = 'exchange-alt';
                                                    break;
                                                case 'garantia':
                                                    $badge_class = 'warning';
                                                    $icon = 'shield-alt';
                                                    break;
                                                case 'devolucion':
                                                    $badge_class = 'secondary';
                                                    $icon = 'undo';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <i class="fas fa-<?php echo $icon; ?> me-1"></i>
                                                <?php echo ucfirst($mov['tipo_movimiento']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['descripcion']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mov['producto_nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($mov['producto_codigo']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $mov['cantidad']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($mov['precio_unitario']): ?>
                                                $<?php echo number_format($mov['precio_unitario'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['usuario_nombre']); ?></td>
                                        <td>
                                            <?php if ($mov['cliente_nombre']): ?>
                                                <span class="text-info"><?php echo htmlspecialchars($mov['cliente_nombre']); ?></span>
                                            <?php elseif ($mov['proveedor_nombre']): ?>
                                                <span class="text-success"><?php echo htmlspecialchars($mov['proveedor_nombre']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['bodega_nombre']); ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="verDetalleMovimiento('<?php echo $mov['tipo_movimiento']; ?>', <?php echo $mov['movimiento_id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Ver
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay movimientos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function aplicarFiltros() {
    const tipo = document.getElementById('filtroTipo').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    const producto = document.getElementById('filtroProducto').value.toLowerCase();
    
    const filas = document.querySelectorAll('.movimiento-row');
    
    filas.forEach(fila => {
        let mostrar = true;
        
        // Filtro por tipo
        if (tipo && fila.dataset.tipo !== tipo) {
            mostrar = false;
        }
        
        // Filtro por producto
        if (producto && !fila.dataset.producto.includes(producto)) {
            mostrar = false;
        }
        
        // Filtro por fecha (implementar si es necesario)
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

function limpiarFiltros() {
    document.getElementById('filtroTipo').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    document.getElementById('filtroProducto').value = '';
    
    const filas = document.querySelectorAll('.movimiento-row');
    filas.forEach(fila => {
        fila.style.display = '';
    });
}

function verDetalleMovimiento(tipo, id) {
    // Cargar detalle del movimiento según el tipo
    const url = `controlador/ControladorIndex.php?action=detalle_movimiento&tipo=${tipo}&id=${id}`;
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            // Mostrar en modal o nueva ventana
            const ventana = window.open('', '_blank', 'width=800,height=600');
            ventana.document.write(html);
            ventana.document.close();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el detalle del movimiento');
        });
}

function exportarMovimientos() {
    // Exportar movimientos filtrados
    const tipo = document.getElementById('filtroTipo').value;
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    
    const url = `controlador/ControladorIndex.php?action=exportar_movimientos&tipo=${tipo}&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;
    window.location.href = url;
}

// Inicializar DataTable
$(document).ready(function() {
    $('#tablaMovimientos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 50,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script> 