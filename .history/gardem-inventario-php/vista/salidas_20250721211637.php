<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once __DIR__ . '/../controlador/ControladorSalidas.php';

// Crear instancia del controlador
$controladorSalidas = new ControladorSalidas($pdo);

// Obtener datos para los dropdowns
try {
    // Categorías
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
    
    // Productos
    $stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.nombre");
    $productos = $stmt->fetchAll();
    
    // Tallas
    $stmt = $pdo->query("SELECT * FROM tallas ORDER BY nombre");
    $tallas = $stmt->fetchAll();
    
    // Colores
    $stmt = $pdo->query("SELECT * FROM colores ORDER BY nombre");
    $colores = $stmt->fetchAll();
    
    // Bodegas
    $stmt = $pdo->query("SELECT * FROM bodega ORDER BY nombre");
    $bodegas = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Error al cargar datos: ' . $e->getMessage();
}

// Obtener historial de salidas (adaptado a tu estructura)
try {
    // Consulta que funciona con la estructura actual
    $stmt = $pdo->query("
        SELECT 
            ps.*,
            p.nombre as producto_nombre,
            ps.talla as talla_nombre,
            ps.color as color_nombre
        FROM productos_salidas ps
        LEFT JOIN productos p ON ps.producto_id = p.id
        ORDER BY ps.fecha DESC
    ");
    $salidas = $stmt->fetchAll();
} catch (Exception $e) {
    $salidas = [];
    $error = 'Error al cargar salidas: ' . $e->getMessage();
}

// Obtener datos reales de proveedores y clientes
try {
    $stmt = $pdo->query("SELECT id, nombre FROM proveedor ORDER BY nombre");
    $proveedores_data = $stmt->fetchAll();
    $proveedores = [];
    foreach ($proveedores_data as $prov) {
        $proveedores[$prov['id']] = $prov['nombre'];
    }
    
    $stmt = $pdo->query("SELECT id, nombre FROM cliente ORDER BY nombre");
    $clientes_data = $stmt->fetchAll();
    $clientes = [];
    foreach ($clientes_data as $cli) {
        $clientes[$cli['id']] = $cli['nombre'];
    }
} catch (Exception $e) {
    // Si falla, usar datos por defecto
    $proveedores = [1 => 'diseños stely'];
    $clientes = [1 => 'nelson'];
}
?>

<div class="container-fluid">
    <!-- Header de la página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-sign-out-alt text-danger me-2"></i>
                Salidas de Inventario
            </h2>
            <p class="text-muted mb-0">Gestiona las salidas de productos del inventario</p>
        </div>
        <div>
            <button class="btn btn-success me-2" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel
            </button>
            <button class="btn btn-info" onclick="printSalidas()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
        </div>
    </div>

    <!-- Mensajes de éxito y error -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> La salida ha sido registrada correctamente.
            <?php if (isset($_GET['salida_id'])): ?>
                <br><small>ID de salida: <?php echo htmlspecialchars($_GET['salida_id']); ?></small>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Nueva Salida - Proceso Simple -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus text-success me-2"></i>Nueva Salida - Proceso Simple
            </h5>
            <div class="progress mt-2">
                <div class="progress-bar bg-success" id="progressBar" style="width: 33%"></div>
            </div>
            <small class="text-muted">Paso <span id="currentStep">1</span> de 3</small>
        </div>
        <div class="card-body">
            <form method="POST" action="procesar_salida.php" id="formNuevaSalida">
                <!-- Paso 1: Categoría y Producto -->
                <div class="step active" id="step1">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-box text-primary me-2"></i>Paso 1: Categoría y Producto</h4>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="producto_id" class="form-label">Producto *</label>
                            <select class="form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar categoría primero...</option>
                            </select>
                            <small class="text-muted">Selecciona una categoría para ver los productos disponibles</small>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="nextStep(1)">
                            Siguiente <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Talla, Color, Cantidad y Bodega -->
                <div class="step" id="step2" style="display: none;">
                    <div class="text-center mb-4">
                        <h4><i class="fas fa-ruler text-primary me-2"></i>Paso 2: Talla, Color, Cantidad y Bodega</h4>
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
                                <option value="">Sin bodega</option>
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
                                <option value="venta">Venta</option>
                                <option value="devolucion">Devolución</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="traslado">Traslado</option>
                                <option value="perdida">Pérdida</option>
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
                            <i class="fas fa-save me-2"></i>Registrar Salida
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Salidas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history text-primary me-2"></i>Historial de Salidas
            </h5>
            <span class="badge bg-secondary"><?php echo count($salidas); ?> registros</span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">ID</th>
                            <th class="px-3">Producto</th>
                            <th class="px-3">Talla</th>
                            <th class="px-3">Color</th>
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
                        <?php if (empty($salidas)): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay salidas registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salidas as $salida): ?>
                                <tr>
                                    <td class="px-3">
                                        <span class="badge bg-secondary"><?php echo $salida['id']; ?></span>
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold"><?php echo htmlspecialchars($salida['producto_nombre'] ?? 'N/A'); ?></div>
                                        <small class="text-muted">ID: <?php echo $salida['producto_id']; ?></small>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($salida['talla_nombre'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-warning"><?php echo htmlspecialchars($salida['color_nombre'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-info">Sin bodega</span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-danger"><?php echo $salida['cantidad']; ?></span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-primary"><?php echo ucfirst($salida['motivo'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="px-3">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($salida['beneficiario'] ?? 'N/A'); ?></div>
                                            <small class="text-muted"><?php echo ucfirst($salida['destinatario_tipo'] ?? 'N/A'); ?></small>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <code><?php echo htmlspecialchars($salida['factura_remision'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td class="px-3">
                                        <small><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></small>
                                    </td>
                                    <td class="px-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetalleSalida(<?php echo $salida['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="editarSalida(<?php echo $salida['id']; ?>)">
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
let currentStep = 1;
const totalSteps = 3;

// Función para exportar a Excel
function exportToExcel() {
    alert('Exportar a Excel - Funcionalidad en desarrollo');
}

// Función para imprimir
function printSalidas() {
    window.print();
}

// Función para ver detalle de salida
function verDetalleSalida(salidaId) {
    alert('Ver detalle de salida ' + salidaId + ' - Funcionalidad en desarrollo');
}

// Función para editar salida
function editarSalida(salidaId) {
    window.location.href = 'editores/editar_salida.php?id=' + salidaId;
}

// Navegación entre pasos
function nextStep(step) {
    if (step < totalSteps) {
        document.getElementById('step' + step).style.display = 'none';
        document.getElementById('step' + (step + 1)).style.display = 'block';
        currentStep = step + 1;
        updateProgress();
    }
}

function prevStep(step) {
    if (step > 1) {
        document.getElementById('step' + step).style.display = 'none';
        document.getElementById('step' + (step - 1)).style.display = 'block';
        currentStep = step - 1;
        updateProgress();
    }
}

function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('currentStep').textContent = currentStep;
}

// Manejar envío del formulario
document.getElementById('formNuevaSalida').addEventListener('submit', function(e) {
    // Validar que todos los campos obligatorios estén completos
    const producto_id = document.getElementById('producto_id').value;
    const cantidad = document.getElementById('cantidad').value;
    
    if (!producto_id) {
        e.preventDefault();
        alert('Debes seleccionar un producto');
        return false;
    }
    
    if (!cantidad || cantidad <= 0) {
        e.preventDefault();
        alert('Debes ingresar una cantidad válida');
        return false;
    }
    
    // Si todo está bien, permitir el envío
    return true;
});

// Datos de beneficiarios
const proveedores = <?php echo json_encode($proveedores); ?>;
const clientes = <?php echo json_encode($clientes); ?>;

// Datos de productos con categorías
const productos = <?php echo json_encode($productos); ?>;

// Filtrar productos por categoría
document.getElementById('categoria_id').addEventListener('change', function() {
    const categoriaId = this.value;
    const productoSelect = document.getElementById('producto_id');
    
    productoSelect.innerHTML = '<option value="">Seleccionar producto...</option>';
    
    if (categoriaId) {
        productos.forEach(producto => {
            if (producto.categoria_id == categoriaId) {
                const option = document.createElement('option');
                option.value = producto.id;
                option.textContent = producto.nombre;
                productoSelect.appendChild(option);
            }
        });
    }
});

// Filtrar beneficiarios por tipo
document.getElementById('beneficiario_tipo').addEventListener('change', function() {
    const tipo = this.value;
    const beneficiarioSelect = document.getElementById('beneficiario_id');
    
    beneficiarioSelect.innerHTML = '<option value="">Seleccionar beneficiario...</option>';
    
    if (tipo === 'proveedor') {
        Object.keys(proveedores).forEach(id => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = proveedores[id];
            beneficiarioSelect.appendChild(option);
        });
    } else if (tipo === 'cliente') {
        Object.keys(clientes).forEach(id => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = clientes[id];
            beneficiarioSelect.appendChild(option);
        });
    } else if (tipo === 'interno') {
        const option = document.createElement('option');
        option.value = '1';
        option.textContent = 'Uso Interno';
        beneficiarioSelect.appendChild(option);
    }
});
</script> 