-- Script para verificar y arreglar la tabla inventario_bodega
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar estructura actual
SHOW CREATE TABLE inventario_bodega;

-- 2. Verificar si hay registros con bodega_id inválidos
SELECT ib.*, b.nombre as bodega_nombre 
FROM inventario_bodega ib 
LEFT JOIN bodega b ON ib.bodega_id = b.id 
WHERE ib.bodega_id IS NOT NULL AND b.id IS NULL;

-- 3. Si hay registros inválidos, limpiarlos
UPDATE inventario_bodega SET bodega_id = NULL WHERE bodega_id NOT IN (SELECT id FROM bodega);

-- 4. Verificar que la tabla tenga la estructura correcta
-- Si no tiene bodega_id, agregarlo:
ALTER TABLE inventario_bodega ADD COLUMN IF NOT EXISTS bodega_id INT NULL;

-- 5. Agregar foreign key si no existe
-- Primero eliminar si existe:
-- ALTER TABLE inventario_bodega DROP FOREIGN KEY IF EXISTS inventario_bodega_ibfk_2;

-- Luego agregar:
-- ALTER TABLE inventario_bodega ADD CONSTRAINT inventario_bodega_ibfk_2 
-- FOREIGN KEY (bodega_id) REFERENCES bodega(id) ON DELETE SET NULL;

-- 6. Verificar registros actuales
SELECT 
    ib.id,
    ib.producto_id,
    p.nombre as producto_nombre,
    ib.bodega_id,
    b.nombre as bodega_nombre,
    ib.stock_actual
FROM inventario_bodega ib
LEFT JOIN productos p ON ib.producto_id = p.id
LEFT JOIN bodega b ON ib.bodega_id = b.id
ORDER BY ib.id DESC
LIMIT 10; 