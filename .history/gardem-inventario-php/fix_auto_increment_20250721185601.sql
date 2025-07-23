-- Script para arreglar el autoincrement en la tabla productos_entradas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- Opción 1: Intentar modificar la columna existente
ALTER TABLE productos_entradas MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY;

-- Si la opción 1 falla, usar la opción 2:
-- Opción 2: Recrear la tabla con la estructura correcta

-- Primero, hacer backup de los datos existentes
CREATE TABLE productos_entradas_backup AS SELECT * FROM productos_entradas;

-- Eliminar la tabla actual
DROP TABLE productos_entradas;

-- Crear la tabla con autoincrement correcto
CREATE TABLE productos_entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    bodega_id INT NULL,
    cantidad INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(100) NULL,
    beneficiario_tipo ENUM('proveedor', 'cliente', 'interno') NULL,
    beneficiario_id INT NULL,
    factura_remision VARCHAR(50) NULL,
    beneficiario VARCHAR(100) NULL,
    INDEX idx_producto (producto_id),
    INDEX idx_fecha (fecha)
);

-- Restaurar los datos (opcional - solo si quieres mantener los datos existentes)
-- INSERT INTO productos_entradas (producto_id, bodega_id, cantidad, fecha, motivo, beneficiario_tipo, beneficiario_id, factura_remision, beneficiario)
-- SELECT producto_id, bodega_id, cantidad, fecha, motivo, beneficiario_tipo, beneficiario_id, factura_remision, beneficiario
-- FROM productos_entradas_backup;

-- Verificar que funciona
SELECT * FROM productos_entradas LIMIT 5; 