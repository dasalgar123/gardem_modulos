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

// Obtener tallas
$stmt = $pdo->query("SELECT id, nombre FROM tallas ORDER BY nombre");
$tallas = $stmt->fetchAll();

// Obtener colores
$stmt = $pdo->query("SELECT id, nombre FROM colores ORDER BY nombre");
$colores = $stmt->fetchAll();

// Obtener categorías
$stmt = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();

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

    <!-- Formulario de Nueva Entrada - Proceso Simple -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Nueva Entrada - Proceso Simple</h5>
        </div>
        <div class="card-body">
            <!-- Barra de Progreso -->
            <div class="progress mb-4" style="height: 20px;">
                <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 33%;" 
                     aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    Paso 1 de 3
                </div>
            </div>

            <form id="formNuevaEntrada">
                <!-- Paso 1: Categoría y Producto -->
                <div class="step" id="step1">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-tags text-primary me-2"></i>Paso 1: Categoría y Producto</h4>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-5">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id">
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
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
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="nextStep(1)">
                            Siguiente <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Talla, Color y Cantidad -->
                <div class="step" id="step2" style="display: none;">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-palette text-primary me-2"></i>Paso 2: Talla, Color y Cantidad</h4>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-3">
                            <label for="talla_id" class="form-label">Talla</label>
                            <select class="form-select" id="talla_id" name="talla_id">
                                <option value="">Seleccionar talla...</option>
                                <?php foreach ($tallas as $talla): ?>
                                    <option value="<?php echo $talla['id']; ?>">
                                        <?php echo htmlspecialchars($talla['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="color_id" class="form-label">Color</label>
                            <select class="form-select" id="color_id" name="color_id">
                                <option value="">Seleccionar color...</option>
                                <?php foreach ($colores as $color): ?>
                                    <option value="<?php echo $color['id']; ?>">
                                        <?php echo htmlspecialchars($color['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="cantidad" class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
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
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" onclick="prevStep(2)">
                            <i class="fas fa-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                            Siguiente <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 3: Información Final -->
                <div class="step" id="step3" style="display: none;">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-info-circle text-primary me-2"></i>Paso 3: Información Final</h4>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <label for="motivo" class="form-label">Motivo</label>
                            <select class="form-select" id="motivo" name="motivo">
                                <option value="compra">Compra</option>
                                <option value="devolucion">Devolución</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="traslado">Traslado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="factura_remision" class="form-label">Factura/Remisión</label>
                            <input type="text" class="form-control" id="factura_remision" name="factura_remision">
                        </div>
                        <div class="col-md-4">
                            <label for="beneficiario_tipo" class="form-label">Tipo de Beneficiario</label>
                            <select class="form-select" id="beneficiario_tipo" name="beneficiario_tipo">
                                <option value="">Seleccionar...</option>
                                <option value="proveedor">Proveedor</option>
                                <option value="cliente">Cliente</option>
                                <option value="interno">Interno</option>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-center mt-3">
                        <div class="col-md-6">
                            <label for="beneficiario_id" class="form-label">Beneficiario</label>
                            <select class="form-select" id="beneficiario_id" name="beneficiario_id">
                                <option value="">Seleccionar tipo primero...</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" onclick="prevStep(3)">
                            <i class="fas fa-arrow-left me-2"></i>Anterior
                        </button>
                        <button type="submit" class="btn btn-success">
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

// Variables para el proceso por pasos
let currentStep = 1;
const totalSteps = 3;

// Función para ir al siguiente paso
function nextStep(step) {
    if (step < totalSteps) {
        document.getElementById(`step${step}`).style.display = 'none';
        document.getElementById(`step${step + 1}`).style.display = 'block';
        currentStep = step + 1;
        updateProgressBar();
    }
}

// Función para ir al paso anterior
function prevStep(step) {
    if (step > 1) {
        document.getElementById(`step${step}`).style.display = 'none';
        document.getElementById(`step${step - 1}`).style.display = 'block';
        currentStep = step - 1;
        updateProgressBar();
    }
}

// Función para actualizar la barra de progreso
function updateProgressBar() {
    const progress = (currentStep / totalSteps) * 100;
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = progress + '%';
    progressBar.textContent = `Paso ${currentStep} de ${totalSteps}`;
    progressBar.setAttribute('aria-valuenow', progress);
}

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