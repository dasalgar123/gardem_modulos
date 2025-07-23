<?php
require_once 'config/database.php';

echo "<h2>🔧 Creando Datos de Prueba</h2>";

try {
    // Crear categorías
    echo "<h3>📂 Creando categorías...</h3>";
    $categorias = [
        ['nombre' => 'Ropa Interior', 'descripcion' => 'Productos de ropa interior'],
        ['nombre' => 'Ropa Deportiva', 'descripcion' => 'Ropa para deportes'],
        ['nombre' => 'Ropa Casual', 'descripcion' => 'Ropa casual y diaria'],
        ['nombre' => 'Accesorios', 'descripcion' => 'Accesorios varios']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
    foreach ($categorias as $cat) {
        $stmt->execute([$cat['nombre'], $cat['descripcion']]);
    }
    echo "<p>✅ Categorías creadas: " . count($categorias) . "</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Categorías: " . $e->getMessage() . "</p>";
}

try {
    // Crear proveedores
    echo "<h3>🚚 Creando proveedores...</h3>";
    $proveedores = [
        ['nombre' => 'Proveedor A', 'contacto' => 'Juan Pérez', 'telefono' => '3001234567', 'email' => 'juan@proveedor.com'],
        ['nombre' => 'Proveedor B', 'contacto' => 'María García', 'telefono' => '3009876543', 'email' => 'maria@proveedor.com'],
        ['nombre' => 'Proveedor C', 'contacto' => 'Carlos López', 'telefono' => '3005555555', 'email' => 'carlos@proveedor.com']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, contacto, telefono, email) VALUES (?, ?, ?, ?)");
    foreach ($proveedores as $prov) {
        $stmt->execute([$prov['nombre'], $prov['contacto'], $prov['telefono'], $prov['email']]);
    }
    echo "<p>✅ Proveedores creados: " . count($proveedores) . "</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Proveedores: " . $e->getMessage() . "</p>";
}

try {
    // Crear productos
    echo "<h3>📦 Creando productos...</h3>";
    $productos = [
        ['nombre' => 'Boxer Clásico', 'descripcion' => 'Boxer de algodón clásico', 'precio' => 25000, 'categoria_id' => 1],
        ['nombre' => 'Boxer Deportivo', 'descripcion' => 'Boxer deportivo con tecnología dry-fit', 'precio' => 35000, 'categoria_id' => 2],
        ['nombre' => 'Camiseta Básica', 'descripcion' => 'Camiseta de algodón básica', 'precio' => 15000, 'categoria_id' => 3],
        ['nombre' => 'Pantalón Deportivo', 'descripcion' => 'Pantalón deportivo cómodo', 'precio' => 45000, 'categoria_id' => 2],
        ['nombre' => 'Calcetines Pack 3', 'descripcion' => 'Pack de 3 calcetines deportivos', 'precio' => 12000, 'categoria_id' => 4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id) VALUES (?, ?, ?, ?)");
    foreach ($productos as $prod) {
        $stmt->execute([$prod['nombre'], $prod['descripcion'], $prod['precio'], $prod['categoria_id']]);
    }
    echo "<p>✅ Productos creados: " . count($productos) . "</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Productos: " . $e->getMessage() . "</p>";
}

try {
    // Crear inventario
    echo "<h3>📋 Creando inventario...</h3>";
    $inventario = [
        ['producto_id' => 1, 'cantidad' => 50, 'bodega_id' => 1],
        ['producto_id' => 2, 'cantidad' => 30, 'bodega_id' => 1],
        ['producto_id' => 3, 'cantidad' => 100, 'bodega_id' => 1],
        ['producto_id' => 4, 'cantidad' => 25, 'bodega_id' => 1],
        ['producto_id' => 5, 'cantidad' => 0, 'bodega_id' => 1], // Agotado
        ['producto_id' => 1, 'cantidad' => 5, 'bodega_id' => 2], // Stock bajo
    ];
    
    $stmt = $pdo->prepare("INSERT INTO inventario (producto_id, cantidad, bodega_id) VALUES (?, ?, ?)");
    foreach ($inventario as $inv) {
        $stmt->execute([$inv['producto_id'], $inv['cantidad'], $inv['bodega_id']]);
    }
    echo "<p>✅ Inventario creado: " . count($inventario) . " registros</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Inventario: " . $e->getMessage() . "</p>";
}

try {
    // Crear entradas de hoy
    echo "<h3>⬇️ Creando entradas de hoy...</h3>";
    $entradas = [
        ['producto_id' => 1, 'cantidad' => 20, 'proveedor_id' => 1, 'fecha' => date('Y-m-d H:i:s')],
        ['producto_id' => 2, 'cantidad' => 15, 'proveedor_id' => 2, 'fecha' => date('Y-m-d H:i:s')],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO entradas (producto_id, cantidad, proveedor_id, fecha) VALUES (?, ?, ?, ?)");
    foreach ($entradas as $ent) {
        $stmt->execute([$ent['producto_id'], $ent['cantidad'], $ent['proveedor_id'], $ent['fecha']]);
    }
    echo "<p>✅ Entradas creadas: " . count($entradas) . "</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Entradas: " . $e->getMessage() . "</p>";
}

try {
    // Crear salidas de hoy
    echo "<h3>⬆️ Creando salidas de hoy...</h3>";
    $salidas = [
        ['producto_id' => 3, 'cantidad' => 5, 'cliente_id' => 1, 'fecha' => date('Y-m-d H:i:s')],
        ['producto_id' => 4, 'cantidad' => 3, 'cliente_id' => 1, 'fecha' => date('Y-m-d H:i:s')],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO salidas (producto_id, cantidad, cliente_id, fecha) VALUES (?, ?, ?, ?)");
    foreach ($salidas as $sal) {
        $stmt->execute([$sal['producto_id'], $sal['cantidad'], $sal['cliente_id'], $sal['fecha']]);
    }
    echo "<p>✅ Salidas creadas: " . count($salidas) . "</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Salidas: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>✅ Datos de prueba creados exitosamente</h3>";
echo "<p><a href='index.php' class='btn btn-success'>Volver al Dashboard</a></p>";
echo "<p><a href='verificar_db.php' class='btn btn-info'>Verificar Base de Datos</a></p>";
?> 