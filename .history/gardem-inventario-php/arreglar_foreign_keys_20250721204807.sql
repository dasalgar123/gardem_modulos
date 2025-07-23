-- Script para arreglar foreign keys y estructura de productos_salidas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar estructura actual
DESCRIBE productos_salidas;

-- 2. Ver datos actuales problemáticos
SELECT 
    ps.id,
    ps.producto_id,
    ps.cantidad,
    ps.talla,
    ps.color,
    t.nombre as talla_nombre,
    c.nombre as color_nombre
FROM productos_salidas ps
LEFT JOIN tallas t ON ps.talla = t.id
LEFT JOIN colores c ON ps.color = c.id
ORDER BY ps.fecha DESC;

-- 3. Actualizar datos existentes (convertir IDs a nombres)
UPDATE productos_salidas ps
LEFT JOIN tallas t ON ps.talla = t.id
LEFT JOIN colores c ON ps.color = c.id
SET 
    ps.talla = t.nombre,
    ps.color = c.nombre
WHERE ps.talla IS NOT NULL OR ps.color IS NOT NULL;

-- 4. Verificar que se actualizaron correctamente
SELECT 
    ps.id,
    ps.producto_id,
    ps.cantidad,
    ps.talla,
    ps.color
FROM productos_salidas ps
ORDER BY ps.fecha DESC;

-- 5. Agregar foreign keys si no existen
-- Primero eliminar si existen
-- ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_producto;
-- ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_cliente;

-- Luego agregar las foreign keys
-- ALTER TABLE productos_salidas ADD CONSTRAINT fk_salida_producto 
-- FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

-- ALTER TABLE productos_salidas ADD CONSTRAINT fk_salida_cliente 
-- FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE SET NULL;

-- 6. Verificar estructura final
SHOW CREATE TABLE productos_salidas;

-- 7. Probar consulta final
SELECT 
    ps.*,
    p.nombre as producto_nombre
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
ORDER BY ps.fecha DESC
LIMIT 10; 