<?php
// Script para migrar datos de productos_entradas a entradas y entrada_detalles
require_once '../config/database.php';

echo "<h2>Migración de Entradas</h2>";
echo "<p>Base de datos: gardelcatalogo</p>";

try {
    // 1. Verificar si existe la tabla productos_entradas
    $stmt = $pdo->query("SHOW TABLES LIKE 'productos_entradas'");
    $tabla_exists = $stmt->rowCount() > 0;
    
    if (!$tabla_exists) {
        echo "<div style='color: red;'>❌ No existe la tabla 'productos_entradas'</div>";
        exit;
    }
    
    echo "<div style='color: green;'>✅ Tabla 'productos_entradas' encontrada</div>";
    
    // 2. Verificar si existen las tablas destino
    $stmt = $pdo->query("SHOW TABLES LIKE 'entradas'");
    $entradas_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'entrada_detalles'");
    $detalles_exists = $stmt->rowCount() > 0;
    
    if (!$entradas_exists) {
        echo "<div style='color: orange;'>⚠️ Creando tabla 'entradas'...</div>";
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
        echo "<div style='color: green;'>✅ Tabla 'entradas' creada</div>";
    }
    
    if (!$detalles_exists) {
        echo "<div style='color: orange;'>⚠️ Creando tabla 'entrada_detalles'...</div>";
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
        echo "<div style='color: green;'>✅ Tabla 'entrada_detalles' creada</div>";
    }
    
    // 3. Contar registros en productos_entradas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos_entradas");
    $total_registros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Registros en productos_entradas:</strong> {$total_registros}</p>";
    
    // 4. Obtener datos de productos_entradas
    $stmt = $pdo->query("SELECT * FROM productos_entradas ORDER BY fecha");
    $productos_entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrados = 0;
    $errores = 0;
    
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
            
        } catch (Exception $e) {
            $errores++;
            echo "<div style='color: red;'>❌ Error migrando registro ID {$pe['id']}: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0;'>";
    echo "<strong>✅ Migración completada!</strong><br>";
    echo "Registros migrados: <strong>{$migrados}</strong><br>";
    echo "Errores: <strong>{$errores}</strong>";
    echo "</div>";
    
    // 5. Verificar resultado
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entradas");
    $total_entradas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entrada_detalles");
    $total_detalles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p><strong>Total entradas:</strong> {$total_entradas}</p>";
    echo "<p><strong>Total detalles:</strong> {$total_detalles}</p>";
    
    echo "<p><a href='index.php?page=entradas' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Entradas</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?> 