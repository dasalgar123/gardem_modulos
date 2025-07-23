<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$salida_id = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$salida_id) {
    header('Location: index.php?page=salidas&error=ID de salida no válido');
    exit();
}

// Obtener datos de la salida
try {
    $stmt = $pdo->prepare("
        SELECT * FROM productos_salidas 
        WHERE id = ?
    ");
    $stmt->execute([$salida_id]);
    $salida = $stmt->fetch();
    
    if (!$salida) {
        header('Location: index.php?page=salidas&error=Salida no encontrada');
        exit();
    }
} catch (Exception $e) {
    header('Location: index.php?page=salidas&error=' . urlencode($e->getMessage()));
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
        $cantidad_anterior = $salida['cantidad'];
        $diferencia = $cantidad - $cantidad_anterior;

        // Verificar stock disponible si se aumenta la cantidad
        if ($diferencia > 0) {
            $stmt = $pdo->prepare("
                SELECT COALESCE(ib.stock_actual, 0) as stock_actual,
                       COALESCE(entradas.total_entradas, 0) as total_entradas,
                       COALESCE(salidas.total_salidas, 0) as total_salidas
                FROM productos p
                LEFT JOIN inventario_bodega ib ON p.id = ib.producto_id
                LEFT JOIN (
                    SELECT producto_id, SUM(cantidad) as total_entradas 
                    FROM productos_entradas 
                    GROUP BY producto_id
                ) entradas ON p.id = entradas.producto_id
                LEFT JOIN (
                    SELECT producto_id, SUM(cantidad) as total_salidas 
                    FROM productos_salidas 
                    WHERE id != ?
                    GROUP BY producto_id
                ) salidas ON p.id = salidas.producto_id
                WHERE p.id = ?
            ");
            $stmt->execute([$salida_id, $producto_id]);
            $stock_info = $stmt->fetch();
            
            $stock_disponible = ($stock_info['total_entradas'] ?? 0) - ($stock_info['total_salidas'] ?? 0);
            
            if ($diferencia > $stock_disponible) {
                throw new Exception("Stock insuficiente. Disponible: $stock_disponible, Solicitado: $diferencia");
            }
        }

        // Obtener nombre del beneficiario
        $beneficiario_nombre = '';
        if ($beneficiario_tipo && $beneficiario_id) {
            if ($beneficiario_tipo === 'proveedor') {
                $stmt = $pdo->prepare("SELECT nombre FROM proveedor WHERE id = ?");
                $stmt->execute([$beneficiario_id]);
                $result = $stmt->fetch();
                $beneficiario_nombre = $result['nombre'] ?? 'Proveedor ' . $beneficiario_id;
            } elseif ($beneficiario_tipo === 'cliente') {
                $stmt = $pdo->prepare("SELECT nombre FROM cliente WHERE id = ?");
                $stmt->execute([$beneficiario_id]);
                $result = $stmt->fetch();
                $beneficiario_nombre = $result['nombre'] ?? 'Cliente ' . $beneficiario_id;
            } elseif ($beneficiario_tipo === 'interno') {
                $beneficiario_nombre = 'Uso Interno';
            }
        }

        // Guardar IDs de talla y color directamente (no nombres)
        $talla_id_final = $talla_id ?: null;
        $color_id_final = $color_id ?: null;

        // Actualizar salida
        $cliente_id = null;
        if ($beneficiario_tipo === 'cliente' && $beneficiario_id) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM cliente WHERE id = ?");
                $stmt->execute([$beneficiario_id]);
                if ($stmt->fetch()) {
                    $cliente_id = $beneficiario_id;
                }
            } catch (Exception $e) {
                // Si falla la consulta del cliente, continuar sin cliente_id
                $cliente_id = null;
            }
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE productos_salidas 
                SET producto_id = ?, cantidad = ?, motivo = ?, 
                    destinatario_tipo = ?, destinatario_id = ?, factura_remision = ?, 
                    talla = ?, color = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $producto_id,
                $cantidad,
                $motivo,
                $beneficiario_tipo,
                $beneficiario_id,
                $factura_remision,
                $talla_id_final,
                $color_id_final,
                $salida_id
            ]);
        } catch (Exception $e) {
            // Si falla por foreign key, intentar sin cliente_id
            $stmt = $pdo->prepare("
                UPDATE productos_salidas 
                SET producto_id = ?, cantidad = ?, motivo = ?, 
                    destinatario_tipo = ?, destinatario_id = ?, factura_remision = ?, 
                    talla = ?, color = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $producto_id,
                $cantidad,
                $motivo,
                $beneficiario_tipo,
                $beneficiario_id,
                $factura_remision,
                $talla_id_final,
                $color_id_final,
                $salida_id
            ]);
        }

        // Actualizar stock en inventario_bodega si cambió la cantidad
        if ($diferencia != 0) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE inventario_bodega 
                    SET stock_actual = stock_actual - ? 
                    WHERE producto_id = ?
                ");
                $stmt->execute([$diferencia, $producto_id]);
            } catch (Exception $e) {
                // Si falla, intentar insertar con stock negativo
                $stmt = $pdo->prepare("
                    INSERT INTO inventario_bodega (producto_id, stock_actual) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE stock_actual = stock_actual - ?
                ");
                $stmt->execute([$producto_id, -$diferencia, $diferencia]);
            }
        }

        $success = 'Salida actualizada correctamente';
        
        // Recargar datos de la salida
        $stmt = $pdo->prepare("SELECT * FROM productos_salidas WHERE id = ?");
        $stmt->execute([$salida_id]);
        $salida = $stmt->fetch();
        
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Salida - Sistema de Almacenista</title>
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
                    <h2><i class="fas fa-edit text-warning"></i> Editar Salida #<?php echo $salida_id; ?></h2>
                    <a href="index.php?page=salidas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Salidas
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
                        <h5><i class="fas fa-edit"></i> Editar Salida - Proceso Simple</h5>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" id="progressBar" style="width: 33%"></div>
                        </div>
                        <small class="text-muted">Paso <span id="currentStep">1</span> de 3</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="formEditarSalida">
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
                                                        <?php echo ($salida['producto_id'] && $productos && array_filter($productos, function($p) use ($categoria) { return $p['categoria_id'] == $categoria['id']; })) ? 'selected' : ''; ?>>
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
                                                        <?php echo ($salida['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Selecciona una categoría para ver los productos disponibles</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-warning" onclick="nextStep()">
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
                                                <option value="<?php echo $talla['id']; ?>" 
                                                        <?php echo ($salida['talla'] == $talla['id']) ? 'selected' : ''; ?>>
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
                                                <option value="<?php echo $color['id']; ?>" 
                                                        <?php echo ($salida['color'] == $color['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($color['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="cantidad" class="form-label">Cantidad *</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                               value="<?php echo htmlspecialchars($salida['cantidad']); ?>" min="1" required>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
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
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                        <i class="fas fa-arrow-left"></i> Anterior
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="nextStep()">
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
                                            <option value="venta" <?php echo ($salida['motivo'] == 'venta') ? 'selected' : ''; ?>>Venta</option>
                                            <option value="devolucion" <?php echo ($salida['motivo'] == 'devolucion') ? 'selected' : ''; ?>>Devolución</option>
                                            <option value="ajuste" <?php echo ($salida['motivo'] == 'ajuste') ? 'selected' : ''; ?>>Ajuste</option>
                                            <option value="traslado" <?php echo ($salida['motivo'] == 'traslado') ? 'selected' : ''; ?>>Traslado</option>
                                            <option value="perdida" <?php echo ($salida['motivo'] == 'perdida') ? 'selected' : ''; ?>>Pérdida</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="factura_remision" class="form-label">Factura/Remisión</label>
                                        <input type="text" class="form-control" id="factura_remision" name="factura_remision" 
                                               value="<?php echo htmlspecialchars($salida['factura_remision'] ?? ''); ?>" 
                                               placeholder="Número de factura o remisión">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="beneficiario_tipo" class="form-label">Tipo de Beneficiario</label>
                                        <select class="form-select" id="beneficiario_tipo" name="beneficiario_tipo">
                                            <option value="">Seleccionar tipo...</option>
                                            <option value="proveedor" <?php echo ($salida['destinatario_tipo'] == 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
                                            <option value="cliente" <?php echo ($salida['destinatario_tipo'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                            <option value="interno" <?php echo ($salida['destinatario_tipo'] == 'interno') ? 'selected' : ''; ?>>Interno</option>
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
                                    <button type="submit" class="btn btn-warning">
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
            const beneficiarioTipo = '<?php echo $salida['destinatario_tipo']; ?>';
            const beneficiarioId = '<?php echo $salida['destinatario_id']; ?>';
            
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