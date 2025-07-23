<?php
session_start();
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$entrada_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$entrada_id) {
    header('Location: index.php?page=entradas&error=ID de entrada no válido');
    exit();
}

// Obtener datos de la entrada
try {
    $stmt = $pdo->prepare("
        SELECT * FROM productos_entradas 
        WHERE id = ?
    ");
    $stmt->execute([$entrada_id]);
    $entrada = $stmt->fetch();
    
    if (!$entrada) {
        header('Location: index.php?page=entradas&error=Entrada no encontrada');
        exit();
    }
} catch (Exception $e) {
    header('Location: index.php?page=entradas&error=' . urlencode($e->getMessage()));
    exit();
}

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $producto_id = $_POST['producto_id'] ?? null;
        $categoria_id = $_POST['categoria_id'] ?? null;
        $talla_id = $_POST['talla_id'] ?? null;
        $color_id = $_POST['color_id'] ?? null;
        $bodega_id = $_POST['bodega_id'] ?? null;
        $cantidad = $_POST['cantidad'] ?? null;
        $motivo = $_POST['motivo'] ?? null;
        $factura_remision = $_POST['factura_remision'] ?? null;
        $beneficiario_tipo = $_POST['beneficiario_tipo'] ?? null;
        $beneficiario_id = $_POST['beneficiario_id'] ?? null;

        // Validaciones
        if (!$producto_id || !$cantidad) {
            throw new Exception('Producto y cantidad son obligatorios');
        }

        if ($cantidad <= 0) {
            throw new Exception('La cantidad debe ser mayor a 0');
        }

        // Calcular diferencia de cantidad
        $cantidad_anterior = $entrada['cantidad'];
        $diferencia = $cantidad - $cantidad_anterior;

        // Obtener nombre del beneficiario
        $beneficiario_nombre = '';
        if ($beneficiario_tipo && $beneficiario_id) {
            if ($beneficiario_tipo === 'proveedor') {
                $proveedores = [
                    1 => 'diseños stely',
                    2 => 'Textiles ABC',
                    3 => 'Ropa Express',
                    4 => 'Moda Latina'
                ];
                $beneficiario_nombre = $proveedores[$beneficiario_id] ?? 'Proveedor ' . $beneficiario_id;
            } elseif ($beneficiario_tipo === 'cliente') {
                $clientes = [
                    1 => 'Cliente Mayorista A',
                    2 => 'Tienda Fashion',
                    3 => 'Boutique Elegante',
                    4 => 'Distribuidor XYZ'
                ];
                $beneficiario_nombre = $clientes[$beneficiario_id] ?? 'Cliente ' . $beneficiario_id;
            } elseif ($beneficiario_tipo === 'interno') {
                $beneficiario_nombre = 'Uso Interno';
            }
        }

        // Actualizar entrada
        $stmt = $pdo->prepare("
            UPDATE productos_entradas 
            SET producto_id = ?, bodega_id = ?, cantidad = ?, motivo = ?, 
                beneficiario_tipo = ?, beneficiario_id = ?, factura_remision = ?, beneficiario = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $producto_id,
            $bodega_id ?: null,
            $cantidad,
            $motivo,
            $beneficiario_tipo,
            $beneficiario_id,
            $factura_remision,
            $beneficiario_nombre,
            $entrada_id
        ]);

        // Actualizar stock en inventario_bodega
        if ($diferencia != 0) {
            try {
                if ($bodega_id && $bodega_id > 0) {
                    $stmt = $pdo->prepare("
                        INSERT INTO inventario_bodega (producto_id, bodega_id, stock_actual) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE stock_actual = stock_actual + ?
                    ");
                    $stmt->execute([$producto_id, $bodega_id, $diferencia, $diferencia]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO inventario_bodega (producto_id, stock_actual) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE stock_actual = stock_actual + ?
                    ");
                    $stmt->execute([$producto_id, $diferencia, $diferencia]);
                }
            } catch (Exception $e) {
                // Si falla, intentar sin bodega_id
                $stmt = $pdo->prepare("
                    INSERT INTO inventario_bodega (producto_id, stock_actual) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE stock_actual = stock_actual + ?
                ");
                $stmt->execute([$producto_id, $diferencia, $diferencia]);
            }
        }

        // Actualizar inventario_tallas_colores_categorias si hay cambios
        if ($talla_id && $color_id && $categoria_id && $diferencia != 0) {
            $stmt = $pdo->prepare("
                INSERT INTO inventario_tallas_colores_categorias 
                (producto_id, talla_id, color_id, `stock existente`, categoria_id, fecha_ingreso) 
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE `stock existente` = `stock existente` + ?
            ");
            $stmt->execute([$producto_id, $talla_id, $color_id, $diferencia, $categoria_id, $diferencia]);
        }

        $success = 'Entrada actualizada correctamente';
        
        // Recargar datos de la entrada
        $stmt = $pdo->prepare("SELECT * FROM productos_entradas WHERE id = ?");
        $stmt->execute([$entrada_id]);
        $entrada = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

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

// Datos hardcodeados
$proveedores = [
    1 => 'diseños stely',
    2 => 'Textiles ABC',
    3 => 'Ropa Express',
    4 => 'Moda Latina'
];

$clientes = [
    1 => 'Cliente Mayorista A',
    2 => 'Tienda Fashion',
    3 => 'Boutique Elegante',
    4 => 'Distribuidor XYZ'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Entrada - Sistema de Almacenista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-edit text-primary"></i> Editar Entrada #<?php echo $entrada_id; ?></h2>
                    <a href="index.php?page=entradas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Entradas
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>¡Éxito!</strong> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit"></i> Editar Entrada - Proceso Simple</h5>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" id="progressBar" style="width: 33%"></div>
                        </div>
                        <small class="text-muted">Paso <span id="currentStep">1</span> de 3</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="formEditarEntrada">
                            <!-- Paso 1: Categoría y Producto -->
                            <div class="step active" id="step1">
                                <h6 class="mb-3">Paso 1: Categoría y Producto</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="categoria_id" class="form-label">Categoría</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Seleccionar categoría...</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo $categoria['id']; ?>" 
                                                        <?php echo ($entrada['producto_id'] && $productos && array_filter($productos, function($p) use ($categoria) { return $p['categoria_id'] == $categoria['id']; })) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="producto_id" class="form-label">Producto *</label>
                                        <select class="form-select" id="producto_id" name="producto_id" required>
                                            <option value="">Seleccionar categoría primero...</option>
                                            <?php foreach ($productos as $producto): ?>
                                                <option value="<?php echo $producto['id']; ?>" 
                                                        <?php echo ($entrada['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Selecciona una categoría para ver los productos disponibles</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary" onclick="nextStep()">
                                        Siguiente <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Paso 2: Talla, Color, Cantidad y Bodega -->
                            <div class="step" id="step2">
                                <h6 class="mb-3">Paso 2: Talla, Color, Cantidad y Bodega</h6>
                                
                                <div class="row">
                                    <div class="col-md-3 mb-3">
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
                                    
                                    <div class="col-md-3 mb-3">
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
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="cantidad" class="form-label">Cantidad *</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                               value="<?php echo htmlspecialchars($entrada['cantidad']); ?>" min="1" required>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="bodega_id" class="form-label">Bodega</label>
                                        <select class="form-select" id="bodega_id" name="bodega_id">
                                            <option value="">Sin bodega</option>
                                            <?php foreach ($bodegas as $bodega): ?>
                                                <option value="<?php echo $bodega['id']; ?>" 
                                                        <?php echo ($entrada['bodega_id'] == $bodega['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($bodega['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                        <i class="fas fa-arrow-left"></i> Anterior
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep()">
                                        Siguiente <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Paso 3: Información Final -->
                            <div class="step" id="step3">
                                <h6 class="mb-3">Paso 3: Información Final</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="motivo" class="form-label">Motivo</label>
                                        <select class="form-select" id="motivo" name="motivo">
                                            <option value="">Seleccionar motivo...</option>
                                            <option value="compra" <?php echo ($entrada['motivo'] == 'compra') ? 'selected' : ''; ?>>Compra</option>
                                            <option value="devolucion" <?php echo ($entrada['motivo'] == 'devolucion') ? 'selected' : ''; ?>>Devolución</option>
                                            <option value="ajuste" <?php echo ($entrada['motivo'] == 'ajuste') ? 'selected' : ''; ?>>Ajuste</option>
                                            <option value="traslado" <?php echo ($entrada['motivo'] == 'traslado') ? 'selected' : ''; ?>>Traslado</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="factura_remision" class="form-label">Factura/Remisión</label>
                                        <input type="text" class="form-control" id="factura_remision" name="factura_remision" 
                                               value="<?php echo htmlspecialchars($entrada['factura_remision'] ?? ''); ?>" 
                                               placeholder="Número de factura o remisión">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="beneficiario_tipo" class="form-label">Tipo de Beneficiario</label>
                                        <select class="form-select" id="beneficiario_tipo" name="beneficiario_tipo">
                                            <option value="">Seleccionar tipo...</option>
                                            <option value="proveedor" <?php echo ($entrada['beneficiario_tipo'] == 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
                                            <option value="cliente" <?php echo ($entrada['beneficiario_tipo'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                            <option value="interno" <?php echo ($entrada['beneficiario_tipo'] == 'interno') ? 'selected' : ''; ?>>Interno</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="beneficiario_id" class="form-label">Beneficiario</label>
                                        <select class="form-select" id="beneficiario_id" name="beneficiario_id">
                                            <option value="">Seleccionar tipo primero...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                        <i class="fas fa-arrow-left"></i> Anterior
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 3;

        // Datos hardcodeados
        const proveedores = <?php echo json_encode($proveedores); ?>;
        const clientes = <?php echo json_encode($clientes); ?>;

        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('currentStep').textContent = currentStep;
        }

        function showStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            currentStep = step;
            updateProgress();
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                showStep(currentStep + 1);
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }

        // Filtrar productos por categoría
        document.getElementById('categoria_id').addEventListener('change', function() {
            const categoriaId = this.value;
            const productoSelect = document.getElementById('producto_id');
            const productos = <?php echo json_encode($productos); ?>;
            
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

        // Cargar beneficiario actual si existe
        window.addEventListener('load', function() {
            const beneficiarioTipo = '<?php echo $entrada['beneficiario_tipo']; ?>';
            const beneficiarioId = '<?php echo $entrada['beneficiario_id']; ?>';
            
            if (beneficiarioTipo) {
                document.getElementById('beneficiario_tipo').value = beneficiarioTipo;
                document.getElementById('beneficiario_tipo').dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    if (beneficiarioId) {
                        document.getElementById('beneficiario_id').value = beneficiarioId;
                    }
                }, 100);
            }
        });
    </script>
</body>
</html> 