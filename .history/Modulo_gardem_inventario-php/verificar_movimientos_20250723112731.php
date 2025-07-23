<?php
require_once 'config/database.php';

echo "<h2>🔍 Verificar Movimientos de Productos</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style>";

try {
    // Listar todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📊 Tablas encontradas:</h3>";
    echo "<p>" . implode(', ', $tablas) . "</p>";
    
    // Buscar tablas relevantes
    $tabla_productos = null;
    $tabla_entradas = null;
    $tabla_salidas = null;
    $tabla_inventario = null;
    
    foreach ($tablas as $tabla) {
        if (strpos(strtolower($tabla), 'product') !== false) {
            $tabla_productos = $tabla;
        }
        if (strpos(strtolower($tabla), 'entrada') !== false) {
            $tabla_entradas = $tabla;
        }
        if (strpos(strtolower($tabla), 'salida') !== false) {
            $tabla_salidas = $tabla;
        }
        if (strpos(strtolower($tabla), 'inventario') !== false || strpos(strtolower($tabla), 'stock') !== false) {
            $tabla_inventario = $tabla;
        }
    }
    
    echo "<div class='card'>";
    echo "<h4>🔍 Tablas Identificadas:</h4>";
    echo "<p><strong>Productos:</strong> " . ($tabla_productos ?: 'No encontrada') . "</p>";
    echo "<p><strong>Entradas:</strong> " . ($tabla_entradas ?: 'No encontrada') . "</p>";
    echo "<p><strong>Salidas:</strong> " . ($tabla_salidas ?: 'No encontrada') . "</p>";
    echo "<p><strong>Inventario:</strong> " . ($tabla_inventario ?: 'No encontrada') . "</p>";
    echo "</div>";
    
    // Verificar productos
    if ($tabla_productos) {
        echo "<div class='card'>";
        echo "<h4>📦 Productos Disponibles:</h4>";
        $stmt = $pdo->query("SELECT id, nombre, precio FROM `$tabla_productos` ORDER BY nombre");
        $productos = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th></tr>";
        foreach ($productos as $producto) {
            echo "<tr>";
            echo "<td>{$producto['id']}</td>";
            echo "<td>{$producto['nombre']}</td>";
            echo "<td>$" . number_format($producto['precio'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    // Verificar inventario actual
    if ($tabla_inventario) {
        echo "<div class='card'>";
        echo "<h4>📊 Inventario Actual:</h4>";
        
        // Buscar columnas de la tabla inventario
        $stmt = $pdo->query("DESCRIBE `$tabla_inventario`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columna_producto = null;
        $columna_cantidad = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'product') !== false) {
                $columna_producto = $col;
            }
            if (strpos(strtolower($col), 'cantidad') !== false || strpos(strtolower($col), 'stock') !== false) {
                $columna_cantidad = $col;
            }
        }
        
        if ($columna_producto && $columna_cantidad) {
            $query = "SELECT i.$columna_producto, p.nombre, i.$columna_cantidad 
                     FROM `$tabla_inventario` i 
                     LEFT JOIN `$tabla_productos` p ON i.$columna_producto = p.id 
                     ORDER BY i.$columna_cantidad DESC";
            
            $stmt = $pdo->query($query);
            $inventario = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID Producto</th><th>Nombre</th><th>Stock Actual</th></tr>";
            foreach ($inventario as $item) {
                $stock_class = $item[$columna_cantidad] == 0 ? 'error' : ($item[$columna_cantidad] < 10 ? 'warning' : 'success');
                echo "<tr>";
                echo "<td>{$item[$columna_producto]}</td>";
                echo "<td>{$item['nombre']}</td>";
                echo "<td class='$stock_class'>{$item[$columna_cantidad]}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>No se pudieron identificar las columnas de producto y cantidad</p>";
        }
        echo "</div>";
    }
    
    // Verificar entradas
    if ($tabla_entradas) {
        echo "<div class='card'>";
        echo "<h4>⬇️ Entradas (Últimas 10):</h4>";
        
        // Buscar columnas de fecha
        $stmt = $pdo->query("DESCRIBE `$tabla_entradas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columna_fecha = null;
        $columna_producto = null;
        $columna_cantidad = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'fecha') !== false) {
                $columna_fecha = $col;
            }
            if (strpos(strtolower($col), 'product') !== false) {
                $columna_producto = $col;
            }
            if (strpos(strtolower($col), 'cantidad') !== false) {
                $columna_cantidad = $col;
            }
        }
        
        if ($columna_fecha && $columna_producto && $columna_cantidad) {
            $query = "SELECT e.$columna_fecha, e.$columna_producto, p.nombre, e.$columna_cantidad 
                     FROM `$tabla_entradas` e 
                     LEFT JOIN `$tabla_productos` p ON e.$columna_producto = p.id 
                     ORDER BY e.$columna_fecha DESC LIMIT 10";
            
            $stmt = $pdo->query($query);
            $entradas = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>Fecha</th><th>ID Producto</th><th>Nombre</th><th>Cantidad</th></tr>";
            foreach ($entradas as $entrada) {
                echo "<tr>";
                echo "<td>{$entrada[$columna_fecha]}</td>";
                echo "<td>{$entrada[$columna_producto]}</td>";
                echo "<td>{$entrada['nombre']}</td>";
                echo "<td class='success'>{$entrada[$columna_cantidad]}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>No se pudieron identificar las columnas necesarias</p>";
        }
        echo "</div>";
    }
    
    // Verificar salidas
    if ($tabla_salidas) {
        echo "<div class='card'>";
        echo "<h4>⬆️ Salidas (Últimas 10):</h4>";
        
        // Buscar columnas de fecha
        $stmt = $pdo->query("DESCRIBE `$tabla_salidas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columna_fecha = null;
        $columna_producto = null;
        $columna_cantidad = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'fecha') !== false) {
                $columna_fecha = $col;
            }
            if (strpos(strtolower($col), 'product') !== false) {
                $columna_producto = $col;
            }
            if (strpos(strtolower($col), 'cantidad') !== false) {
                $columna_cantidad = $col;
            }
        }
        
        if ($columna_fecha && $columna_producto && $columna_cantidad) {
            $query = "SELECT s.$columna_fecha, s.$columna_producto, p.nombre, s.$columna_cantidad 
                     FROM `$tabla_salidas` s 
                     LEFT JOIN `$tabla_productos` p ON s.$columna_producto = p.id 
                     ORDER BY s.$columna_fecha DESC LIMIT 10";
            
            $stmt = $pdo->query($query);
            $salidas = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>Fecha</th><th>ID Producto</th><th>Nombre</th><th>Cantidad</th></tr>";
            foreach ($salidas as $salida) {
                echo "<tr>";
                echo "<td>{$salida[$columna_fecha]}</td>";
                echo "<td>{$salida[$columna_producto]}</td>";
                echo "<td>{$salida['nombre']}</td>";
                echo "<td class='error'>{$salida[$columna_cantidad]}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>No se pudieron identificar las columnas necesarias</p>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al Dashboard</a></p>";
?> 