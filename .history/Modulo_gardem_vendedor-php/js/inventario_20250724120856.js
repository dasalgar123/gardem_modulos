// ========================================
// FUNCIONES DE EXPORTACIÓN - INVENTARIO VENDEDOR
// ========================================

// Función para exportar a Excel
function exportToExcel() {
    const table = document.getElementById('inventarioTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Inventario" });
    XLSX.writeFile(wb, 'inventario_vendedor.xlsx');
}

// Función para exportar a PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    const table = document.getElementById('inventarioTable');
    const headers = [];
    const data = [];
    
    // Obtener headers
    const headerRow = table.querySelector('thead tr');
    headerRow.querySelectorAll('th').forEach(th => {
        headers.push(th.textContent);
    });
    
    // Obtener datos
    table.querySelectorAll('tbody tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach(td => {
            // Extraer solo el texto, sin los badges
            const text = td.textContent.trim();
            rowData.push(text);
        });
        if (rowData.length > 0) {
            data.push(rowData);
        }
    });
    
    // Crear tabla en PDF
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 20,
        styles: {
            fontSize: 8,
            cellPadding: 2
        },
        headStyles: {
            fillColor: [102, 126, 234],
            textColor: 255
        }
    });
    
    doc.save('inventario_vendedor.pdf');
}

// Función para exportar a CSV
function exportToCSV() {
    const table = document.getElementById('inventarioTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            // Extraer solo el texto, sin los badges
            const text = col.textContent.trim();
            rowData.push(`"${text}"`);
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'inventario_vendedor.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Función para imprimir
function printInventario() {
    const printWindow = window.open('', '_blank');
    const table = document.getElementById('inventarioTable');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Inventario - Vendedor</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { color: #333; }
                .summary { margin-bottom: 20px; }
                .summary-item { display: inline-block; margin-right: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Inventario - Sistema de Vendedor</h1>
                <p>Fecha: ${new Date().toLocaleDateString('es-ES')}</p>
            </div>
            <div class="summary">
                <div class="summary-item">
                    <strong>Total Productos:</strong> ${document.querySelector('.summary-cards .summary-card:nth-child(1) .summary-content p').textContent}
                </div>
                <div class="summary-item">
                    <strong>Disponible:</strong> ${document.querySelector('.summary-cards .summary-card:nth-child(2) .summary-content p').textContent}
                </div>
                <div class="summary-item">
                    <strong>Stock Bajo:</strong> ${document.querySelector('.summary-cards .summary-card:nth-child(3) .summary-content p').textContent}
                </div>
                <div class="summary-item">
                    <strong>Agotado:</strong> ${document.querySelector('.summary-cards .summary-card:nth-child(4) .summary-content p').textContent}
                </div>
            </div>
            ${table.outerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Función para filtrar por estado de stock
function filtrarPorStock(estado) {
    const rows = document.querySelectorAll('#inventarioTable tbody tr');
    
    rows.forEach(row => {
        const saldoCell = row.querySelector('.saldo .badge');
        if (saldoCell) {
            const saldo = parseInt(saldoCell.textContent);
            let mostrar = true;
            
            switch(estado) {
                case 'disponible':
                    mostrar = saldo > 10;
                    break;
                case 'bajo':
                    mostrar = saldo <= 10 && saldo > 0;
                    break;
                case 'agotado':
                    mostrar = saldo <= 0;
                    break;
                default:
                    mostrar = true;
            }
            
            row.style.display = mostrar ? '' : 'none';
        }
    });
}

// Función para buscar productos
function buscarProducto(termino) {
    const rows = document.querySelectorAll('#inventarioTable tbody tr');
    const terminoLower = termino.toLowerCase();
    
    rows.forEach(row => {
        const productoCell = row.querySelector('.product-name strong');
        if (productoCell) {
            const nombreProducto = productoCell.textContent.toLowerCase();
            const mostrar = nombreProducto.includes(terminoLower);
            row.style.display = mostrar ? '' : 'none';
        }
    });
}

// Event listeners cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Filtro por producto
    const inputProducto = document.getElementById('producto');
    if (inputProducto) {
        inputProducto.addEventListener('input', function() {
            buscarProducto(this.value);
        });
    }
    
    // Filtro por stock
    const selectStock = document.getElementById('stock');
    if (selectStock) {
        selectStock.addEventListener('change', function() {
            filtrarPorStock(this.value);
        });
    }
    
    // Botón limpiar filtros
    const btnLimpiar = document.querySelector('a[href="?page=inventario"]');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function(e) {
            e.preventDefault();
            if (inputProducto) inputProducto.value = '';
            if (selectStock) selectStock.value = '';
            buscarProducto('');
            filtrarPorStock('');
        });
    }
});

// Función para actualizar contadores en tiempo real
function actualizarContadores() {
    const rows = document.querySelectorAll('#inventarioTable tbody tr:not([style*="display: none"])');
    const totalProductos = rows.length;
    
    let disponibles = 0;
    let stockBajo = 0;
    let agotados = 0;
    
    rows.forEach(row => {
        const saldoCell = row.querySelector('.saldo .badge');
        if (saldoCell) {
            const saldo = parseInt(saldoCell.textContent);
            if (saldo > 10) disponibles++;
            else if (saldo > 0) stockBajo++;
            else agotados++;
        }
    });
    
    // Actualizar contadores en las tarjetas
    const contadores = document.querySelectorAll('.summary-content p');
    if (contadores.length >= 4) {
        contadores[0].textContent = totalProductos;
        contadores[1].textContent = disponibles;
        contadores[2].textContent = stockBajo;
        contadores[3].textContent = agotados;
    }
} 