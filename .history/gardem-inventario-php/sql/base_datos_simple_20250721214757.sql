-- ========================================
-- BASE DE DATOS SIMPLE PARA ALMACÉN
-- ========================================
-- Esta base de datos es LIMPIA y CLARA
-- Solo lo necesario para inventario

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS gardelcatalogo_simple;
USE gardelcatalogo_simple;

-- ========================================
-- 1. TABLA DE USUARIOS (Solo almacenistas)
-- ========================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('almacenista', 'admin') DEFAULT 'almacenista',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- 2. TABLA DE CATEGORÍAS (Simple)
-- ========================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- ========================================
-- 3. TABLA DE PRODUCTOS (Simple)
-- ========================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) DEFAULT 0.00,
    categoria_id INT,
    stock_minimo INT DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- ========================================
-- 4. TABLA DE TALLAS (Simple)
-- ========================================
CREATE TABLE tallas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) NOT NULL UNIQUE
);

-- ========================================
-- 5. TABLA DE COLORES (Simple)
-- ========================================
CREATE TABLE colores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- ========================================
-- 6. TABLA DE BODEGAS (Simple)
-- ========================================
CREATE TABLE bodegas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(150)
);

-- ========================================
-- 7. TABLA DE PROVEEDORES (Simple)
-- ========================================
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100)
);

-- ========================================
-- 8. TABLA DE CLIENTES (Simple)
-- ========================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100)
);

-- ========================================
-- 9. TABLA DE INVENTARIO (PRINCIPAL)
-- ========================================
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    talla_id INT,
    color_id INT,
    bodega_id INT,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (talla_id) REFERENCES tallas(id) ON DELETE SET NULL,
    FOREIGN KEY (color_id) REFERENCES colores(id) ON DELETE SET NULL,
    FOREIGN KEY (bodega_id) REFERENCES bodegas(id) ON DELETE SET NULL,
    UNIQUE KEY unique_producto_talla_color_bodega (producto_id, talla_id, color_id, bodega_id)
);

-- ========================================
-- 10. TABLA DE ENTRADAS (Simple)
-- ========================================
CREATE TABLE entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    talla_id INT,
    color_id INT,
    bodega_id INT,
    cantidad INT NOT NULL,
    proveedor_id INT,
    motivo ENUM('compra', 'devolucion', 'ajuste', 'traslado') DEFAULT 'compra',
    factura_remision VARCHAR(50),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (talla_id) REFERENCES tallas(id) ON DELETE SET NULL,
    FOREIGN KEY (color_id) REFERENCES colores(id) ON DELETE SET NULL,
    FOREIGN KEY (bodega_id) REFERENCES bodegas(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ========================================
-- 11. TABLA DE SALIDAS (Simple)
-- ========================================
CREATE TABLE salidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    talla_id INT,
    color_id INT,
    bodega_id INT,
    cantidad INT NOT NULL,
    cliente_id INT,
    motivo ENUM('venta', 'devolucion', 'ajuste', 'traslado', 'perdida') DEFAULT 'venta',
    factura_remision VARCHAR(50),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (talla_id) REFERENCES tallas(id) ON DELETE SET NULL,
    FOREIGN KEY (color_id) REFERENCES colores(id) ON DELETE SET NULL,
    FOREIGN KEY (bodega_id) REFERENCES bodegas(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ========================================
-- INSERTAR DATOS DE PRUEBA
-- ========================================

-- Categorías
INSERT INTO categorias (nombre) VALUES 
('Caballeros'),
('Damas'),
('Niños'),
('Niñas');

-- Tallas
INSERT INTO tallas (nombre) VALUES 
('XS'), ('S'), ('M'), ('L'), ('XL'), ('XXL'),
('28'), ('30'), ('32'), ('34'), ('36'), ('38'), ('40'), ('42');

-- Colores
INSERT INTO colores (nombre) VALUES 
('Negro'), ('Blanco'), ('Azul'), ('Rojo'), ('Verde'), 
('Amarillo'), ('Gris'), ('Marrón'), ('Rosa'), ('Morado');

-- Bodegas
INSERT INTO bodegas (nombre, ubicacion) VALUES 
('Bodega Principal', 'Piso 1'),
('Bodega Secundaria', 'Piso 2'),
('Almacén Norte', 'Zona Norte');

-- Proveedores
INSERT INTO proveedores (nombre, telefono, correo) VALUES 
('Diseños Stely', '3001234567', 'contacto@stely.com'),
('Textiles ABC', '3009876543', 'ventas@abc.com'),
('Ropa Express', '3005555555', 'info@ropaexpress.com');

-- Clientes
INSERT INTO clientes (nombre, telefono, correo) VALUES 
('Nelson', '3001111111', 'nelson@email.com'),
('Tienda Fashion', '3002222222', 'ventas@fashion.com'),
('Boutique Elegante', '3003333333', 'info@boutique.com');

-- Productos
INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock_minimo) VALUES 
('Boxer Clásico', 'Boxer de algodón clásico', 25000.00, 1, 10),
('Boxer Premium', 'Boxer de algodón premium', 35000.00, 1, 10),
('Boxer Deportivo', 'Boxer deportivo microfibra', 45000.00, 1, 10),
('Blusa Casual', 'Blusa casual para damas', 55000.00, 2, 5),
('Pantalón Niño', 'Pantalón para niños', 40000.00, 3, 8);

-- Usuario admin
INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES 
('Administrador', 'admin@gardem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ========================================
-- ÍNDICES PARA MEJOR RENDIMIENTO
-- ========================================
CREATE INDEX idx_producto_categoria ON productos(categoria_id);
CREATE INDEX idx_inventario_producto ON inventario(producto_id);
CREATE INDEX idx_entradas_fecha ON entradas(fecha);
CREATE INDEX idx_salidas_fecha ON salidas(fecha);
CREATE INDEX idx_entradas_producto ON entradas(producto_id);
CREATE INDEX idx_salidas_producto ON salidas(producto_id);

-- ========================================
-- VISTAS ÚTILES
-- ========================================

-- Vista del inventario actual
CREATE VIEW vista_inventario AS
SELECT 
    p.id as producto_id,
    p.nombre as producto_nombre,
    c.nombre as categoria_nombre,
    t.nombre as talla_nombre,
    co.nombre as color_nombre,
    b.nombre as bodega_nombre,
    i.stock_actual,
    i.stock_minimo,
    (i.stock_actual - i.stock_minimo) as diferencia_stock
FROM inventario i
JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN tallas t ON i.talla_id = t.id
LEFT JOIN colores co ON i.color_id = co.id
LEFT JOIN bodegas b ON i.bodega_id = b.id;

-- Vista de productos con stock bajo
CREATE VIEW vista_stock_bajo AS
SELECT 
    p.nombre as producto_nombre,
    c.nombre as categoria_nombre,
    i.stock_actual,
    i.stock_minimo,
    (i.stock_minimo - i.stock_actual) as faltante
FROM inventario i
JOIN productos p ON i.producto_id = p.id
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE i.stock_actual <= i.stock_minimo;

-- ========================================
-- PROCEDIMIENTOS ÚTILES
-- ========================================

-- Procedimiento para actualizar stock después de entrada
DELIMITER //
CREATE PROCEDURE actualizar_stock_entrada(
    IN p_producto_id INT,
    IN p_talla_id INT,
    IN p_color_id INT,
    IN p_bodega_id INT,
    IN p_cantidad INT
)
BEGIN
    INSERT INTO inventario (producto_id, talla_id, color_id, bodega_id, stock_actual)
    VALUES (p_producto_id, p_talla_id, p_color_id, p_bodega_id, p_cantidad)
    ON DUPLICATE KEY UPDATE stock_actual = stock_actual + p_cantidad;
END //
DELIMITER ;

-- Procedimiento para actualizar stock después de salida
DELIMITER //
CREATE PROCEDURE actualizar_stock_salida(
    IN p_producto_id INT,
    IN p_talla_id INT,
    IN p_color_id INT,
    IN p_bodega_id INT,
    IN p_cantidad INT
)
BEGIN
    UPDATE inventario 
    SET stock_actual = stock_actual - p_cantidad
    WHERE producto_id = p_producto_id 
    AND (talla_id = p_talla_id OR (talla_id IS NULL AND p_talla_id IS NULL))
    AND (color_id = p_color_id OR (color_id IS NULL AND p_color_id IS NULL))
    AND (bodega_id = p_bodega_id OR (bodega_id IS NULL AND p_bodega_id IS NULL));
END //
DELIMITER ;

-- ========================================
-- TRIGGERS PARA MANTENER INTEGRIDAD
-- ========================================

-- Trigger para actualizar inventario después de entrada
DELIMITER //
CREATE TRIGGER trigger_entrada_inventario
AFTER INSERT ON entradas
FOR EACH ROW
BEGIN
    CALL actualizar_stock_entrada(NEW.producto_id, NEW.talla_id, NEW.color_id, NEW.bodega_id, NEW.cantidad);
END //
DELIMITER ;

-- Trigger para actualizar inventario después de salida
DELIMITER //
CREATE TRIGGER trigger_salida_inventario
AFTER INSERT ON salidas
FOR EACH ROW
BEGIN
    CALL actualizar_stock_salida(NEW.producto_id, NEW.talla_id, NEW.color_id, NEW.bodega_id, NEW.cantidad);
END //
DELIMITER ;

-- ========================================
-- MENSAJE FINAL
-- ========================================
SELECT '✅ Base de datos SIMPLE creada exitosamente!' as mensaje;
SELECT '📊 Tablas creadas: 11' as resumen;
SELECT '🔧 Características: LIMPIA, CLARA, SIN COMPLICACIONES' as caracteristicas; 