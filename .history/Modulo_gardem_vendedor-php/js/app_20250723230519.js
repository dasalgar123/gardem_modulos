/**
 * JavaScript unificado para el Sistema de Vendedor
 * Incluye todas las funcionalidades de ventas, pedidos y productos
 * 
 * FUNCIONES PRINCIPALES:
 * - initializeApp(): Inicializa toda la aplicación
 * - initializeVentas(): Maneja la funcionalidad de ventas
 * - initializePedidos(): Maneja la funcionalidad de pedidos
 * - initializeProductos(): Maneja la funcionalidad de productos
 * - initializeClientes(): Maneja la funcionalidad de clientes
 */

// Variables globales del sistema
let productoCounter = 1; // Contador para productos en ventas

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializa todas las funcionalidades de la aplicación
 * Esta es la función principal que se ejecuta al cargar la página
 */
function initializeApp() {
    console.log('🚀 Iniciando Sistema de Vendedor...');
    
    // Inicializar funcionalidades comunes del sistema
    initializeForms();        // Formularios y validaciones
    initializeTables();       // Tablas y búsquedas
    initializeNotifications(); // Sistema de notificaciones
    
    // Inicializar funcionalidades específicas según la página actual
    const currentPage = getCurrentPage();
    initializePageSpecific(currentPage);
    
    console.log('✅ Sistema de Vendedor inicializado correctamente');
}

/**
 * Obtiene la página actual desde la URL
 * Detecta automáticamente en qué sección del sistema estamos
 */
function getCurrentPage() {
    const path = window.location.pathname;
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page');
    
    console.log('📍 Página detectada:', page || 'dashboard');
    
    if (page) return page;
    return 'dashboard'; // Página por defecto
}

/**
 * Inicializa funcionalidades específicas según la página actual
 * Cada página tiene sus propias funciones y comportamientos
 */
function initializePageSpecific(page) {
    console.log('🔧 Inicializando funcionalidades para:', page);
    
    switch(page) {
        case 'ventas':
            console.log('🛒 Inicializando módulo de ventas...');
            initializeVentas();
            break;
        case 'pedidos':
            console.log('📋 Inicializando módulo de pedidos...');
            initializePedidos();
            break;
        case 'productos':
            console.log('📦 Inicializando módulo de productos...');
            initializeProductos();
            break;
        case 'clientes':
            console.log('👥 Inicializando módulo de clientes...');
            initializeClientes();
            break;
        default:
            console.log('🏠 Página principal - sin funcionalidades específicas');
    }
}

/**
 * Inicializa los formularios del sistema
 * Configura validaciones y botones de reset
 */
function initializeForms() {
    console.log('📝 Inicializando formularios...');
    
    // Configurar validación automática de formularios
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', validateForm);
        console.log('✅ Formulario con validación configurado:', form.id || 'sin ID');
    });
    
    // Configurar botones de limpiar formularios
    const resetButtons = document.querySelectorAll('.btn-reset');
    resetButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (form) {
                form.reset();
                clearFormErrors(form);
                console.log('🧹 Formulario limpiado:', form.id || 'sin ID');
            }
        });
    });
    
    console.log(`📝 Formularios inicializados: ${forms.length} con validación, ${resetButtons.length} botones de reset`);
}

/**
 * Valida un formulario
 */
function validateForm(e) {
    const form = e.target;
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    clearFormErrors(form);
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showFieldError(field, 'Este campo es requerido');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        showNotification('Por favor, completa todos los campos requeridos', 'error');
    }
}

/**
 * Muestra error en un campo
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let errorMsg = field.parentNode.querySelector('.invalid-feedback');
    if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'invalid-feedback';
        field.parentNode.appendChild(errorMsg);
    }
    errorMsg.textContent = message;
}

/**
 * Limpia errores de un formulario
 */
function clearFormErrors(form) {
    const errorFields = form.querySelectorAll('.is-invalid');
    errorFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    const errorMessages = form.querySelectorAll('.invalid-feedback');
    errorMessages.forEach(msg => {
        msg.remove();
    });
}

/**
 * Inicializa las tablas
 */
function initializeTables() {
    // Búsqueda en tiempo real
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('.table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
}

/**
 * Inicializa las notificaciones
 */
function initializeNotifications() {
    // Auto-remover notificaciones después de 5 segundos
    setInterval(() => {
        const notifications = document.querySelectorAll('.alert');
        notifications.forEach(notification => {
            if (!notification.classList.contains('alert-persistent')) {
                notification.remove();
            }
        });
    }, 5000);
}

/**
 * Muestra una notificación
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio del contenido principal
    const mainContent = document.querySelector('.container-fluid');
    if (mainContent) {
        mainContent.insertBefore(notification, mainContent.firstChild);
    }
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Confirma una acción
 */
function confirmAction(message = '¿Estás seguro de que quieres realizar esta acción?') {
    return confirm(message);
}

// ========================================
// FUNCIONALIDADES DE VENTAS
// ========================================

/**
 * Inicializa funcionalidades específicas de ventas
 */
function initializeVentas() {
    console.log('Inicializando módulo de ventas');
    
    // Verificar que estamos en la página de ventas
    const productosContainer = document.getElementById('productosContainer');
    if (!productosContainer) {
        console.log('No se encontró productosContainer - no estamos en la página de ventas');
        return;
    }
    
    // Inicializar el módulo de ventas
    if (ventasModule) {
        ventasModule.init();
        console.log('Módulo de ventas inicializado correctamente');
    } else {
        console.error('Error: No se pudo inicializar el módulo de ventas');
    }
}

/**
 * Módulo de ventas optimizado
 * Maneja toda la funcionalidad relacionada con ventas
 * 
 * FUNCIONES DEL MÓDULO:
 * - agregarProducto(): Agrega una nueva fila de producto
 * - removerProducto(): Elimina una fila de producto
 * - calcularPrecioYSubtotal(): Calcula precios y subtotales
 * - calcularTotal(): Calcula el total de la venta
 * - cancelarVenta(): Limpia el formulario de venta
 */
let ventasModule = {
    isAddingProduct: false, // Flag para evitar múltiples agregaciones simultáneas
    
    /**
     * Inicializa el módulo de ventas
     * Configura todos los event listeners y la primera fila
     */
    init: function() {
        console.log('🛒 Inicializando módulo de ventas...');
        this.bindEvents();
        this.initializeFirstRow();
        console.log('✅ Módulo de ventas inicializado correctamente');
    },
    
    /**
     * Configura todos los event listeners del módulo de ventas
     * Maneja botones y eventos de elementos dinámicos
     */
    bindEvents: function() {
        console.log('🔗 Configurando event listeners de ventas...');
        
        // Configurar botón para agregar producto
        const botonAgregar = document.getElementById('agregarProducto');
        if (botonAgregar) {
            botonAgregar.addEventListener('click', this.agregarProducto.bind(this));
            console.log('✅ Botón "Agregar Producto" configurado');
        } else {
            console.log('⚠️ No se encontró el botón "Agregar Producto"');
        }
        
        // Configurar botón para cancelar venta
        const botonCancelar = document.getElementById('btnCancelar');
        if (botonCancelar) {
            botonCancelar.addEventListener('click', this.cancelarVenta.bind(this));
            console.log('✅ Botón "Cancelar" configurado');
        } else {
            console.log('⚠️ No se encontró el botón "Cancelar"');
        }
        
        // Configurar event delegation para elementos dinámicos
        document.addEventListener('click', this.handleClick.bind(this));
        document.addEventListener('change', this.handleChange.bind(this));
        document.addEventListener('input', this.handleInput.bind(this));
        console.log('✅ Event delegation configurado para elementos dinámicos');
    },
    
    initializeFirstRow: function() {
        // Solo inicializar eventos en la primera fila, no agregar ninguna fila extra
        const primeraFila = document.querySelector('.producto-row');
        if (primeraFila) {
            this.setupRowEvents(primeraFila);
        }
    },
    
    /**
     * Agrega una nueva fila de producto al formulario de venta
     * Crea dinámicamente una nueva fila con todos los campos necesarios
     */
    agregarProducto: function(e) {
        // Prevenir múltiples clics simultáneos
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Evitar agregar múltiples productos al mismo tiempo
        if (this.isAddingProduct) {
            console.log('⚠️ Ya se está agregando un producto, espera...');
            return;
        }
        this.isAddingProduct = true;
        
        console.log('➕ Agregando nuevo producto...');
        
        const container = document.getElementById('productosContainer');
        if (!container) {
            console.log('❌ No se encontró el contenedor de productos');
            this.isAddingProduct = false;
            return;
        }
        
        const productosExistentes = container.querySelectorAll('.producto-row');
        const numeroProducto = productosExistentes.length + 1;
        
        console.log(`📦 Creando producto #${numeroProducto}`);
        
        // Crear nueva fila de producto
        const nuevaFila = this.crearFilaProducto(numeroProducto);
        container.appendChild(nuevaFila);
        
        // Configurar eventos de la nueva fila
        this.setupRowEvents(nuevaFila);
        
        // Enfocar el primer campo de la nueva fila
        setTimeout(() => {
            const primerSelect = nuevaFila.querySelector('.producto-select');
            if (primerSelect) primerSelect.focus();
            this.isAddingProduct = false; // Resetear el flag
            console.log('✅ Producto agregado exitosamente');
        }, 100);
    },
    
    crearFilaProducto: function(numeroProducto) {
        const fila = document.createElement('div');
        fila.className = 'producto-row border rounded p-2 mb-2';
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
    
    /**
     * Calcula el total de la venta incluyendo IVA
     * Suma todos los subtotales y aplica el 19% de IVA
     */
    calcularTotal: function() {
        let subtotal = 0;
        const productos = document.querySelectorAll('.producto-row');
        
        console.log('🧮 Calculando total de venta...');
        
        // Sumar todos los subtotales de productos
        productos.forEach((producto, index) => {
            const subtotalProducto = producto.querySelector('.subtotal-input');
            
            if (subtotalProducto && subtotalProducto.value) {
                const subtotalValor = parseFloat(subtotalProducto.value) || 0;
                subtotal += subtotalValor;
                console.log(`📦 Producto ${index + 1}: $${subtotalValor.toFixed(2)}`);
            }
        });
        
        // Calcular IVA (19%)
        const iva = subtotal * 0.19;
        const total = subtotal + iva;
        
        console.log(`💰 Subtotal: $${subtotal.toFixed(2)}`);
        console.log(`📊 IVA (19%): $${iva.toFixed(2)}`);
        console.log(`💵 Total: $${total.toFixed(2)}`);
        
        // Actualizar campos en el formulario
        const subtotalElement = document.getElementById('subtotal');
        const ivaElement = document.getElementById('iva');
        const totalElement = document.getElementById('total_venta');
        
        if (subtotalElement) subtotalElement.value = '$' + subtotal.toFixed(2);
        if (ivaElement) ivaElement.value = '$' + iva.toFixed(2);
        if (totalElement) totalElement.value = '$' + total.toFixed(2);
        
        console.log('✅ Total calculado y actualizado');
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
                productos[i].remove();}
            
            // Limpiar el primer producto
            const primerProducto = container.querySelector('.producto-row');
            if (primerProducto) {
                const selects = primerProducto.querySelectorAll('select');
                const inputs = primerProducto.querySelectorAll('input');
                
                selects.forEach(select => { select.value = ''; });
                
                inputs.forEach(input => {
                    if (input.type === 'number') { input.value = '';}});}
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

/**
 * Función para obtener opciones de productos
 */
function obtenerOpcionesProductos() {
    const primerSelect = document.querySelector('.producto-select');
    if (primerSelect) { return primerSelect.innerHTML.replace(/name="productos\[1]\[producto_id\]"/g, '');}
    return '';
}

/**
 * Función para obtener opciones de colores
 */
function obtenerOpcionesColores() {
    const primerSelect = document.querySelector('select[name="productos[1][color_id]"]');
    if (primerSelect) { return primerSelect.innerHTML.replace(/name="productos\[1\]\[color_id\]"/g, '');}
    return '';
}

/**
 * Función para obtener opciones de tallas
 */
function obtenerOpcionesTallas() {
    const primerSelect = document.querySelector('.talla-select');
    if (primerSelect) {return primerSelect.innerHTML.replace(/name="productos\[1\]\[talla_id\]"/g, '');}
    return '';
}

/**
 * Función para agregar un nuevo producto en ventas
 */
function agregarProductoVenta() {
    console.log('Función agregarProductoVenta DESHABILITADA');
    return false; // No hacer nada
}

/**
 * Función para remover un producto en ventas
 */
function removerProducto(elemento) {
    const fila = elemento.closest('.producto-row');
    if (productoCounter > 1) {
        fila.remove();
        productoCounter--;
        
        // Ocultar botón de remover en la primera fila si solo queda una
        if (productoCounter === 1) {
            document.querySelector('.remover-producto').style.display = 'none';
        }
        
        console.log('Producto removido');
    }
}

/**
 * Calcula los totales en ventas
 */
function calcularTotalesVentas() {
    let subtotal = 0;
    const productoRows = document.querySelectorAll('.producto-row');
    
    productoRows.forEach(row => {
        const cantidad = row.querySelector('.cantidad-input');
        const precio = row.querySelector('.precio-input');
        const subtotalInput = row.querySelector('.subtotal-input');
        
        if (cantidad && precio && subtotalInput) {
            const cantidadVal = parseFloat(cantidad.value) || 0;
            const precioVal = parseFloat(precio.value) || 0;
            const itemSubtotal = cantidadVal * precioVal;
            
            subtotalInput.value = itemSubtotal.toFixed(2);
            subtotal += itemSubtotal;
        }
    });
    
    const iva = subtotal * 0.19;
    const total = subtotal + iva;
    
    const subtotalElement = document.getElementById('subtotal');
    const ivaElement = document.getElementById('iva');
    const totalElement = document.getElementById('total');
    
    if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    if (ivaElement) ivaElement.textContent = `$${iva.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `$${total.toFixed(2)}`;
}

// ========================================
// FUNCIONALIDADES DE PEDIDOS
// ========================================

/**
 * Inicializa funcionalidades específicas de pedidos
 */
function initializePedidos() {
    console.log('Inicializando funcionalidades de pedidos');
    
    // Event listener para copiar correo
    document.querySelectorAll('.copy-email').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const email = this.getAttribute('data-email');
            if (email) {
                navigator.clipboard.writeText(email);
                showNotification('Correo copiado al portapapeles', 'info');
            }
        });
    });
}

// ========================================
// FUNCIONALIDADES DE PRODUCTOS
// ========================================

/**
 * Inicializa funcionalidades específicas de productos
 */
function initializeProductos() {
    console.log('Inicializando funcionalidades de productos');
    
    // Auto-submit del formulario de filtros cuando cambien los valores
    const filtroTipo = document.getElementById('tipo');
    const filtroBusqueda = document.getElementById('buscar');
    
    if (filtroTipo) {
        filtroTipo.addEventListener('change', function() {document.querySelector('form[method="GET"]').submit();});
    }
    
    if (filtroBusqueda) {
        let timeout;
        filtroBusqueda.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() { document.querySelector('form[method="GET"]').submit();}, 500); // Esperar 500 después de que el usuario deje de escribir
        });
    }
    
    // Event listeners para formularios de producto
    const formularioProducto = document.getElementById('formulario-producto');
    if (formularioProducto) {
        formularioProducto.addEventListener('submit', function(e) {
            if (!validarFormularioProducto()) {e.preventDefault();}
        });
    }
    
    // Event listener para previsualización de imagen
    const inputImagen = document.getElementById('imagen');
    if (inputImagen) {
        inputImagen.addEventListener('change', function() {
            previsualizarImagen(this);
        });
    }
    
    // Event listeners para cálculo de precio
    const inputPrecio = document.getElementById('precio');
    const inputDescuento = document.getElementById('descuento');
    
    if (inputPrecio) { inputPrecio.addEventListener('input', calcularPrecioConDescuento);}
    if (inputDescuento) {inputDescuento.addEventListener('input', calcularPrecioConDescuento);}
}

/**
 * Función para confirmar eliminación de productos
 */
function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        window.location.href = 'index.php?page=productos&action=eliminar&id=' + id;
    }
}

/**
 * Función para filtrar productos dinámicamente
 */
function filtrarProductos() {
    const tipo = document.getElementById('tipo').value;
    const busqueda = document.getElementById('buscar').value;
    
    // Construir URL con filtros
    let url = 'index.php?page=productos';
    if (tipo) url += '&tipo=' + encodeURIComponent(tipo);
    if (busqueda) url += '&buscar=' + encodeURIComponent(busqueda);
    
    window.location.href = url;
}

/**
 * Función para limpiar filtros
 */
function limpiarFiltros() {
    window.location.href = 'index.php?page=productos';
}

/**
 * Función para exportar productos
 */
function exportarProductos() {
    const tipo = document.getElementById('tipo').value;
    const busqueda = document.getElementById('buscar').value;
    
    let url = 'exportar_productos.php?';
    if (tipo) url += 'tipo=' + encodeURIComponent(tipo);
    if (busqueda) url += '&buscar=' + encodeURIComponent(busqueda);
    
    window.open(url, '_blank');
}

/**
 * Función para mostrar detalles del producto en modal
 */
function mostrarDetallesProducto(id) {
    // Aquí se podría hacer una petición AJAX para obtener los detalles
    // Por ahora redirigimos a la página de detalles
    window.location.href = 'index.php?page=productos&action=ver&id=' + id;
}

/**
 * Función para editar producto
 */
function editarProducto(id) {
    window.location.href = 'index.php?page=productos&action=editar&id=' + id;
}

/**
 * Función para validar formulario de producto
 */
function validarFormularioProducto() {
    const nombre = document.getElementById('nombre').value.trim();
    const tipo = document.getElementById('tipo_producto').value;
    const precio = document.getElementById('precio').value;
    const descripcion = document.getElementById('descripcion').value.trim();
    
    if (!nombre) {
        alert('El nombre del producto es obligatorio');
        return false;
    }
    
    if (!tipo) {
        alert('Debe seleccionar un tipo de producto');
        return false;
    }
    
    if (!precio || isNaN(precio) || parseFloat(precio) <= 0) {
        alert('El precio debe ser un número válido mayor a 0');
        return false;
    }
    
    if (!descripcion) {
        alert('La descripción es obligatoria');
        return false;
    }
    
    return true;
}

/**
 * Función para previsualizar imagen
 */
function previsualizarImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-imagen').src = e.target.result;
            document.getElementById('preview-imagen').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Función para calcular precio con descuento
 */
function calcularPrecioConDescuento() {
    const precio = parseFloat(document.getElementById('precio').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const precioFinal = precio - (precio * descuento / 100);
    
    document.getElementById('precio_final').value = precioFinal.toFixed(2);
    document.getElementById('precio_final_display').textContent = '$' + precioFinal.toFixed(2);
}

// ========================================
// FUNCIONALIDADES DE CLIENTES
// ========================================

/**
 * Inicializa funcionalidades específicas de clientes
 */
function initializeClientes() {
    console.log('Inicializando funcionalidades de clientes');
}

// ========================================
// FUNCIONES COMPATIBILIDAD (mantener para compatibilidad)
// ========================================

/**
 * Agrega un nuevo producto al formulario (compatibilidad)
 */
function agregarProducto() {
    const container = document.getElementById('productosContainer');
    const productoItems = container.querySelectorAll('.producto-item');
    const newIndex = productoItems.length;
    
    const newProductoItem = document.createElement('div');
    newProductoItem.className = 'row producto-item mb-2';
    newProductoItem.innerHTML = `
        <div class="col-md-4">
            <select class="form-select producto-select" name="productos[${newIndex}][id]" required>
                <option value="">Seleccionar producto</option>
                ${getProductosOptions()}
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control cantidad-input" 
                   name="productos[${newIndex}][cantidad]" placeholder="Cantidad" 
                   min="1" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control precio-input" 
                   name="productos[${newIndex}][precio]" placeholder="Precio" 
                   step="0.01" readonly>
        </div>
        <div class="col-md-2">
            <span class="form-control subtotal-display">$00</span>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-producto">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newProductoItem);
    
    // Agregar event listeners al nuevo elemento
    const newSelect = newProductoItem.querySelector('.producto-select');
    const newCantidad = newProductoItem.querySelector('.cantidad-input');
    const newRemoveBtn = newProductoItem.querySelector('.remove-producto');
    
    newSelect.addEventListener('change', calcularTotales);
    newCantidad.addEventListener('input', calcularTotales);
    newRemoveBtn.addEventListener('click', removeProducto);
}

/**
 * Remueve un producto del formulario (compatibilidad)
 */
function removeProducto() {
    this.closest('.producto-item').remove();
    calcularTotales();
}

/**
 * Calcula los totales del formulario (compatibilidad)
 */
function calcularTotales() {
    let subtotal = 0;
    const productoItems = document.querySelectorAll('.producto-item');
    productoItems.forEach(item => {
        const select = item.querySelector('.producto-select');
        const cantidad = item.querySelector('.cantidad-input');
        const precio = item.querySelector('.precio-input');
        const subtotalDisplay = item.querySelector('.subtotal-display');
        
        if (select.value && cantidad.value && precio.value) {
            const cantidadVal = parseFloat(cantidad.value);
            const precioVal = parseFloat(precio.value);
            const itemSubtotal = cantidadVal * precioVal;
            
            subtotalDisplay.textContent = `$${itemSubtotal.toFixed(2)}`;
            subtotal += itemSubtotal;
        }
    });
    
    const iva = subtotal * 0.16;
    const total = subtotal + iva;
    
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('iva').textContent = `$${iva.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
}

/**
 * Obtiene las opciones de productos para el select (compatibilidad)
 */
function getProductosOptions() {
    // Esta función debería obtener las opciones de productos desde el servidor
    // Por ahora retornamos opciones de ejemplo
    return `
        <option value="1">Producto 1</option>
        <option value="2">Producto 2</option>
        <option value="3">Producto 3</option>
    `;
}

// ========================================
// EXPOSICIÓN DE FUNCIONES GLOBALES
// ========================================

/**
 * Expone las funciones principales para uso global
 * Permite que otras partes del código accedan a estas funciones
 */
console.log('🌐 Exponiendo funciones globales...');

// Funciones de notificación y confirmación
window.showNotification = showNotification;           // Mostrar notificaciones
window.confirmAction = confirmAction;                 // Confirmar acciones

// Funciones de ventas
window.agregarProductoVenta = agregarProductoVenta;   // Agregar producto (compatibilidad)
window.removerProducto = removerProducto;             // Remover producto (compatibilidad)

// Funciones de productos
window.confirmarEliminar = confirmarEliminar;         // Confirmar eliminación
window.filtrarProductos = filtrarProductos;           // Filtrar productos
window.limpiarFiltros = limpiarFiltros;               // Limpiar filtros
window.exportarProductos = exportarProductos;         // Exportar productos
window.mostrarDetallesProducto = mostrarDetallesProducto; // Ver detalles
window.editarProducto = editarProducto;               // Editar producto
window.validarFormularioProducto = validarFormularioProducto; // Validar formulario
window.previsualizarImagen = previsualizarImagen;     // Previsualizar imagen
window.calcularPrecioConDescuento = calcularPrecioConDescuento; // Calcular precio

console.log('✅ Funciones globales expuestas correctamente');
console.log('🎉 Sistema de Vendedor completamente cargado y listo para usar'); 