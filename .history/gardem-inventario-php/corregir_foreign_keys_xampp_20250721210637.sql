-- Script específico para XAMPP - Corregir Foreign Keys
-- Ejecuta esto en phpMyAdmin en tu base de datos gardelcatalogo

-- ========================================
-- 1. VERIFICAR ESTRUCTURA ACTUAL
-- ========================================

-- Verificar si existen las foreign keys
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_salidas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ========================================
-- 2. ELIMINAR FOREIGN KEYS EXISTENTES (si las hay)
-- ========================================

-- Eliminar foreign keys existentes de productos_salidas
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_producto;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_cliente;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_talla;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_color;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_cliente_salida;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_venta_salida;

-- ========================================
-- 3. AGREGAR FOREIGN KEYS CORRECTAS
-- ========================================

-- Foreign key para producto_id
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_producto 
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

-- Foreign key para cliente_id
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_cliente 
FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE SET NULL;

-- Foreign key para talla (relacionar con tabla tallas)
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_talla 
FOREIGN KEY (talla) REFERENCES tallas(id) ON DELETE SET NULL;

-- Foreign key para color (relacionar con tabla colores)
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_color 
FOREIGN KEY (color) REFERENCES colores(id) ON DELETE SET NULL;

-- ========================================
-- 4. HACER LO MISMO PARA productos_entradas
-- ========================================

-- Eliminar foreign keys existentes de productos_entradas
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_producto;
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_proveedor;
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_talla;
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_color;

-- Agregar foreign keys para productos_entradas
ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_producto 
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_proveedor 
FOREIGN KEY (proveedor_id) REFERENCES proveedor(id) ON DELETE SET NULL;

-- Si productos_entradas tiene columnas talla y color
ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_talla 
FOREIGN KEY (talla) REFERENCES tallas(id) ON DELETE SET NULL;

ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_color 
FOREIGN KEY (color) REFERENCES colores(id) ON DELETE SET NULL;

-- ========================================
-- 5. VERIFICAR QUE SE AGREGARON CORRECTAMENTE
-- ========================================

-- Verificar foreign keys en productos_salidas
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_salidas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Verificar foreign keys en productos_entradas
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_entradas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ========================================
-- 6. VERIFICAR DATOS HUÉRFANOS
-- ========================================

-- Verificar datos huérfanos en productos_salidas
SELECT 'productos_salidas con talla inexistente' as problema, COUNT(*) as cantidad
FROM productos_salidas ps
LEFT JOIN tallas t ON ps.talla = t.id
WHERE ps.talla IS NOT NULL AND t.id IS NULL
UNION ALL
SELECT 'productos_salidas con color inexistente' as problema, COUNT(*) as cantidad
FROM productos_salidas ps
LEFT JOIN colores c ON ps.color = c.id
WHERE ps.color IS NOT NULL AND c.id IS NULL;

-- ========================================
-- 7. LIMPIAR DATOS HUÉRFANOS (si los hay)
-- ========================================

-- Establecer talla NULL si no existe
UPDATE productos_salidas ps
LEFT JOIN tallas t ON ps.talla = t.id
SET ps.talla = NULL
WHERE ps.talla IS NOT NULL AND t.id IS NULL;

-- Establecer color NULL si no existe
UPDATE productos_salidas ps
LEFT JOIN colores c ON ps.color = c.id
SET ps.color = NULL
WHERE ps.color IS NOT NULL AND c.id IS NULL;

-- ========================================
-- 8. VERIFICAR FINAL
-- ========================================

SELECT '✅ Foreign keys corregidas exitosamente' as estado; 