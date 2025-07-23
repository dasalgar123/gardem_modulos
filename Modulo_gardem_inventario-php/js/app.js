// ========================================
// SISTEMA DE ALMACENISTA - JAVASCRIPT PRINCIPAL
// ========================================

// Variables globales
let contadorProductos = 0;

// ========================================
// FUNCIONES GENERALES
// ========================================

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo = 'info') {
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
    alerta.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insertar al inicio del contenedor principal
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alerta, container.firstChild);
    }
}

// Función para confirmar acciones
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}

// Función para formatear números
function formatearNumero(numero, decimales = 2) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    }).format(numero);
}

// ========================================
// FUNCIONES DE ENTRADAS
// ========================================

function agregarProducto() {
    contadorProductos++;
    const container = document.getElementById('productosContainer');
    
    if (!container) return; // Si no estamos en la página de entradas
    
    const productoHtml = `
        <div class="row mb-2 producto-row" data-producto-id="${contadorProductos}">
            <div class="col-md-3">
                <select class="form-select" name="productos[${contadorProductos}][producto_id]" required>
                    <option value="">Seleccionar producto...</option>
                    ${getProductosOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][color_id]">
                    <option value="">Color...</option>
                    ${getColoresOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][talla_id]">
                    <option value="">Talla...</option>
                    ${getTallasOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="productos[${contadorProductos}][cantidad]" 
                       placeholder="Cantidad" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="productos[${contadorProductos}][precio_unitario]" 
                       placeholder="Precio" step="0.01" min="0" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerProducto(${contadorProductos})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', productoHtml);
}

function removerProducto(productoId) {
    const elemento = document.querySelector(`[data-producto-id="${productoId}"]`);
    if (elemento) {
        elemento.remove();
    }
}

function verDetalleEntrada(entradaId) {
    mostrarAlerta('Función de detalle en desarrollo para entrada ID: ' + entradaId, 'info');
}

function confirmarEntrada(entradaId) {
    if (confirmarAccion('¿Está seguro de que desea confirmar esta entrada?')) {
        mostrarAlerta('Función de confirmación en desarrollo para entrada ID: ' + entradaId, 'success');
    }
}

function anularEntrada(entradaId) {
    if (confirmarAccion('¿Está seguro de que desea anular esta entrada?')) {
        mostrarAlerta('Función de anulación en desarrollo para entrada ID: ' + entradaId, 'warning');
    }
}

// ========================================
// FUNCIONES DE COMPRAS
// ========================================

function agregarProductoCompra() {
    contadorProductos++;
    const container = document.getElementById('productosContainer');
    
    if (!container) return;
    
    const productoHtml = `
        <div class="row mb-2 producto-row" data-producto-id="${contadorProductos}">
            <div class="col-md-3">
                <select class="form-select producto-select" name="productos[${contadorProductos}][producto_id]" required onchange="cargarPrecioProducto(this, ${contadorProductos})">
                    <option value="">Seleccionar producto...</option>
                    ${getProductosOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][color_id]">
                    <option value="">Color...</option>
                    ${getColoresOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="productos[${contadorProductos}][talla_id]">
                    <option value="">Talla...</option>
                    ${getTallasOptions()}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control cantidad-input" name="productos[${contadorProductos}][cantidad]" 
                       placeholder="Cantidad" min="1" required onchange="calcularSubtotal(${contadorProductos})">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control precio-input" name="productos[${contadorProductos}][precio_unitario]" 
                       placeholder="Precio" step="0.01" min="0" required onchange="calcularSubtotal(${contadorProductos})">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerProducto(${contadorProductos})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', productoHtml);
}

function cargarPrecioProducto(select, productoId) {
    const precioInput = select.closest('.producto-row').querySelector('.precio-input');
    const precioSeleccionado = select.options[select.selectedIndex].getAttribute('data-precio');
    
    if (precioSeleccionado) {
        precioInput.value = precioSeleccionado;
        calcularSubtotal(productoId);
    }
}

function calcularSubtotal(productoId) {
    const fila = document.querySelector(`[data-producto-id="${productoId}"]`);
    const cantidad = fila.querySelector('.cantidad-input').value;
    const precio = fila.querySelector('.precio-input').value;
    
    if (cantidad && precio) {
        const subtotal = parseFloat(cantidad) * parseFloat(precio);
        // Aquí podrías mostrar el subtotal en un campo adicional si lo necesitas
    }
}

function verDetalleCompra(compraId) {
    mostrarAlerta('Función de detalle en desarrollo para compra ID: ' + compraId, 'info');
}

function confirmarCompra(compraId) {
    if (confirmarAccion('¿Está seguro de que desea confirmar esta compra?')) {
        mostrarAlerta('Función de confirmación en desarrollo para compra ID: ' + compraId, 'success');
    }
}

function cancelarCompra(compraId) {
    if (confirmarAccion('¿Está seguro de que desea cancelar esta compra?')) {
        mostrarAlerta('Función de cancelación en desarrollo para compra ID: ' + compraId, 'warning');
    }
}

// ========================================
// FUNCIONES DE PROVEEDORES
// ========================================

function editarProveedor(proveedorId) {
    mostrarAlerta('Función de edición en desarrollo para proveedor ID: ' + proveedorId, 'info');
}

function verDetalleProveedor(proveedorId) {
    mostrarAlerta('Función de detalle en desarrollo para proveedor ID: ' + proveedorId, 'info');
}

function nuevaCompra(proveedorId) {
    // Redirigir a nueva compra
    window.location.href = `index.php?page=compras&proveedor_id=${proveedorId}`;
}

function eliminarProveedor(proveedorId) {
    if (confirmarAccion('¿Está seguro de que desea eliminar este proveedor?')) {
        mostrarAlerta('Función de eliminación en desarrollo para proveedor ID: ' + proveedorId, 'warning');
    }
}

// ========================================
// FUNCIONES DE OPCIONES DINÁMICAS
// ========================================

function getProductosOptions() {
    if (typeof window.productosData !== 'undefined') {
        return window.productosData;
    }
    return '<option value="">No hay productos disponibles</option>';
}

function getColoresOptions() {
    if (typeof window.coloresData !== 'undefined') {
        return window.coloresData;
    }
    return '<option value="">No hay colores disponibles</option>';
}

function getTallasOptions() {
    if (typeof window.tallasData !== 'undefined') {
        return window.tallasData;
    }
    return '<option value="">No hay tallas disponibles</option>';
}

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de Almacenista - JavaScript cargado');
    
    // Detectar la página actual y inicializar funciones específicas
    const currentPage = window.location.search.match(/page=([^&]+)/);
    if (currentPage) {
        const page = currentPage[1];
        
        switch(page) {
            case 'entradas':
                inicializarEntradas();
                break;
            case 'compras':
                inicializarCompras();
                break;
            case 'proveedores':
                inicializarProveedores();
                break;
        }
    }
});

function inicializarEntradas() {
    agregarProducto();
    console.log('Funciones de entradas inicializadas');
}

function inicializarCompras() {
    agregarProductoCompra();
    
    // Validar que la fecha de entrega no sea anterior a hoy
    const fechaEntregaInput = document.getElementById('fecha_entrega_esperada');
    if (fechaEntregaInput) {
        fechaEntregaInput.addEventListener('change', function() {
            const fechaEntrega = new Date(this.value);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (fechaEntrega < hoy) {
                mostrarAlerta('La fecha de entrega no puede ser anterior a hoy', 'warning');
                this.value = '';
            }
        });
    }
    
    console.log('Funciones de compras inicializadas');
}

function inicializarProveedores() {
    // Validación del formulario de proveedores
    const formProveedor = document.getElementById('formNuevoProveedor');
    if (formProveedor) {
        formProveedor.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const nit = document.getElementById('nit').value.trim();
            
            if (nombre.length < 3) {
                e.preventDefault();
                mostrarAlerta('El nombre del proveedor debe tener al menos 3 caracteres.', 'warning');
                return false;
            }
            
            if (nit && !/^\d{9,10}-\d$/.test(nit)) {
                e.preventDefault();
                mostrarAlerta('El formato del NIT debe ser: 900123456-7', 'warning');
                return false;
            }
        });
    }
    
    console.log('Funciones de proveedores inicializadas');
} 