<?php
// Obtener productos del inventario con stock real
$stmt = $pdo->query("
    SELECT 
        p.*, 
        c.nombre as categoria_nombre,
        COALESCE(ib.stock_actual, 0) as stock_actual,
        COALESCE(itcc.`stock minimo`, 0) as stock_minimo,
        COALESCE(itcc.`stock existente`, 0) as stock_existente
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
    LEFT JOIN inventario_tallas_colores_categorias itcc ON p.id = itcc.producto_id
    ORDER BY p.nombre
");
$productos = $stmt->fetchAll();

// Calcular estadísticas reales
$total_productos = count($productos);
$disponible = 0;
$stock_bajo = 0;
$agotado = 0;

foreach ($productos as $producto) {
    $stock = $producto['stock_actual'] ?? 0;
    if ($stock > 0) {
        $disponible++;
    } elseif ($stock <= 0) {
        $agotado++;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-clipboard-list text-primary me-2"></i>Inventario</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-secondary" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel
            </button>
            <button class="btn btn-secondary" onclick="exportToPDF()">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </button>
            <button class="btn btn-secondary" onclick="exportToCSV()">
                <i class="fas fa-file-csv me-2"></i>CSV
            </button>
            <button class="btn btn-secondary" onclick="printInventario()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="filtroBodega" class="form-label">Bodega</label>
                    <select class="form-select" id="filtroBodega">
                        <option value="">Todas las bodegas</option>
                        <option value="Bodega Principal">Bodega Principal</option>
                        <option value="Bodega niza">Bodega niza</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroProducto" class="form-label">Producto</label>
                    <input type="text" class="form-control" id="filtroProducto" placeholder="Buscar producto...">
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label">Estado del Stock</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button class="btn btn-primary flex-fill" onclick="aplicarFiltros()">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Total Productos</h6>
                            <h4 class="mb-0"><?php echo $total_productos; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Disponible</h6>
                            <h4 class="mb-0"><?php echo $disponible; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Stock Bajo</h6>
                            <h4 class="mb-0"><?php echo $stock_bajo; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-0">Agotado</h6>
                            <h4 class="mb-0"><?php echo $agotado; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de inventario -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list text-primary me-2"></i>Inventario Detallado
            </h5>
            <span class="badge bg-secondary"><?php echo $total_productos; ?> productos</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="inventarioTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">ID</th>
                            <th class="px-3">Producto</th>
                            <th class="px-3">Bodega</th>
                            <th class="px-3">Precio</th>
                            <th class="px-3">Stock</th>
                            <th class="px-3">Estado</th>
                            <th class="px-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <?php 
                            $stock = $producto['stock_actual'] ?? 0;
                            $stock_minimo = $producto['stock_minimo'] ?? 0;
                            
                            // Determinar estado del stock
                            if ($stock <= 0) {
                                $estado = 'agotado';
                                $estado_badge = 'bg-danger';
                                $estado_texto = 'AGOTADO';
                            } elseif ($stock <= $stock_minimo) {
                                $estado = 'bajo';
                                $estado_badge = 'bg-warning';
                                $estado_texto = 'STOCK BAJO';
                            } else {
                                $estado = 'disponible';
                                $estado_badge = 'bg-success';
                                $estado_texto = 'DISPONIBLE';
                            }
                            
                            // Determinar color del badge de stock
                            $stock_badge = $stock > 0 ? 'bg-success' : 'bg-danger';
                            ?>
                            <tr data-producto="<?php echo strtolower($producto['nombre']); ?>" 
                                data-bodega="Bodega Principal" 
                                data-estado="<?php echo $estado; ?>">
                                <td class="px-3">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($producto['id']); ?></span>
                                </td>
                                <td class="px-3">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                        <?php if (!empty($producto['descripcion'])): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                        <?php endif; ?>
                                        <br><small class="text-info"><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></small>
                                    </div>
                                </td>
                                <td class="px-3">
                                    <span class="badge bg-info">Bodega Principal</span>
                                </td>
                                <td class="px-3">
                                    <span class="fw-bold">$<?php echo number_format($producto['precio'] ?? 0, 2); ?></span>
                                </td>
                                <td class="px-3">
                                    <span class="badge <?php echo $stock_badge; ?>">
                                        <?php echo $stock; ?>
                                    </span>
                                    <?php if ($stock_minimo > 0): ?>
                                        <br><small class="text-muted">Mín: <?php echo $stock_minimo; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3">
                                    <span class="badge <?php echo $estado_badge; ?>"><?php echo $estado_texto; ?></span>
                                </td>
                                <td class="px-3">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetalleProducto(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="nuevaEntrada(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="nuevaSalida(<?php echo $producto['id']; ?>)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Función para exportar a Excel
function exportToExcel() {
    alert('Exportar a Excel - Funcionalidad en desarrollo');
}

// Función para exportar a PDF
function exportToPDF() {
    alert('Exportar a PDF - Funcionalidad en desarrollo');
}

// Función para exportar a CSV
function exportToCSV() {
    alert('Exportar a CSV - Funcionalidad en desarrollo');
}

// Función para imprimir
function printInventario() {
    window.print();
}

// Función para aplicar filtros
function aplicarFiltros() {
    const filtroBodega = document.getElementById('filtroBodega').value;
    const filtroProducto = document.getElementById('filtroProducto').value.toLowerCase();
    const filtroEstado = document.getElementById('filtroEstado').value;
    
    const filas = document.querySelectorAll('#inventarioTable tbody tr');
    
    filas.forEach(fila => {
        const producto = fila.getAttribute('data-producto');
        const bodega = fila.getAttribute('data-bodega');
        const estado = fila.getAttribute('data-estado');
        
        let mostrar = true;
        
        // Filtro por bodega
        if (filtroBodega && bodega !== filtroBodega) {
            mostrar = false;
        }
        
        // Filtro por producto
        if (filtroProducto && !producto.includes(filtroProducto)) {
            mostrar = false;
        }
        
        // Filtro por estado
        if (filtroEstado && estado !== filtroEstado) {
            mostrar = false;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroBodega').value = '';
    document.getElementById('filtroProducto').value = '';
    document.getElementById('filtroEstado').value = '';
    
    const filas = document.querySelectorAll('#inventarioTable tbody tr');
    filas.forEach(fila => {
        fila.style.display = '';
    });
}

// Funciones para acciones
function verDetalleProducto(productoId) {
    alert('Ver detalle del producto ' + productoId + ' - Funcionalidad en desarrollo');
}

function nuevaEntrada(productoId) {
    alert('Nueva entrada para producto ' + productoId + ' - Funcionalidad en desarrollo');
}

function nuevaSalida(productoId) {
    alert('Nueva salida para producto ' + productoId + ' - Funcionalidad en desarrollo');
}

// Aplicar filtros al escribir
document.getElementById('filtroProducto').addEventListener('input', aplicarFiltros);
document.getElementById('filtroBodega').addEventListener('change', aplicarFiltros);
document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
</script> 