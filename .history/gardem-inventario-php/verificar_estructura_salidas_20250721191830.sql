-- Script para verificar y actualizar la estructura de productos_salidas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar estructura actual
SHOW CREATE TABLE productos_salidas;

-- 2. Agregar columnas talla_id y color_id si no existen
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS talla_id INT NULL;
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS color_id INT NULL;

-- 3. Agregar foreign keys si no existen
-- ALTER TABLE productos_salidas ADD CONSTRAINT fk_salida_talla FOREIGN KEY (talla_id) REFERENCES tallas(id) ON DELETE SET NULL;
-- ALTER TABLE productos_salidas ADD CONSTRAINT fk_salida_color FOREIGN KEY (color_id) REFERENCES colores(id) ON DELETE SET NULL;

-- 4. Verificar estructura final
DESCRIBE productos_salidas;

-- 5. Ver datos actuales
SELECT 
    ps.id,
    ps.producto_id,
    p.nombre as producto_nombre,
    ps.talla_id,
    t.nombre as talla_nombre,
    ps.color_id,
    c.nombre as color_nombre,
    ps.cantidad,
    ps.motivo,
    ps.fecha
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
LEFT JOIN tallas t ON ps.talla_id = t.id
LEFT JOIN colores c ON ps.color_id = c.id
ORDER BY ps.fecha DESC
LIMIT 10; 