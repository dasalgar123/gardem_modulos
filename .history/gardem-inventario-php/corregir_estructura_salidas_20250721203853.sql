-- Script para corregir la estructura de productos_salidas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar estructura actual
DESCRIBE productos_salidas;

-- 2. Renombrar columnas existentes (si existen)
-- ALTER TABLE productos_salidas CHANGE COLUMN talla talla_old VARCHAR(50) NULL;
-- ALTER TABLE productos_salidas CHANGE COLUMN color color_old VARCHAR(50) NULL;

-- 3. Agregar columnas correctas con IDs
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS talla_id INT NULL;
ALTER TABLE productos_salidas ADD COLUMN IF NOT EXISTS color_id INT NULL;

-- 4. Verificar estructura final
DESCRIBE productos_salidas;

-- 5. Ver datos actuales
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
    ps.factura_remision,
    ps.talla,
    ps.color,
    ps.talla_id,
    ps.color_id
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
ORDER BY ps.fecha DESC
LIMIT 5;

-- 6. Probar consulta corregida
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