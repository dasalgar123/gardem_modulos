<?php
// Generador de códigos QR para inventario
require_once 'config/database.php';
require_once 'controlador/ControladorInventario.php';

// Incluir librería QR (usando CDN para simplificar)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador QR - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">
                    <i class="fas fa-qrcode text-primary me-2"></i>
                    Generador de Códigos QR - Información del Sistema
                </h1>
            </div>
        </div>

        <!-- Controles -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Configurar Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="qrType" class="form-label">Información del QR:</label>
                            <select class="form-select" id="qrType">
                                <option value="summary">Resumen del Inventario</option>
                                <option value="url">Acceso al Sistema</option>
                                <option value="product">Producto Específico</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="productSelect" style="display: none;">
                            <label for="productId" class="form-label">Seleccionar Producto:</label>
                            <select class="form-select" id="productId">
                                <option value="">Cargando productos...</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="qrSize" class="form-label">Tamaño del QR:</label>
                            <select class="form-select" id="qrSize">
                                <option value="128">128x128</option>
                                <option value="256" selected>256x256</option>
                                <option value="512">512x512</option>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary" onclick="generateQR()">
                            <i class="fas fa-qrcode me-2"></i>
                            Generar QR
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-download me-2"></i>
                            Descargar QR
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="qrContainer" class="text-center">
                            <div class="text-muted">
                                <i class="fas fa-qrcode fa-3x mb-3"></i>
                                <p>Genera un código QR para verlo aquí</p>
                            </div>
                        </div>
                        <div class="mt-3" id="downloadButtons" style="display: none;">
                            <button class="btn btn-success btn-sm me-2" onclick="downloadQR('png')">
                                <i class="fas fa-download me-1"></i>PNG
                            </button>
                            <button class="btn btn-info btn-sm me-2" onclick="downloadQR('svg')">
                                <i class="fas fa-download me-1"></i>SVG
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="printQR()">
                                <i class="fas fa-print me-1"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de productos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Productos Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="productsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Tipo</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center">Cargando productos...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cargar productos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            loadInventoryStats();
            
            // Mostrar/ocultar selector de producto según tipo
            document.getElementById('qrType').addEventListener('change', function() {
                const productSelect = document.getElementById('productSelect');
                if (this.value === 'product') {
                    productSelect.style.display = 'block';
                } else {
                    productSelect.style.display = 'none';
                }
            });
        });

        // Cargar productos
        function loadProducts() {
            fetch('get_products.php')
                .then(response => response.json())
                .then(data => {
                    window.productsData = data; // Guardar datos globalmente
                    updateProductsTable(data);
                    updateProductSelect(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Cargar estadísticas del inventario
        function loadInventoryStats() {
            fetch('get_inventory_stats.php')
                .then(response => response.json())
                .then(data => {
                    window.inventoryStats = data; // Guardar estadísticas globalmente
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Actualizar tabla de productos
        function updateProductsTable(products) {
            const tbody = document.querySelector('#productsTable tbody');
            tbody.innerHTML = '';
            
            products.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.id}</td>
                    <td>${product.nombre}</td>
                    <td><span class="badge bg-info">${product.tipo_producto}</span></td>
                    <td>$${parseFloat(product.precio).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="generateProductQR(${product.id})">
                            <i class="fas fa-qrcode"></i> QR
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Actualizar selector de productos
        function updateProductSelect(products) {
            const select = document.getElementById('productId');
            select.innerHTML = '<option value="">Seleccionar producto...</option>';
            
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.nombre} (${product.tipo_producto})`;
                select.appendChild(option);
            });
        }

        // Generar QR
        function generateQR() {
            const type = document.getElementById('qrType').value;
            const size = document.getElementById('qrSize').value;
            let data = '';

            switch(type) {
                case 'url':
                    data = `GARDEM - Sistema de Vendedor\nAcceso: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php\nInventario: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php?page=inventario`;
                    break;
                case 'product':
                    const productId = document.getElementById('productId').value;
                    if (!productId) {
                        alert('Selecciona un producto');
                        return;
                    }
                    // Obtener información del producto seleccionado
                    const selectedProduct = window.productsData.find(p => p.id == productId);
                    if (selectedProduct) {
                        data = `GARDEM - Inventario\nProducto: ${selectedProduct.nombre}\nTipo: ${selectedProduct.tipo_producto}\nPrecio: $${parseFloat(selectedProduct.precio).toFixed(2)}\nURL: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php?page=inventario&product=${productId}`;
                    } else {
                        data = `Producto ID: ${productId}\nURL: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php?page=inventario&product=${productId}`;
                    }
                    break;
                case 'summary':
                    const stats = window.inventoryStats || {};
                    data = `GARDEM - Información del Inventario\n\nFecha: ${new Date().toLocaleDateString()}\nTotal Productos: ${stats.total_productos_db || 'N/A'}\nStock Total: ${stats.stock_total_db || 'N/A'}\nDisponibles: ${stats.productos_disponibles || 'N/A'}\nStock Bajo: ${stats.productos_stock_bajo || 'N/A'}\nAgotados: ${stats.productos_agotados || 'N/A'}\n\nSistema: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php`;
                    break;
            }

            generateQRCode(data, size);
        }

        // Generar QR para producto específico
        function generateProductQR(productId) {
            const size = document.getElementById('qrSize').value;
            const selectedProduct = window.productsData.find(p => p.id == productId);
            let data = '';
            
            if (selectedProduct) {
                data = `GARDEM - Inventario\nProducto: ${selectedProduct.nombre}\nTipo: ${selectedProduct.tipo_producto}\nPrecio: $${parseFloat(selectedProduct.precio).toFixed(2)}\nURL: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php?page=inventario&product=${productId}`;
            } else {
                data = `Producto ID: ${productId}\nURL: ${window.location.origin}/gardem/Modulo_gardem_vendedor-php/vista/index.php?page=inventario&product=${productId}`;
            }
            
            generateQRCode(data, size);
        }

        // Generar código QR
        function generateQRCode(data, size) {
            const container = document.getElementById('qrContainer');
            container.innerHTML = '';
            
            QRCode.toCanvas(container, data, {
                width: parseInt(size),
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function(error) {
                if (error) {
                    console.error(error);
                    container.innerHTML = '<div class="text-danger">Error al generar QR</div>';
                } else {
                    document.getElementById('downloadButtons').style.display = 'block';
                }
            });
        }

        // Descargar QR
        function downloadQR(format) {
            const canvas = document.querySelector('#qrContainer canvas');
            if (!canvas) return;

            if (format === 'png') {
                const link = document.createElement('a');
                link.download = 'inventario_qr.png';
                link.href = canvas.toDataURL();
                link.click();
            } else if (format === 'svg') {
                // Convertir canvas a SVG (simplificado)
                const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${canvas.width}" height="${canvas.height}">
                    <image href="${canvas.toDataURL()}" width="100%" height="100%"/>
                </svg>`;
                
                const blob = new Blob([svg], {type: 'image/svg+xml'});
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = 'inventario_qr.svg';
                link.href = url;
                link.click();
                URL.revokeObjectURL(url);
            }
        }

        // Imprimir QR
        function printQR() {
            const canvas = document.querySelector('#qrContainer canvas');
            if (!canvas) return;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Código QR - Inventario</title>
                        <style>
                            body { text-align: center; padding: 20px; }
                            img { max-width: 100%; height: auto; }
                        </style>
                    </head>
                    <body>
                        <h2>Código QR - Inventario GARDEM</h2>
                        <img src="${canvas.toDataURL()}" alt="Código QR">
                        <p>Fecha: ${new Date().toLocaleDateString()}</p>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html> 