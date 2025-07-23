-- Script para crear las tablas necesarias para el sistema de ventas
-- Base de datos: gardelcatalogo

-- 1. Verificar si existe la tabla de clientes (puede ser 'cliente' o 'clientes')
CREATE TABLE IF NOT EXISTS `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Verificar si existe la tabla de colores
CREATE TABLE IF NOT EXISTS `colores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `codigo_hex` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Verificar si existe la tabla de tallas
CREATE TABLE IF NOT EXISTS `tallas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `categoria` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Verificar si existe la tabla de inventario_bodega
CREATE TABLE IF NOT EXISTS `inventario_bodega` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lote` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producto_id` (`producto_id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Crear tabla de ventas (productos_ventas) si no existe
CREATE TABLE IF NOT EXISTS `productos_ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `factura` varchar(50) NOT NULL,
  `productos` text NOT NULL COMMENT 'Descripción de productos vendidos',
  `total` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_factura` (`factura`),
  KEY `idx_fecha` (`fecha`),
  FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Crear tabla detallada de ventas (productos_venta_detalle) para mejor control
CREATE TABLE IF NOT EXISTS `productos_venta_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venta_id` (`venta_id`),
  KEY `idx_producto_id` (`producto_id`),
  FOREIGN KEY (`venta_id`) REFERENCES `productos_ventas` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Insertar datos de ejemplo para colores si no existen
INSERT IGNORE INTO `colores` (`id`, `nombre`, `codigo_hex`) VALUES
(1, 'Rojo', '#FF0000'),
(2, 'Azul', '#0000FF'),
(3, 'Verde', '#00FF00'),
(4, 'Negro', '#000000'),
(5, 'Blanco', '#FFFFFF'),
(6, 'Amarillo', '#FFFF00'),
(7, 'Rosa', '#FFC0CB'),
(8, 'Morado', '#800080'),
(9, 'Naranja', '#FFA500'),
(10, 'Gris', '#808080'),
(11, 'Café', '#A52A2A'),
(12, 'Celeste', '#87CEEB'),
(13, 'Beige', '#F5F5DC'),
(14, 'Turquesa', '#40E0D0');

-- 8. Insertar datos de ejemplo para tallas si no existen
INSERT IGNORE INTO `tallas` (`id`, `nombre`, `categoria`) VALUES
(1, 'XS', 'adulto'),
(2, 'S', 'adulto'),
(3, 'M', 'adulto'),
(4, 'L', 'adulto'),
(5, 'XL', 'adulto'),
(6, 'XXL', 'adulto'),
(7, '2', 'niño'),
(8, '4', 'niño'),
(9, '6', 'niño'),
(10, '8', 'niño'),
(11, '10', 'niño'),
(12, '12', 'niño'),
(13, '14', 'niño'),
(14, '16', 'niño'),
(15, '2T', 'niña'),
(16, '4T', 'niña'),
(17, '6T', 'niña');

-- 9. Insertar clientes de ejemplo si no existen
INSERT IGNORE INTO `cliente` (`id`, `nombre`, `telefono`, `correo`) VALUES
(1, 'Cliente General', '000-000-0000', 'general@ejemplo.com'),
(2, 'María García', '123-456-7890', 'maria@ejemplo.com'),
(3, 'Juan Pérez', '098-765-4321', 'juan@ejemplo.com');

-- 10. Insertar stock de ejemplo para productos existentes
INSERT IGNORE INTO `inventario_bodega` (`producto_id`, `stock_actual`, `lote`) 
SELECT id, 50, CONCAT('LOTE-', id) FROM productos WHERE id NOT IN (SELECT DISTINCT producto_id FROM inventario_bodega);

-- 11. Mostrar resumen de lo creado
SELECT 'Tablas creadas correctamente' as mensaje;
SELECT 'productos' as tabla, COUNT(*) as registros FROM productos
UNION ALL
SELECT 'colores' as tabla, COUNT(*) as registros FROM colores
UNION ALL
SELECT 'tallas' as tabla, COUNT(*) as registros FROM tallas
UNION ALL
SELECT 'cliente' as tabla, COUNT(*) as registros FROM cliente
UNION ALL
SELECT 'inventario_bodega' as tabla, COUNT(*) as registros FROM inventario_bodega
UNION ALL
SELECT 'productos_ventas' as tabla, COUNT(*) as registros FROM productos_ventas; 