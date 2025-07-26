<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Incluir el controlador
require_once '../controlador/ControladorVentas.php';

// Crear instancia del controlador
$controladorVentas = new ControladorVentas($pdo);

// Obtener datos usando el controlador
$siguiente_factura = $controladorVentas->obtenerSiguienteFactura();
$clientes = $controladorVentas->obtenerClientes();
$productos = $controladorVentas->obtenerProductos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Sistema de Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store me-2"></i>
                Sistema de Vendedor
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=menu_principal">
                            <i class="fas fa-tachometer-alt me-1"></i>Menú Principal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?page=ventas">
                            <i class="fas fa-shopping-cart me-1"></i>Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=pedidos">
                            <i class="fas fa-clipboard-list me-1"></i>Pedidos
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=perfil">
                                <i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?page=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Nueva Venta
                    </h1>
                    <a href="index.php?page=ventas" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Ventas
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <form id="formNuevaVenta" method="POST" action="procesar_venta.php">
                            <!-- Información básica -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="factura" class="form-label fw-bold">Número de Factura</label>
                                        <input type="text" class="form-control form-control-lg" id="factura" name="factura" 
                                               value="<?php echo $siguiente_factura; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cliente_id" class="form-label fw-bold">Cliente</label>
                                        <select class="form-select form-select-lg" id="cliente_id" name="cliente_id" required>
                                            <option value="">Seleccionar cliente...</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id']; ?>">
                                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de Productos -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">
                                        <i class="fas fa-boxes text-primary me-2"></i>Productos
                                    </h4>
                                    <button type="button" class="btn btn-success btn-lg" id="agregarProductoNuevaVenta" style="display: none;">
                                        <i class="fas fa-plus me-2"></i>Agregar Producto
                                    </button>
                                </div>
                                
                                <div id="productosContainer">
                                    <!-- Producto 1 -->
                                    <div class="producto-row border rounded p-4 mb-3 bg-white" data-producto-id="1">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0 text-primary">Producto #1</h5>
                                            <button type="button" class="btn btn-danger btn-sm remover-producto" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Producto</label>
                                                <select class="form-select producto-select" name="productos[1][producto_id]" required>
                                                    <option value="">Seleccionar producto...</option>
                                                    <?php foreach ($productos as $producto): ?>
                                                        <option value="<?php echo $producto['id']; ?>" 
                                                                data-tipo="<?php echo htmlspecialchars($producto['tipo_producto']); ?>" 
                                                                data-precio="<?php echo $producto['precio']; ?>"
                                                                data-color="<?php echo $producto['color_id']; ?>"
                                                                data-talla="<?php echo $producto['tallas_id']; ?>">
                                                            <?php echo htmlspecialchars($producto['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="producto-detalles mt-1" style="display: none;">
                                                    <small class="text-info">
                                                        <span class="color-info"></span> | <span class="talla-info"></span>
                                                    </small>
                                                </div>
                                                <div class="stock-info mt-1" style="display: none;">
                                                    <small class="text-muted">Stock: <span class="stock-cantidad">0</span> unidades</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold">Cantidad</label>
                                                <input type="number" class="form-control cantidad-input" 
                                                       name="productos[1][cantidad]" placeholder="Cantidad" min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-bold">Precio</label>
                                                <input type="number" class="form-control precio-input" 
                                                       name="productos[1][precio]" placeholder="Precio" step="0.01" readonly>
                                            </div>
                                            <div class="col-md-1">
                                                <label class="form-label fw-bold">Subtotal</label>
                                                <input type="number" class="form-control subtotal-input" 
                                                       name="productos[1][subtotal]" placeholder="Subtotal" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total de la Venta -->
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h4 class="mb-0">Total de la Venta</h4>
                                                <h3 class="mb-0" id="total_venta_display">$0.00</h3>
                                            </div>
                                            <input type="hidden" id="total_venta" name="total_venta" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-secondary btn-lg me-3" onclick="history.back()">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Guardar Venta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para nueva venta -->
    <script>
    // Variables globales
    let productoCounter = 1;
    let stockCache = {}; // Cache para evitar consultas repetidas

    // Función para mostrar notificación
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    // Función para verificar stock de un producto
    async function verificarStock(productoId, cantidad = 1) {
        try {
            const response = await fetch(`verificar_stock.php?producto_id=${productoId}&cantidad=${cantidad}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            
            if (data.success) {
                stockCache[productoId] = data;
                return data;
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error al verificar stock:', error);
            mostrarNotificacion('Error al verificar stock: ' + error.message, 'danger');
            return null;
        }
    }

    // Función para actualizar información de stock en la interfaz
    function actualizarStockUI(productoRow, stockData) {
        const stockInfo = productoRow.querySelector('.stock-info');
        const stockCantidad = productoRow.querySelector('.stock-cantidad');
        const cantidadInput = productoRow.querySelector('.cantidad-input');
        
        if (stockInfo && stockCantidad) {
            stockCantidad.textContent = stockData.stock_actual;
            stockInfo.style.display = 'block';
            
            // Cambiar color según stock
            stockInfo.className = 'stock-info mt-1';
            if (stockData.stock_actual <= 12) {
                stockInfo.classList.add('text-danger');
            } else if (stockData.stock_actual <= 20) {
                stockInfo.classList.add('text-warning');
            } else {
                stockInfo.classList.add('text-success');
            }
            
            // Configurar máximo en el input de cantidad
            if (cantidadInput) {
                const maxCantidad = Math.max(0, stockData.stock_actual - 12);
                cantidadInput.setAttribute('max', maxCantidad);
                cantidadInput.setAttribute('data-stock', stockData.stock_actual);
                
                if (maxCantidad === 0) {
                    cantidadInput.disabled = true;
                    cantidadInput.placeholder = 'Sin stock disponible';
                } else {
                    cantidadInput.disabled = false;
                    cantidadInput.placeholder = `Máx: ${maxCantidad}`;
                }
            }
        }
    }

    // Función para validar cantidad ingresada
    function validarCantidad(input, stockData) {
        const cantidad = parseInt(input.value) || 0;
        const maxPermitido = Math.max(0, stockData.stock_actual - 12);
        
        if (cantidad > maxPermitido) {
            input.value = maxPermitido;
            mostrarNotificacion(`Cantidad ajustada. Máximo permitido: ${maxPermitido} (debe quedar mínimo 12 en stock)`, 'warning');
        }
        
        if (cantidad > stockData.stock_actual) {
            input.value = stockData.stock_actual;
            mostrarNotificacion(`Stock insuficiente. Disponible: ${stockData.stock_actual}`, 'danger');
        }
    }
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Función para crear nueva fila de producto
    function crearFilaProducto(numero) {
        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'producto-row border rounded p-4 mb-3 bg-white';
        nuevaFila.setAttribute('data-producto-id', numero);
        
        nuevaFila.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-primary">Producto #${numero}</h5>
                <button type="button" class="btn btn-danger btn-sm remover-producto">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Producto</label>
                    <select class="form-select producto-select" name="productos[${numero}][producto_id]" required>
                        <option value="">Seleccionar producto...</option>
                        ${obtenerOpcionesProductos()}
                    </select>
                    <div class="producto-detalles mt-1" style="display: none;">
                        <small class="text-info">
                            <span class="color-info"></span> | <span class="talla-info"></span>
                        </small>
                    </div>
                    <div class="stock-info mt-1" style="display: none;">
                        <small class="text-muted">Stock: <span class="stock-cantidad">0</span> unidades</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Cantidad</label>
                    <input type="number" class="form-control cantidad-input" 
                           name="productos[${numero}][cantidad]" placeholder="Cantidad" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Precio</label>
                    <input type="number" class="form-control precio-input" 
                           name="productos[${numero}][precio]" placeholder="Precio" step="0.01" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">Subtotal</label>
                    <input type="number" class="form-control subtotal-input" 
                           name="productos[${numero}][subtotal]" placeholder="Subtotal" readonly>
                </div>
            </div>
        `;
        
        return nuevaFila;
    }

    // Función para obtener opciones de productos
    function obtenerOpcionesProductos() {
        const primerSelect = document.querySelector('.producto-select');
        if (primerSelect) {
            return primerSelect.innerHTML.replace(/name="productos\[1]\[producto_id\]"/g, '');
        }
        return '';
    }

    // Función para mostrar detalles del producto seleccionado
    async function mostrarDetallesProducto(productoSelect, fila) {
        const selectedOption = productoSelect.options[productoSelect.selectedIndex];
        const colorId = selectedOption.getAttribute('data-color');
        const tallaId = selectedOption.getAttribute('data-talla');
        
        const detallesDiv = fila.querySelector('.producto-detalles');
        const colorInfo = fila.querySelector('.color-info');
        const tallaInfo = fila.querySelector('.talla-info');
        
        if (colorId && tallaId && detallesDiv) {
            try {
                // Obtener nombre del color
                const colorResponse = await fetch(`verificar_stock.php?obtener_color=${colorId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                // Obtener nombre de la talla  
                const tallaResponse = await fetch(`verificar_stock.php?obtener_talla=${tallaId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (colorResponse.ok && tallaResponse.ok) {
                    const colorData = await colorResponse.json();
                    const tallaData = await tallaResponse.json();
                    
                    if (colorData.success && tallaData.success) {
                        colorInfo.textContent = `Color: ${colorData.nombre}`;
                        tallaInfo.textContent = `Talla: ${tallaData.nombre}`;
                        detallesDiv.style.display = 'block';
                    }
                }
            } catch (error) {
                console.log('No se pudieron cargar los detalles del producto');
                colorInfo.textContent = `Color ID: ${colorId}`;
                tallaInfo.textContent = `Talla ID: ${tallaId}`;
                detallesDiv.style.display = 'block';
            }
        } else {
            detallesDiv.style.display = 'none';
        }
    }

    // Función para cargar precio del producto
    function cargarPrecioProducto(productoSelect, precioInput) {
        const selectedOption = productoSelect.options[productoSelect.selectedIndex];
        const precio = parseFloat(selectedOption.getAttribute('data-precio')) || 0;
        precioInput.value = precio.toFixed(2);
        calcularSubtotalProducto(productoSelect);
    }

    // Función para calcular subtotal de un producto
    function calcularSubtotalProducto(productoSelect) {
        const fila = productoSelect.closest('.producto-row');
        const cantidadInput = fila.querySelector('.cantidad-input');
        const precioInput = fila.querySelector('.precio-input');
        const subtotalInput = fila.querySelector('.subtotal-input');
        
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio = parseFloat(precioInput.value) || 0;
        const subtotal = cantidad * precio;
        
        subtotalInput.value = subtotal.toFixed(2);
        calcularTotal();
    }

    // Función para agregar producto
    function agregarProducto() {
        console.log('Agregando nuevo producto');
        productoCounter++;
        const nuevaFila = crearFilaProducto(productoCounter);
        const container = document.getElementById('productosContainer');
        container.appendChild(nuevaFila);
        
        // Configurar event listeners para la nueva fila
        configurarEventListeners(nuevaFila);
        
        // Mostrar botón de remover en la primera fila
        if (productoCounter > 1) {
            document.querySelector('.remover-producto').style.display = 'block';
        }
        
        mostrarNotificacion('Nuevo producto agregado', 'success');
        console.log('Producto agregado exitosamente');
    }

    // Función para configurar event listeners en una fila
    function configurarEventListeners(fila) {
        const productoSelect = fila.querySelector('.producto-select');
        const cantidadInput = fila.querySelector('.cantidad-input');
        const precioInput = fila.querySelector('.precio-input');
        
        // Event listener para cambio de producto
        if (productoSelect) {
            productoSelect.addEventListener('change', async function() {
                cargarPrecioProducto(this, precioInput);
                
                // Verificar stock cuando se selecciona un producto
                const productoId = this.value;
                if (productoId) {
                    // Mostrar detalles del producto (color y talla)
                    await mostrarDetallesProducto(this, fila);
                    
                    // Verificar stock
                    const stockData = await verificarStock(productoId, 1);
                    if (stockData) {
                        actualizarStockUI(fila, stockData);
                    }
                } else {
                    // Ocultar info de stock y detalles si no hay producto seleccionado
                    const stockInfo = fila.querySelector('.stock-info');
                    const detallesInfo = fila.querySelector('.producto-detalles');
                    if (stockInfo) stockInfo.style.display = 'none';
                    if (detallesInfo) detallesInfo.style.display = 'none';
                }
            });
        }
        
        // Event listener para cambio de cantidad
        if (cantidadInput) {
            cantidadInput.addEventListener('input', async function() {
                const cantidad = parseInt(this.value) || 0;
                const productoId = productoSelect.value;
                
                if (productoId && cantidad > 0) {
                    // Verificar stock con la nueva cantidad
                    const stockData = await verificarStock(productoId, cantidad);
                    if (stockData) {
                        if (stockCache[productoId]) {
                            validarCantidad(this, stockData);
                        }
                    }
                }
                
                calcularSubtotalProducto(productoSelect);
            });
            
            // Event listener para validar cuando se pierde el foco
            cantidadInput.addEventListener('blur', function() {
                const cantidad = parseInt(this.value) || 0;
                const maxPermitido = parseInt(this.getAttribute('max')) || 0;
                
                if (cantidad > maxPermitido) {
                    this.value = maxPermitido;
                    mostrarNotificacion(`Cantidad ajustada al máximo permitido: ${maxPermitido}`, 'warning');
                    calcularSubtotalProducto(productoSelect);
                }
            });
        }
    }

    // Función para remover producto
    function removerProducto(elemento) {
        const fila = elemento.closest('.producto-row');
        if (productoCounter > 1) {
            fila.remove();
            productoCounter--;
            
            if (productoCounter === 1) {
                document.querySelector('.remover-producto').style.display = 'none';
            }
        }
    }

    // Función para calcular total
    function calcularTotal() {
        const subtotales = document.querySelectorAll('.subtotal-input');
        let total = 0;
        subtotales.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        document.getElementById('total_venta').value = total.toFixed(2);
        document.getElementById('total_venta_display').textContent = '$' + total.toFixed(2);
    }

    // Función para validar toda la venta antes de enviar
    async function validarVentaCompleta() {
        const productosRows = document.querySelectorAll('.producto-row');
        const productos = [];
        
        for (const row of productosRows) {
            const productoSelect = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad-input');
            
            if (productoSelect.value && cantidadInput.value) {
                productos.push({
                    producto_id: parseInt(productoSelect.value),
                    cantidad: parseInt(cantidadInput.value)
                });
            }
        }
        
        if (productos.length === 0) {
            mostrarNotificacion('Debe agregar al menos un producto', 'danger');
            return false;
        }
        
        try {
            const response = await fetch('verificar_stock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ productos: productos })
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (!data.puede_procesar_venta) {
                    let mensajesError = [];
                    data.validaciones.forEach(validacion => {
                        if (!validacion.validacion.puede_vender) {
                            mensajesError.push(validacion.mensaje);
                        }
                    });
                    
                    mostrarNotificacion('No se puede procesar la venta:<br>' + mensajesError.join('<br>'), 'danger');
                    return false;
                }
                return true;
            } else {
                mostrarNotificacion('Error al validar la venta: ' + data.error, 'danger');
                return false;
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión al validar la venta', 'danger');
            return false;
        }
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando nueva venta');
        
        // Configurar la primera fila de producto
        const primeraFila = document.querySelector('.producto-row');
        if (primeraFila) {
            configurarEventListeners(primeraFila);
        }
        
        // Event listener para agregar producto
        const btnAgregar = document.getElementById('agregarProductoNuevaVenta');
        if (btnAgregar) {
            btnAgregar.addEventListener('click', agregarProducto);
            console.log('Event listener agregado al botón agregar producto');
        }
        
        // Event listener para remover productos
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remover-producto')) {
                removerProducto(e.target.closest('.remover-producto'));
            }
        });
        
        // Validación del formulario antes de enviar
        const formulario = document.getElementById('formNuevaVenta');
        if (formulario) {
            formulario.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Mostrar loading
                const btnSubmit = this.querySelector('button[type="submit"]');
                const textoOriginal = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Validando...';
                btnSubmit.disabled = true;
                
                try {
                    const esValido = await validarVentaCompleta();
                    
                    if (esValido) {
                        // Si la validación es exitosa, enviar el formulario
                        btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i>Procesando...';
                        this.submit();
                    }
                } catch (error) {
                    mostrarNotificacion('Error al procesar la venta', 'danger');
                } finally {
                    // Restaurar botón
                    setTimeout(() => {
                        btnSubmit.innerHTML = textoOriginal;
                        btnSubmit.disabled = false;
                    }, 2000);
                }
            });
        }
    });
    </script>
</body>
</html> 