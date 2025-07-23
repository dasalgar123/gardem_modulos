-- Script para arreglar las columnas faltantes en productos_salidas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar estructura actual
DESCRIBE productos_salidas;

-- 2. Agregar columnas talla_id y color_id si no existen
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS talla_id INT NULL;
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS color_id INT NULL;

-- 3. Verificar que se agregaron correctamente
DESCRIBE productos_salidas;

-- 4. Ver registros actuales
SELECT 
    ps.id,
    ps.producto_id,
    p.nombre as producto_nombre,
    ps.cantidad,
    ps.motivo,
    ps.fecha,
    ps.destinatario_tipo,
    ps.destinatario_id,
    ps.cliente_id,
    ps.factura_remision
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
ORDER BY ps.fecha DESC
LIMIT 10;

-- 5. Probar consulta con talla y color (debería funcionar ahora)
SELECT 
    ps.*,
    p.nombre as producto_nombre,
    t.nombre as talla_nombre,
    c.nombre as color_nombre
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
LEFT JOIN tallas t ON ps.talla_id = t.id
LEFT JOIN colores c ON ps.color_id = c.id
ORDER BY ps.fecha DESC
LIMIT 5; 