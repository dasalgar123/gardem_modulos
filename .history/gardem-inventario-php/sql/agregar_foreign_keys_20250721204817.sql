-- Script para agregar foreign keys a productos_salidas
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- 1. Verificar foreign keys existentes
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_salidas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 2. Agregar foreign key para producto_id
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_producto 
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

-- 3. Agregar foreign key para cliente_id
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_cliente 
FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE SET NULL;

-- 4. Verificar que se agregaron correctamente
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_salidas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 5. Probar inserción con foreign key
-- INSERT INTO productos_salidas (producto_id, cantidad, motivo, destinatario_tipo, cliente_id) 
-- VALUES (1, 5, 'venta', 'cliente', 1); 