<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorInventario.php';

// Crear instancia del controlador
$controladorInventario = new ControladorInventario($pdo);

// Obtener datos usando el controlador
$inventario = $controladorInventario->obtenerInventario();
$stats = $controladorInventario->obtenerEstadisticasInventario();
$total_productos = $stats['total_productos'];
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
            <button class="btn btn-secondary" onclick="printInventario()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
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
                            <h4 class="mb-0"><?php echo $stats['total_productos']; ?></h4>
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
                            <h4 class="mb-0"><?php echo $stats['productos_disponibles']; ?></h4>
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
                            <h4 class="mb-0"><?php echo $stats['productos_stock_bajo']; ?></h4>
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
                            <h4 class="mb-0"><?php echo $stats['productos_agotados']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
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
                    <label for="filtroProducto" class="form-label">Producto</label>
                    <input type="text" class="form-control" id="filtroProducto" placeholder="Buscar producto...">
                </div>
                <div class="col-md-3">
                    <label for="filtroTipo" class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos los tipos</option>
                        <option value="caballero">Caballero</option>
                        <option value="dama">Dama</option>
                        <option value="unisex">Unisex</option>
                        <option value="accesorio">Accesorio</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroStock" class="form-label">Estado Stock</label>
                    <select class="form-select" id="filtroStock">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                        <i class="fas fa-search me-2"></i>Aplicar Filtros
                    </button>
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
            <?php if (empty($inventario)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <h5>No se encontraron productos</h5>
                    <p>El inventario está vacío o no hay productos disponibles.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="inventarioTable">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">ID</th>
                                <th class="px-3">Producto</th>
                                <th class="px-3">Tipo</th>
                                <th class="px-3">Precio</th>
                                <th class="px-3">Stock</th>
                                <th class="px-3">Estado</th>
                                <th class="px-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventario as $item): ?>
                                <?php $stockStatus = ControladorInventario::getStockStatus($item['stock_actual']); ?>
                                <tr data-producto="<?php echo strtolower($item['producto_nombre']); ?>" 
                                    data-tipo="<?php echo $item['tipo_producto']; ?>" 
                                    data-stock="<?php echo $stockStatus['text']; ?>">
                                    <td class="px-3">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($item['id']); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($item['producto_nombre']); ?></div>
                                            <?php if (!empty($item['descripcion'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['descripcion']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($item['tipo_producto'])); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="fw-bold"><?php echo ControladorInventario::formatPrice($item['precio']); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-<?php echo $stockStatus['status']; ?>">
                                            <?php echo htmlspecialchars($item['stock_actual']); ?>
                                        </span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-<?php echo $stockStatus['status']; ?>">
                                            <?php echo $stockStatus['text']; ?>
                                        </span>
                                    </td>
                                    <td class="px-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetalleProducto(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="nuevaEntrada(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="nuevaSalida(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts para exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/autotable.min.js"></script>

<script>
// Función para exportar a Excel
function exportToExcel() {
    const table = document.getElementById('inventarioTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Inventario"});
    XLSX.writeFile(wb, "inventario_almacenista.xlsx");
}

// Función para exportar a PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    doc.autoTable({
        html: '#inventarioTable',
        startY: 20,
        headStyles: { fillColor: [66, 139, 202] },
        styles: { fontSize: 8 }
    });
    
    doc.text('Inventario - Sistema Almacenista', 14, 15);
    doc.save('inventario_almacenista.pdf');
}

// Función para imprimir
function printInventario() {
    window.print();
}

// Función para aplicar filtros
function aplicarFiltros() {
    const filtroProducto = document.getElementById('filtroProducto').value.toLowerCase();
    const filtroTipo = document.getElementById('filtroTipo').value;
    const filtroStock = document.getElementById('filtroStock').value;
    
    const filas = document.querySelectorAll('#inventarioTable tbody tr');
    
    filas.forEach(fila => {
        const producto = fila.getAttribute('data-producto');
        const tipo = fila.getAttribute('data-tipo');
        const stock = fila.getAttribute('data-stock');
        
        let mostrar = true;
        
        // Filtro por producto
        if (filtroProducto && !producto.includes(filtroProducto)) {
            mostrar = false;
        }
        
        // Filtro por tipo
        if (filtroTipo && tipo !== filtroTipo) {
            mostrar = false;
        }
        
        // Filtro por stock
        if (filtroStock) {
            if (filtroStock === 'disponible' && stock !== 'Disponible') mostrar = false;
            if (filtroStock === 'bajo' && stock !== 'Stock Bajo') mostrar = false;
            if (filtroStock === 'agotado' && stock !== 'Agotado') mostrar = false;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

// Funciones para acciones
function verDetalleProducto(productoId) {
    window.location.href = `index.php?page=productos&action=ver&id=${productoId}`;
}

function nuevaEntrada(productoId) {
    window.location.href = `index.php?page=entradas&producto_id=${productoId}`;
}

function nuevaSalida(productoId) {
    window.location.href = `index.php?page=salidas&producto_id=${productoId}`;
}

// Aplicar filtros al escribir
document.getElementById('filtroProducto').addEventListener('input', aplicarFiltros);
document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
document.getElementById('filtroStock').addEventListener('change', aplicarFiltros);
</script> 