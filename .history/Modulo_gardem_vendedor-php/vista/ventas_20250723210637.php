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
$colores = $controladorVentas->obtenerColores();
$tallas = $controladorVentas->obtenerTallas();
$ventas = $controladorVentas->obtenerVentas();
$total_ventas = count($ventas);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 mb-3">
        <h1 class="h2"><i class="fas fa-chart-line text-primary me-2"></i>Ventas</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaVentaModal">
            <i class="fas fa-plus me-2"></i>Nueva Venta
        </button>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">
            <strong>¡Éxito!</strong> La venta con factura <?php echo htmlspecialchars($_GET['factura'] ?? ''); ?> ha sido registrada correctamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Ventas (<?php echo $total_ventas; ?> registros)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($ventas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <h5>No se encontraron ventas</h5>
                    <p>La tabla productos_ventas está vacía o no existe.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Factura</th>
                                <th>Productos</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo $venta['id']; ?></td>
                                <td><?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente #' . $venta['cliente_id']); ?></td>
                                <td><?php echo $venta['fecha']; ?></td>
                                <td><?php echo htmlspecialchars($venta['factura']); ?></td>
                                <td><?php echo htmlspecialchars($venta['productos']); ?></td>
                                <td>$<?php echo number_format($venta['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nueva Venta -->
<div class="modal fade" id="nuevaVentaModal" tabindex="-1" aria-labelledby="nuevaVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevaVentaModalLabel">
                    <i class="fas fa-file-invoice me-2"></i>FACTURA DE VENTA
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaVenta" method="POST" action="procesar_venta.php">
                <div class="modal-body">
                    <!-- Encabezado de Factura -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>DATOS DE LA EMPRESA</h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-primary mb-1">GARDEL CATÁLOGO</h5>
                                    <p class="mb-1"><strong>Dirección:</strong> Calle Principal #123</p>
                                    <p class="mb-1"><strong>Teléfono:</strong> (123) 456-7890</p>
                                    <p class="mb-0"><strong>Email:</strong> info@gardel.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>DATOS DEL CLIENTE</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="cliente_id" class="form-label fw-bold">Cliente</label>
                                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                                            <option value="">Seleccionar cliente...</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Factura #</label>
                                            <input type="text" class="form-control" id="factura" name="factura" value="<?php echo $siguiente_factura; ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Fecha</label>
                                            <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                                         <!-- Tabla de Productos -->
                     <div class="mb-4">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                             <h5 class="mb-0 text-dark">
                                 <i class="fas fa-list me-2"></i>DETALLE DE PRODUCTOS
                             </h5>
                             <button type="button" class="btn btn-success" id="agregarProducto">
                                 <i class="fas fa-plus me-2"></i>Agregar Producto
                             </button>
                         </div>
                         
                         <div id="productosContainer">
                             <!-- Producto 1 -->
                             <div class="producto-row border rounded p-2 mb-2" data-producto-id="1">
                                 <div class="row g-2">
                                     <div class="col-md-4">
                                         <select class="form-select producto-select" name="productos[1][producto_id]" required>
                                             <option value="">Producto...</option>
                                             <?php foreach ($productos as $producto): ?>
                                                 <option value="<?php echo $producto['id']; ?>" data-tipo="<?php echo htmlspecialchars($producto['tipo_producto']); ?>" data-precio="<?php echo $producto['precio']; ?>">
                                                     <?php echo htmlspecialchars($producto['nombre']); ?>
                                                 </option>
                                             <?php endforeach; ?>
                                         </select>
                                     </div>
                                     <div class="col-md-2">
                                         <select class="form-select" name="productos[1][color_id]" required>
                                             <option value="">Color...</option>
                                             <?php foreach ($colores as $color): ?>
                                                 <option value="<?php echo $color['id']; ?>">
                                                     <?php echo htmlspecialchars($color['nombre']); ?>
                                                 </option>
                                             <?php endforeach; ?>
                                         </select>
                                     </div>
                                     <div class="col-md-1">
                                         <select class="form-select talla-select" name="productos[1][talla_id]" required>
                                             <option value="">Talla...</option>
                                             <?php foreach ($tallas as $talla): ?>
                                                 <option value="<?php echo $talla['id']; ?>" data-categoria="<?php echo $talla['categoria']; ?>">
                                                     <?php echo htmlspecialchars($talla['nombre']); ?>
                                                 </option>
                                             <?php endforeach; ?>
                                         </select>
                                     </div>
                                     <div class="col-md-1">
                                         <input type="number" class="form-control cantidad-input" name="productos[1][cantidad]" placeholder="Cant." min="1" required>
                                     </div>
                                     <div class="col-md-2">
                                         <input type="number" class="form-control precio-input" name="productos[1][precio]" placeholder="Precio" step="0.01" readonly>
                                     </div>
                                     <div class="col-md-1">
                                         <input type="number" class="form-control subtotal-input" name="productos[1][subtotal]" placeholder="Total" step="0.01" readonly>
                                     </div>
                                     <div class="col-md-1">
                                         <button type="button" class="btn btn-danger btn-sm remover-producto">
                                             <i class="fas fa-trash"></i>
                                         </button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                    
                    <!-- Totales de Factura -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>RESUMEN DE TOTALES</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col">
                                            <label class="form-label fw-bold">Subtotal</label>
                                            <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
                                        </div>
                                        <div class="col">
                                            <label class="form-label fw-bold">IVA (19%)</label>
                                            <input type="text" class="form-control" id="iva" name="iva" readonly>
                                        </div>
                                        <div class="col">
                                            <label class="form-label fw-bold text-success">TOTAL A PAGAR</label>
                                            <input type="text" class="form-control fw-bold text-success" id="total_venta" name="total_venta" readonly>
                                        </div>
                                        <div class="col">
                                            <button type="button" class="btn btn-secondary w-100" id="btnCancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="col">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Script optimizado para ventas -->
<script>
(function() {
    'use strict';
    
    // Variables globales del módulo
    let ventasModule = {
        init: function() {
            this.bindEvents();
            this.initializeFirstRow();
        },
        
        bindEvents: function() {
            // Botón agregar producto
            const botonAgregar = document.getElementById('agregarProducto');
            if (botonAgregar) {
                botonAgregar.addEventListener('click', this.agregarProducto.bind(this));
            }
            
            // Botón cancelar
            const botonCancelar = document.getElementById('btnCancelar');
            if (botonCancelar) {
                botonCancelar.addEventListener('click', this.cancelarVenta.bind(this));
            }
            
            // Event delegation para elementos dinámicos
            document.addEventListener('click', this.handleClick.bind(this));
            document.addEventListener('change', this.handleChange.bind(this));
            document.addEventListener('input', this.handleInput.bind(this));
        },
        
        initializeFirstRow: function() {
            // Inicializar la primera fila si existe
            const primeraFila = document.querySelector('.producto-row');
            if (primeraFila) {
                this.setupRowEvents(primeraFila);
            }
        },
        
                 agregarProducto: function(e) {
             // Prevenir múltiples clics
             if (e) {
                 e.preventDefault();
                 e.stopPropagation();
             }
             
             const container = document.getElementById('productosContainer');
             if (!container) return;
             
             const productosExistentes = container.querySelectorAll('.producto-row');
             const numeroProducto = productosExistentes.length + 1;
             
             // Crear nueva fila
             const nuevaFila = this.crearFilaProducto(numeroProducto);
             container.appendChild(nuevaFila);
             
             // Configurar eventos de la nueva fila
             this.setupRowEvents(nuevaFila);
             
             // Enfocar el primer campo de la nueva fila
             setTimeout(() => {
                 const primerSelect = nuevaFila.querySelector('.producto-select');
                 if (primerSelect) primerSelect.focus();
             }, 100);
         },
        
        crearFilaProducto: function(numeroProducto) {
            const fila = document.createElement('div');
            fila.className = 'producto-row border rounded p-3 mb-3';
            fila.setAttribute('data-producto-id', numeroProducto);
            
            // Obtener opciones de productos del primer select
            const opcionesProductos = this.obtenerOpcionesProductos();
            const opcionesColores = this.obtenerOpcionesColores();
            const opcionesTallas = this.obtenerOpcionesTallas();
            
                         fila.innerHTML = `
                 <div class="row g-2">
                     <div class="col-md-4">
                         <select class="form-select producto-select" name="productos[${numeroProducto}][producto_id]" required>
                             <option value="">Producto...</option>
                             ${opcionesProductos}
                         </select>
                     </div>
                     <div class="col-md-2">
                         <select class="form-select" name="productos[${numeroProducto}][color_id]" required>
                             <option value="">Color...</option>
                             ${opcionesColores}
                         </select>
                     </div>
                     <div class="col-md-1">
                         <select class="form-select talla-select" name="productos[${numeroProducto}][talla_id]" required>
                             <option value="">Talla...</option>
                             ${opcionesTallas}
                         </select>
                     </div>
                     <div class="col-md-1">
                         <input type="number" class="form-control cantidad-input" name="productos[${numeroProducto}][cantidad]" placeholder="Cant." min="1" required>
                     </div>
                     <div class="col-md-2">
                         <input type="number" class="form-control precio-input" name="productos[${numeroProducto}][precio]" placeholder="Precio" step="0.01" readonly>
                     </div>
                     <div class="col-md-1">
                         <input type="number" class="form-control subtotal-input" name="productos[${numeroProducto}][subtotal]" placeholder="Total" step="0.01" readonly>
                     </div>
                     <div class="col-md-1">
                         <button type="button" class="btn btn-danger btn-sm remover-producto" title="Eliminar producto">
                             <i class="fas fa-trash"></i>
                         </button>
                     </div>
                 </div>
             `;
            
            return fila;
        },
        
        setupRowEvents: function(fila) {
            const select = fila.querySelector('.producto-select');
            const cantidad = fila.querySelector('.cantidad-input');
            
            if (select) {
                select.addEventListener('change', this.calcularPrecioYSubtotal.bind(this));
            }
            if (cantidad) {
                cantidad.addEventListener('input', this.calcularPrecioYSubtotal.bind(this));
            }
        },
        
        handleClick: function(e) {
            if (e.target.closest('.remover-producto')) {
                e.preventDefault();
                e.stopPropagation();
                this.removerProducto(e.target.closest('.producto-row'));
            }
        },
        
        handleChange: function(e) {
            if (e.target.classList.contains('producto-select')) {
                this.calcularPrecioYSubtotal(e.target);
            }
        },
        
        handleInput: function(e) {
            if (e.target.classList.contains('cantidad-input')) {
                this.calcularPrecioYSubtotal(e.target);
            }
        },
        
        removerProducto: function(fila) {
            if (fila && fila.parentNode) {
                fila.remove();
                this.renumerarProductos();
                this.calcularTotal();
            }
        },
        
                 renumerarProductos: function() {
             const productos = document.querySelectorAll('.producto-row');
             productos.forEach((producto, index) => {
                 producto.setAttribute('data-producto-id', index + 1);
             });
         },
        
        obtenerOpcionesProductos: function() {
            const primerSelect = document.querySelector('.producto-select');
            if (primerSelect) {
                return primerSelect.innerHTML.replace(/name="productos\[\d+\]\[producto_id\]"/g, '');
            }
            return '';
        },
        
        obtenerOpcionesColores: function() {
            const primerSelect = document.querySelector('select[name*="[color_id]"]');
            if (primerSelect) {
                return primerSelect.innerHTML.replace(/name="productos\[\d+\]\[color_id\]"/g, '');
            }
            return '';
        },
        
        obtenerOpcionesTallas: function() {
            const primerSelect = document.querySelector('select[name*="[talla_id]"]');
            if (primerSelect) {
                return primerSelect.innerHTML.replace(/name="productos\[\d+\]\[talla_id\]"/g, '');
            }
            return '';
        },
        
                 calcularPrecioYSubtotal: function(elemento) {
             const fila = elemento.closest('.producto-row');
             if (!fila) return;
             
             const select = fila.querySelector('.producto-select');
             const cantidad = fila.querySelector('.cantidad-input');
             const precio = fila.querySelector('.precio-input');
             const subtotal = fila.querySelector('.subtotal-input');
             
             if (select && select.value) {
                 const precioSeleccionado = select.options[select.selectedIndex].getAttribute('data-precio');
                 if (precioSeleccionado && precio) {
                     precio.value = precioSeleccionado;
                 }
             }
             
             // Calcular subtotal del producto (cantidad × precio unitario)
             if (cantidad && cantidad.value && precio && precio.value && subtotal) {
                 const cantidadValor = parseFloat(cantidad.value) || 0;
                 const precioValor = parseFloat(precio.value) || 0;
                 const subtotalValor = cantidadValor * precioValor;
                 subtotal.value = subtotalValor.toFixed(2);
             } else if (subtotal) {
                 subtotal.value = '';
             }
             
             this.calcularTotal();
         },
        
                                   calcularTotal: function() {
              let subtotal = 0;
              const productos = document.querySelectorAll('.producto-row');
              
              productos.forEach(producto => {
                  const subtotalProducto = producto.querySelector('.subtotal-input');
                  
                  if (subtotalProducto && subtotalProducto.value) {
                      const subtotalValor = parseFloat(subtotalProducto.value) || 0;
                      subtotal += subtotalValor;
                  }
              });
             
             // Calcular IVA (16%)
             const iva = subtotal * 0.16;
             const total = subtotal + iva;
             
             // Actualizar campos
             const subtotalElement = document.getElementById('subtotal');
             const ivaElement = document.getElementById('iva');
             const totalElement = document.getElementById('total_venta');
             
             if (subtotalElement) subtotalElement.value = '$' + subtotal.toFixed(2);
             if (ivaElement) ivaElement.value = '$' + iva.toFixed(2);
             if (totalElement) totalElement.value = '$' + total.toFixed(2);
         },
         
         cancelarVenta: function() {
             // Limpiar formulario
             const form = document.getElementById('formNuevaVenta');
             if (form) {
                 form.reset();
             }
             
             // Limpiar cliente seleccionado
             const clienteSelect = document.getElementById('cliente_id');
             if (clienteSelect) {
                 clienteSelect.value = '';
             }
             
             // Limpiar productos agregados (dejar solo el primero)
             const container = document.getElementById('productosContainer');
             if (container) {
                 const productos = container.querySelectorAll('.producto-row');
                 // Mantener solo el primer producto y limpiarlo
                 for (let i = 1; i < productos.length; i++) {
                     productos[i].remove();
                 }
                 
                 // Limpiar el primer producto
                 const primerProducto = container.querySelector('.producto-row');
                 if (primerProducto) {
                     const selects = primerProducto.querySelectorAll('select');
                     const inputs = primerProducto.querySelectorAll('input');
                     
                     selects.forEach(select => {
                         select.value = '';
                     });
                     
                     inputs.forEach(input => {
                         if (input.type === 'number') {
                             input.value = '';
                         }
                     });
                 }
             }
             
             // Limpiar totales
             const subtotalElement = document.getElementById('subtotal');
             const ivaElement = document.getElementById('iva');
             const totalElement = document.getElementById('total_venta');
             
             if (subtotalElement) subtotalElement.value = '';
             if (ivaElement) ivaElement.value = '';
             if (totalElement) totalElement.value = '';
             
             // NO cerrar el modal - solo limpiar los campos
             // El modal permanece abierto para que el usuario pueda seguir trabajando
         }
    };
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            ventasModule.init();
        });
    } else {
        ventasModule.init();
    }
    
})();
</script>

