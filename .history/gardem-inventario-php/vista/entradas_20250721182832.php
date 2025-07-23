<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorEntradas.php';

// Crear instancia del controlador
$controladorEntradas = new ControladorEntradas($pdo);

// Obtener datos
$entradas = $controladorEntradas->obtenerEntradas();
$proveedores = $controladorEntradas->obtenerProveedores();
$productos = $controladorEntradas->obtenerProductos();
$bodegas = $controladorEntradas->obtenerBodegas();
$colores = $controladorEntradas->obtenerColores();
$tallas = $controladorEntradas->obtenerTallas();

// Calcular estadísticas
$total_entradas = count($entradas);
$entradas_confirmadas = count(array_filter($entradas, function($e) { return $e['estado'] == 'confirmada'; }));
$entradas_pendientes = count(array_filter($entradas, function($e) { return $e['estado'] == 'pendiente'; }));
$entradas_hoy = count(array_filter($entradas, function($e) { return date('Y-m-d', strtotime($e['fecha'])) == date('Y-m-d'); }));

// Verificar mensajes
$mensaje_exito = isset($_GET['success']) && $_GET['success'] == 1;
$mensaje_error = isset($_GET['error']) ? $_GET['error'] : '';

// Obtener productos para el formulario
$stmt = $pdo->query("SELECT id, nombre FROM productos ORDER BY nombre");
$productos = $stmt->fetchAll();

// Obtener bodegas
$stmt = $pdo->query("SELECT id, nombre FROM bodega ORDER BY nombre");
$bodegas = $stmt->fetchAll();

// Obtener proveedores (usando datos hardcodeados por ahora)
$proveedores = [
    ['id' => 1, 'nombre' => 'diseños stely'],
    ['id' => 2, 'nombre' => 'Proveedor 2'],
    ['id' => 3, 'nombre' => 'Proveedor 3']
];

// Obtener clientes (usando datos hardcodeados por ahora)
$clientes = [
    ['id' => 1, 'nombre' => 'Cliente 1'],
    ['id' => 2, 'nombre' => 'Cliente 2'],
    ['id' => 3, 'nombre' => 'Cliente 3']
];

// Obtener historial de entradas
$stmt = $pdo->query("
    SELECT 
        pe.*,
        p.nombre as producto_nombre,
        b.nombre as bodega_nombre
    FROM productos_entradas pe
    LEFT JOIN productos p ON pe.producto_id = p.id
    LEFT JOIN bodega b ON pe.bodega_id = b.id
    ORDER BY pe.fecha DESC
    LIMIT 50
");
$entradas = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-plus-circle text-success me-2"></i>Entradas de Inventario</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-secondary" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel
            </button>
            <button class="btn btn-secondary" onclick="printEntradas()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <!-- Formulario de Nueva Entrada -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Nueva Entrada</h5>
        </div>
        <div class="card-body">
            <form id="formNuevaEntrada">
                <div class="row">
                    <div class="col-md-3">
                        <label for="producto_id" class="form-label">Producto *</label>
                        <select class="form-select" id="producto_id" name="producto_id" required>
                            <option value="">Seleccionar producto...</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bodega_id" class="form-label">Bodega</label>
                        <select class="form-select" id="bodega_id" name="bodega_id">
                            <option value="">Seleccionar bodega...</option>
                            <?php foreach ($bodegas as $bodega): ?>
                                <option value="<?php echo $bodega['id']; ?>">
                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="cantidad" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label for="motivo" class="form-label">Motivo</label>
                        <select class="form-select" id="motivo" name="motivo">
                            <option value="compra">Compra</option>
                            <option value="devolucion">Devolución</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="traslado">Traslado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="factura_remision" class="form-label">Factura/Remisión</label>
                        <input type="text" class="form-control" id="factura_remision" name="factura_remision">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="beneficiario_tipo" class="form-label">Tipo de Beneficiario</label>
                        <select class="form-select" id="beneficiario_tipo" name="beneficiario_tipo">
                            <option value="">Seleccionar...</option>
                            <option value="proveedor">Proveedor</option>
                            <option value="cliente">Cliente</option>
                            <option value="interno">Interno</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="beneficiario_id" class="form-label">Beneficiario</label>
                        <select class="form-select" id="beneficiario_id" name="beneficiario_id">
                            <option value="">Seleccionar tipo primero...</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-2"></i>Registrar Entrada
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Entradas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history text-primary me-2"></i>Historial de Entradas
            </h5>
            <span class="badge bg-secondary"><?php echo count($entradas); ?> registros</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">ID</th>
                            <th class="px-3">Producto</th>
                            <th class="px-3">Bodega</th>
                            <th class="px-3">Cantidad</th>
                            <th class="px-3">Motivo</th>
                            <th class="px-3">Beneficiario</th>
                            <th class="px-3">Factura</th>
                            <th class="px-3">Fecha</th>
                            <th class="px-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay entradas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entradas as $entrada): ?>
                                <tr>
                                    <td class="px-3">
                                        <span class="badge bg-secondary"><?php echo $entrada['id']; ?></span>
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold"><?php echo htmlspecialchars($entrada['producto_nombre'] ?? 'N/A'); ?></div>
                                        <small class="text-muted">ID: <?php echo $entrada['producto_id']; ?></small>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-info"><?php echo htmlspecialchars($entrada['bodega_nombre'] ?? 'Sin bodega'); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-success"><?php echo $entrada['cantidad']; ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-primary"><?php echo ucfirst($entrada['motivo'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($entrada['beneficiario'] ?? 'N/A'); ?></div>
                                            <small class="text-muted"><?php echo ucfirst($entrada['beneficiario_tipo'] ?? 'N/A'); ?></small>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <code><?php echo htmlspecialchars($entrada['factura_remision'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td class="px-3">
                                        <small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small>
                                    </td>
                                    <td class="px-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetalleEntrada(<?php echo $entrada['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editarEntrada(<?php echo $entrada['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
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

<script>
// Función para exportar a Excel
function exportToExcel() {
    alert('Exportar a Excel - Funcionalidad en desarrollo');
}

// Función para imprimir
function printEntradas() {
    window.print();
}

// Función para ver detalle de entrada
function verDetalleEntrada(entradaId) {
    alert('Ver detalle de entrada ' + entradaId + ' - Funcionalidad en desarrollo');
}

// Función para editar entrada
function editarEntrada(entradaId) {
    alert('Editar entrada ' + entradaId + ' - Funcionalidad en desarrollo');
}

// Manejar envío del formulario
document.getElementById('formNuevaEntrada').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Aquí iría la lógica para enviar al servidor
    alert('Registrando entrada... - Funcionalidad en desarrollo');
    
    // Limpiar formulario
    this.reset();
});

// Datos de beneficiarios
const proveedores = <?php echo json_encode($proveedores); ?>;
const clientes = <?php echo json_encode($clientes); ?>;

// Actualizar beneficiarios según el tipo seleccionado
document.getElementById('beneficiario_tipo').addEventListener('change', function() {
    const tipo = this.value;
    const beneficiarioSelect = document.getElementById('beneficiario_id');
    
    // Limpiar opciones actuales
    beneficiarioSelect.innerHTML = '<option value="">Seleccionar...</option>';
    
    if (tipo === 'proveedor') {
        proveedores.forEach(proveedor => {
            const option = document.createElement('option');
            option.value = proveedor.id;
            option.textContent = proveedor.nombre;
            beneficiarioSelect.appendChild(option);
        });
    } else if (tipo === 'cliente') {
        clientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nombre;
            beneficiarioSelect.appendChild(option);
        });
    } else if (tipo === 'interno') {
        const option = document.createElement('option');
        option.value = 'interno';
        option.textContent = 'Uso Interno';
        beneficiarioSelect.appendChild(option);
    }
});
</script> 