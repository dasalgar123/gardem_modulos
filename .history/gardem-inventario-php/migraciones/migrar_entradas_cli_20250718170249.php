<?php
// Script para migrar datos de productos_entradas a entradas y entrada_detalles
// Versión para línea de comandos

// Configuración directa para XAMPP local
$DB_HOST = 'localhost';
$DB_NAME = 'gardelcatalogo';
$DB_USER = 'root';
$DB_PASS = '';

echo "=== Migración de Entradas ===\n";
echo "Base de datos: {$DB_NAME}\n\n";

try {
    // Conectar a la base de datos
    $dsn = 'mysql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    echo "✅ Conexión a la base de datos exitosa\n";
    
    // 1. Verificar si existe la tabla productos_entradas
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos_entradas'");
    $tabla_exists = $stmt->rowCount() > 0;
    
    if (!$tabla_exists) {
        echo "❌ No existe la tabla 'productos_entradas'\n";
        exit;
    }
    
    echo "✅ Tabla 'productos_entradas' encontrada\n";
    
    // 2. Verificar si existen las tablas destino
    $stmt = $pdo->query("SHOW TABLES LIKE 'entradas'");
    $entradas_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'entrada_detalles'");
    $detalles_exists = $stmt->rowCount() > 0;
    
    if (!$entradas_exists) {
        echo "⚠️ Creando tabla 'entradas'...\n";
        $create_entradas = "
        CREATE TABLE IF NOT EXISTS `entradas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `numero_documento` VARCHAR(50) UNIQUE NOT NULL,
            `proveedor_id` INT,
            `bodega_id` INT NOT NULL,
            `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `total` DECIMAL(10,2) DEFAULT 0,
            `observaciones` TEXT,
            `usuario_id` INT NOT NULL,
            `estado` ENUM('pendiente', 'confirmada', 'anulada') DEFAULT 'pendiente',
            FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
            FOREIGN KEY (bodega_id) REFERENCES bodegas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        )";
        $pdo->exec($create_entradas);
        echo "✅ Tabla 'entradas' creada\n";
    }
    
    if (!$detalles_exists) {
        echo "⚠️ Creando tabla 'entrada_detalles'...\n";
        $create_detalles = "
        CREATE TABLE IF NOT EXISTS `entrada_detalles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `entrada_id` INT NOT NULL,
            `producto_id` INT NOT NULL,
            `color_id` INT,
            `talla_id` INT,
            `cantidad` INT NOT NULL,
            `precio_unitario` DECIMAL(10,2) NOT NULL,
            `subtotal` DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (entrada_id) REFERENCES entradas(id) ON DELETE CASCADE,
            FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
            FOREIGN KEY (color_id) REFERENCES colores(id) ON DELETE SET NULL,
            FOREIGN KEY (talla_id) REFERENCES tallas(id) ON DELETE SET NULL
        )";
        $pdo->exec($create_detalles);
        echo "✅ Tabla 'entrada_detalles' creada\n";
    }
    
    // 3. Contar registros en productos_entradas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_entradas");
    $total_registros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 Registros en productos_entradas: {$total_registros}\n";
    
    // 4. Obtener datos de productos_entradas
    $stmt = $pdo->query("SELECT * FROM productos_entradas ORDER BY fecha");
    $productos_entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrados = 0;
    $errores = 0;
    
    echo "\n🔄 Iniciando migración...\n";
    
    foreach ($productos_entradas as $pe) {
        try {
            // Crear entrada
            $numero_documento = $pe['factura_remision'] ?: 'ENT-' . date('Ymd') . '-' . str_pad($pe['id'], 4, '0', STR_PAD_LEFT);
            $proveedor_id = null;
            
            // Buscar proveedor por nombre si existe
            if (!empty($pe['beneficiario'])) {
                $stmt = $pdo->prepare("SELECT id FROM proveedores WHERE nombre LIKE ?");
                $stmt->execute(['%' . $pe['beneficiario'] . '%']);
                $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($proveedor) {
                    $proveedor_id = $proveedor['id'];
                }
            }
            
            // Usar bodega_id o bodega por defecto
            $bodega_id = $pe['bodega_id'] ?: 1; // Bodega por defecto
            
            // Usar usuario por defecto
            $usuario_id = 1; // Usuario por defecto
            
            $sql_entrada = "INSERT INTO entradas (numero_documento, proveedor_id, bodega_id, fecha, total, observaciones, usuario_id, estado) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmada')";
            $stmt = $pdo->prepare($sql_entrada);
            $stmt->execute([
                $numero_documento,
                $proveedor_id,
                $bodega_id,
                $pe['fecha'],
                $pe['cantidad'] * 1000, // Precio estimado
                "Migrado desde productos_entradas - Motivo: " . ($pe['motivo'] ?: 'compra'),
                $usuario_id
            ]);
            
            $entrada_id = $pdo->lastInsertId();
            
            // Crear detalle
            $sql_detalle = "INSERT INTO entrada_detalles (entrada_id, producto_id, cantidad, precio_unitario, subtotal) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_detalle);
            $stmt->execute([
                $entrada_id,
                $pe['producto_id'],
                $pe['cantidad'],
                1000, // Precio unitario estimado
                $pe['cantidad'] * 1000
            ]);
            
            $migrados++;
            echo "✅ Migrado registro ID {$pe['id']} - Producto ID {$pe['producto_id']} - Cantidad {$pe['cantidad']}\n";
            
        } catch (Exception $e) {
            $errores++;
            echo "❌ Error migrando registro ID {$pe['id']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== RESULTADO DE LA MIGRACIÓN ===\n";
    echo "✅ Registros migrados: {$migrados}\n";
    echo "❌ Errores: {$errores}\n";
    
    // 5. Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas");
    $total_entradas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entrada_detalles");
    $total_detalles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "\n📊 Total entradas: {$total_entradas}\n";
    echo "📊 Total detalles: {$total_detalles}\n";
    
    echo "\n🎉 ¡Migración completada exitosamente!\n";
    echo "Ahora puedes acceder a la página de entradas en tu navegador.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 