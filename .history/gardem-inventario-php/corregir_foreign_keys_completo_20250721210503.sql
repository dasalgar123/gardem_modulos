-- Script completo para corregir foreign keys en gardelcatalogo
-- Ejecuta esto en phpMyAdmin paso a paso

-- ========================================
-- 1. VERIFICAR ESTRUCTURA ACTUAL
-- ========================================

-- Verificar foreign keys existentes en productos_salidas
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'gardelcatalogo' 
AND TABLE_NAME = 'productos_salidas'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Verificar foreign keys existentes en productos_entradas
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
-- 2. ELIMINAR FOREIGN KEYS EXISTENTES (si las hay)
-- ========================================

-- Eliminar foreign keys de productos_salidas si existen
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_producto;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_cliente;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_proveedor;
ALTER TABLE productos_salidas DROP FOREIGN KEY IF EXISTS fk_salida_bodega;

-- Eliminar foreign keys de productos_entradas si existen
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_producto;
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_proveedor;
ALTER TABLE productos_entradas DROP FOREIGN KEY IF EXISTS fk_entrada_bodega;

-- ========================================
-- 3. AGREGAR FOREIGN KEYS CORRECTAS
-- ========================================

-- Foreign keys para productos_salidas
ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_producto 
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

ALTER TABLE productos_salidas 
ADD CONSTRAINT fk_salida_cliente 
FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE SET NULL;

-- Foreign keys para productos_entradas
ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_producto 
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE;

ALTER TABLE productos_entradas 
ADD CONSTRAINT fk_entrada_proveedor 
FOREIGN KEY (proveedor_id) REFERENCES proveedor(id) ON DELETE SET NULL;

-- ========================================
-- 4. VERIFICAR QUE SE AGREGARON CORRECTAMENTE
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
-- 5. VERIFICAR DATOS EXISTENTES
-- ========================================

-- Verificar que no hay datos huérfanos
SELECT 'productos_salidas con producto_id inexistente' as problema, COUNT(*) as cantidad
FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
WHERE p.id IS NULL
UNION ALL
SELECT 'productos_salidas con cliente_id inexistente' as problema, COUNT(*) as cantidad
FROM productos_salidas ps
LEFT JOIN cliente c ON ps.cliente_id = c.id
WHERE ps.cliente_id IS NOT NULL AND c.id IS NULL
UNION ALL
SELECT 'productos_entradas con producto_id inexistente' as problema, COUNT(*) as cantidad
FROM productos_entradas pe
LEFT JOIN productos p ON pe.producto_id = p.id
WHERE p.id IS NULL
UNION ALL
SELECT 'productos_entradas con proveedor_id inexistente' as problema, COUNT(*) as cantidad
FROM productos_entradas pe
LEFT JOIN proveedor pr ON pe.proveedor_id = pr.id
WHERE pe.proveedor_id IS NOT NULL AND pr.id IS NULL;

-- ========================================
-- 6. LIMPIAR DATOS HUÉRFANOS (si los hay)
-- ========================================

-- Eliminar salidas con productos inexistentes
DELETE ps FROM productos_salidas ps
LEFT JOIN productos p ON ps.producto_id = p.id
WHERE p.id IS NULL;

-- Eliminar entradas con productos inexistentes
DELETE pe FROM productos_entradas pe
LEFT JOIN productos p ON pe.producto_id = p.id
WHERE p.id IS NULL;

-- Establecer cliente_id NULL si el cliente no existe
UPDATE productos_salidas ps
LEFT JOIN cliente c ON ps.cliente_id = c.id
SET ps.cliente_id = NULL
WHERE ps.cliente_id IS NOT NULL AND c.id IS NULL;

-- Establecer proveedor_id NULL si el proveedor no existe
UPDATE productos_entradas pe
LEFT JOIN proveedor pr ON pe.proveedor_id = pr.id
SET pe.proveedor_id = NULL
WHERE pe.proveedor_id IS NOT NULL AND pr.id IS NULL;

-- ========================================
-- 7. VERIFICAR FINAL
-- ========================================

-- Verificar que todo está correcto
SELECT 'Verificación final completada' as estado; 