-- Crear tabla de usuarios para el sistema de vendedor
-- Base de datos: gardelcatalogo

CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL UNIQUE,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor') NOT NULL DEFAULT 'vendedor',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario de prueba
INSERT INTO `usuario` (`nombre`, `correo`, `contraseña`, `rol`) VALUES
('Vendedor Demo', 'vendedor@demo.com', '123456', 'vendedor'),
('Administrador', 'admin@demo.com', 'admin123', 'admin');

-- Verificar que se creó correctamente
SELECT 'Tabla usuario creada correctamente' as mensaje;
SELECT COUNT(*) as total_usuarios FROM usuario; 