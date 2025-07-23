<?php
// Vista para actualizar inventario - Sistema de Almacenista

require_once __DIR__ . '/../controlador/ControladorIndex.php';
$controlador = new ControladorIndex($pdo);

// Obtener inventario actual
try {
    $stmt = $pdo->prepare("
        SELECT p.*, ib.stock_actual, ib.stock_minimo, ib.stock_maximo,
               b.nombre as bodega_nombre, b.id as bodega_id,
               c.nombre as categoria_nombre,
               COALESCE(ib.ultima_actualizacion, p.fecha_creacion) as ultima_actualizacion
        FROM productos p
        JOIN inventario_bodega ib ON p.id = ib.producto_id
        JOIN bodega b ON ib.bodega_id = b.id
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.nombre ASC
    ");
    $stmt->execute();
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $inventario = [];
}

// Obtener bodegas disponibles
try {
    $stmt = $pdo->query("SELECT * FROM bodega ORDER BY nombre");
    $bodegas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bodegas = [];
}

// Obtener categorías
try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
}
?>

<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="fas fa-sync me-2"></i>Actualizar Inventario
        </h1>
    </div>
</div>

<!-- Estadísticas del inventario -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary shadow h-100 py-2">
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
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Stock Normal
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $stock_normal = array_filter($inventario, function($item) {
                                return $item['stock_actual'] > $item['stock_minimo'] && $item['stock_actual'] <= $item['stock_maximo'];
                            });
                            echo count($stock_normal);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Stock Bajo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $stock_bajo = array_filter($inventario, function($item) {
                                return $item['stock_actual'] <= $item['stock_minimo'];
                            });
                            echo count($stock_bajo);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Agotados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $agotados = array_filter($inventario, function($item) {
                                return $item['stock_actual'] == 0;
                            });
                            echo count($agotados);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filtros y Búsqueda
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Bodega</label>
                        <select class="form-select" id="filtroBodega">
                            <option value="">Todas las bodegas</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>"><?php echo htmlspecialchars($bodega['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="filtroCategoria">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado de Stock</label>
                        <select class="form-select" id="filtroEstado">
                            <option value="">Todos</option>
                            <option value="normal">Stock Normal</option>
                            <option value="bajo">Stock Bajo</option>
                            <option value="agotado">Agotado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar Producto</label>
                        <input type="text" class="form-control" id="buscarProducto" placeholder="Nombre o código...">
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
                        <button class="btn btn-success" onclick="sincronizarInventario()">
                            <i class="fas fa-sync me-1"></i>Sincronizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de inventario -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>Inventario Actual
                </h6>
                <div>
                    <button class="btn btn-warning btn-sm" onclick="exportarInventario()">
                        <i class="fas fa-download me-1"></i>Exportar
                    </button>
                    <button class="btn btn-info btn-sm" onclick="imprimirInventario()">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($inventario)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaInventario">
                            <thead class="table-primary">
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Bodega</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Stock Máximo</th>
                                    <th>Estado</th>
                                    <th>Última Actualización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventario as $item): ?>
                                    <?php
                                    $estado = 'normal';
                                    $badge_class = 'success';
                                    if ($item['stock_actual'] == 0) {
                                        $estado = 'agotado';
                                        $badge_class = 'danger';
                                    } elseif ($item['stock_actual'] <= $item['stock_minimo']) {
                                        $estado = 'bajo';
                                        $badge_class = 'warning';
                                    }
                                    ?>
                                    <tr class="inventario-row" 
                                        data-bodega="<?php echo $item['bodega_id']; ?>"
                                        data-categoria="<?php echo $item['categoria_id'] ?? ''; ?>"
                                        data-estado="<?php echo $estado; ?>"
                                        data-producto="<?php echo strtolower($item['nombre'] . ' ' . $item['codigo']); ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['imagen']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imagen']); ?>" 
                                                         alt="Producto" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                        <td><?php echo htmlspecialchars($item['bodega_nombre']); ?></td>
                                        <td>
                                            <span class="badge bg-primary fs-6"><?php echo $item['stock_actual']; ?></span>
                                        </td>
                                        <td><?php echo $item['stock_minimo']; ?></td>
                                        <td><?php echo $item['stock_maximo']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($estado); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($item['ultima_actualizacion'])); ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="editarStock(<?php echo $item['id']; ?>, <?php echo $item['bodega_id']; ?>, '<?php echo htmlspecialchars($item['nombre']); ?>', <?php echo $item['stock_actual']; ?>)">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="verHistorial(<?php echo $item['id']; ?>, <?php echo $item['bodega_id']; ?>)">
                                                <i class="fas fa-history me-1"></i>Historial
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay productos en el inventario.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar stock -->
<div class="modal fade" id="modalEditarStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Actualizar Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarStock">
                    <input type="hidden" id="productoId" name="producto_id">
                    <input type="hidden" id="bodegaId" name="bodega_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="productoNombre" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock Actual</label>
                        <input type="number" class="form-control" id="stockActual" name="stock_actual" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stockMinimo" name="stock_minimo" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Stock Máximo</label>
                        <input type="number" class="form-control" id="stockMaximo" name="stock_maximo" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Motivo del ajuste..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarStock()">
                    <i class="fas fa-save me-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function aplicarFiltros() {
    const bodega = document.getElementById('filtroBodega').value;
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    const producto = document.getElementById('buscarProducto').value.toLowerCase();
    
    const filas = document.querySelectorAll('.inventario-row');
    
    filas.forEach(fila => {
        let mostrar = true;
        
        // Filtro por bodega
        if (bodega && fila.dataset.bodega !== bodega) {
            mostrar = false;
        }
        
        // Filtro por categoría
        if (categoria && fila.dataset.categoria !== categoria) {
            mostrar = false;
        }
        
        // Filtro por estado
        if (estado && fila.dataset.estado !== estado) {
            mostrar = false;
        }
        
        // Filtro por producto
        if (producto && !fila.dataset.producto.includes(producto)) {
            mostrar = false;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

function limpiarFiltros() {
    document.getElementById('filtroBodega').value = '';
    document.getElementById('filtroCategoria').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('buscarProducto').value = '';
    
    const filas = document.querySelectorAll('.inventario-row');
    filas.forEach(fila => {
        fila.style.display = '';
    });
}

function editarStock(productoId, bodegaId, nombre, stockActual) {
    document.getElementById('productoId').value = productoId;
    document.getElementById('bodegaId').value = bodegaId;
    document.getElementById('productoNombre').value = nombre;
    document.getElementById('stockActual').value = stockActual;
    
    // Cargar datos adicionales del producto
    fetch(`controlador/ControladorIndex.php?action=obtener_producto&id=${productoId}&bodega_id=${bodegaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stockMinimo').value = data.producto.stock_minimo;
                document.getElementById('stockMaximo').value = data.producto.stock_maximo;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    
    new bootstrap.Modal(document.getElementById('modalEditarStock')).show();
}

function guardarStock() {
    const formData = new FormData(document.getElementById('formActualizarStock'));
    
    fetch('controlador/ControladorIndex.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Stock actualizado exitosamente');
            location.reload();
        } else {
            alert('Error al actualizar el stock: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el stock');
    });
}

function verHistorial(productoId, bodegaId) {
    window.open(`controlador/ControladorIndex.php?action=historial_producto&producto_id=${productoId}&bodega_id=${bodegaId}`, '_blank');
}

function sincronizarInventario() {
    if (confirm('¿Desea sincronizar todo el inventario con el sistema en línea?')) {
        fetch('controlador/ControladorIndex.php?action=sincronizar_inventario', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Inventario sincronizado exitosamente');
                location.reload();
            } else {
                alert('Error al sincronizar: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al sincronizar el inventario');
        });
    }
}

function exportarInventario() {
    const bodega = document.getElementById('filtroBodega').value;
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    
    const url = `controlador/ControladorIndex.php?action=exportar_inventario&bodega=${bodega}&categoria=${categoria}&estado=${estado}`;
    window.location.href = url;
}

function imprimirInventario() {
    window.print();
}

// Inicializar DataTable
$(document).ready(function() {
    $('#tablaInventario').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script> 