// ========================================
// FUNCIONES PARA INVENTARIO - MÓDULO VENDEDOR
// ========================================

// Exportar a Excel
function exportToExcel() {
    const table = document.getElementById('inventarioTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Inventario" });
    XLSX.writeFile(wb, 'inventario_vendedor.xlsx');
}

// Exportar a PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    const table = document.getElementById('inventarioTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    const headers = Array.from(rows[0].querySelectorAll('th')).map(th => th.textContent);
    const data = rows.slice(1).map(row => 
        Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim())
    );
    
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 20,
        styles: {
            fontSize: 8,
            cellPadding: 2
        }
    });
    
    doc.save('inventario_vendedor.pdf');
}

// Exportar a CSV
function exportToCSV() {
    const table = document.getElementById('inventarioTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    let csv = '';
    
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        const rowData = cells.map(cell => {
            let text = cell.textContent.trim();
            // Escapar comillas
            if (text.includes('"')) {
                text = text.replace(/"/g, '""');
            }
            return `"${text}"`;
        });
        csv += rowData.join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'inventario_vendedor.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Imprimir inventario
function printInventario() {
    window.print();
}

// Filtrar por stock
function filtrarPorStock(tipo) {
    const rows = document.querySelectorAll('#inventarioTable tbody tr');
    
    rows.forEach(row => {
        const saldoCell = row.querySelector('td:last-child');
        if (saldoCell) {
            const saldo = parseInt(saldoCell.textContent.trim());
            let mostrar = true;
            
            switch(tipo) {
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
    
    actualizarContadores();
}

// Buscar producto
function buscarProducto() {
    const input = document.getElementById('producto');
    const filtro = input.value.toLowerCase();
    const rows = document.querySelectorAll('#inventarioTable tbody tr');
    
    rows.forEach(row => {
        const nombreCell = row.querySelector('td:first-child');
        if (nombreCell) {
            const nombre = nombreCell.textContent.toLowerCase();
            row.style.display = nombre.includes(filtro) ? '' : 'none';
        }
    });
    
    actualizarContadores();
}

// Actualizar contadores
function actualizarContadores() {
    const rows = document.querySelectorAll('#inventarioTable tbody tr:not([style*="display: none"])');
    const totalProductos = rows.length;
    
    let disponibles = 0;
    let stockBajo = 0;
    let agotados = 0;
    
    rows.forEach(row => {
        const saldoCell = row.querySelector('td:last-child');
        if (saldoCell) {
            const saldo = parseInt(saldoCell.textContent.trim());
            if (saldo > 10) {
                disponibles++;
            } else if (saldo > 0) {
                stockBajo++;
            } else {
                agotados++;
            }
        }
    });
    
    // Actualizar los contadores en las tarjetas de resumen
    const totalElement = document.querySelector('.summary-card:first-child .summary-content p');
    const disponibleElement = document.querySelector('.summary-card:nth-child(2) .summary-content p');
    const bajoElement = document.querySelector('.summary-card:nth-child(3) .summary-content p');
    const agotadoElement = document.querySelector('.summary-card:nth-child(4) .summary-content p');
    
    if (totalElement) totalElement.textContent = totalProductos;
    if (disponibleElement) disponibleElement.textContent = disponibles;
    if (bajoElement) bajoElement.textContent = stockBajo;
    if (agotadoElement) agotadoElement.textContent = agotados;
}

// Event listeners cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Buscar producto en tiempo real
    const productoInput = document.getElementById('producto');
    if (productoInput) {
        productoInput.addEventListener('input', buscarProducto);
    }
    
    // Filtrar por stock
    const stockSelect = document.getElementById('stock');
    if (stockSelect) {
        stockSelect.addEventListener('change', function() {
            filtrarPorStock(this.value);
        });
    }
}); 