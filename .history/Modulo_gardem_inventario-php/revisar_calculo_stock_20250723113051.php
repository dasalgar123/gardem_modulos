<?php
require_once 'config/database.php';

echo "<h2>🔍 Revisar Cálculo de Stock - Producto ID 10</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .calculation { background-color: #f9f9f9; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; }
</style>";

try {
    // Buscar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
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
    echo "<h3>📊 Información del Producto ID 10</h3>";
    
    // Obtener información del producto
    if ($tabla_productos) {
        $stmt = $pdo->prepare("SELECT id, nombre, precio FROM `$tabla_productos` WHERE id = 10");
        $stmt->execute();
        $producto = $stmt->fetch();
        
        if ($producto) {
            echo "<p><strong>Producto:</strong> {$producto['nombre']}</p>";
            echo "<p><strong>Precio:</strong> $" . number_format($producto['precio'], 0, ',', '.') . "</p>";
        } else {
            echo "<p class='error'>No se encontró el producto con ID 10</p>";
        }
    }
    echo "</div>";
    
    // Revisar entradas del producto ID 10
    if ($tabla_entradas) {
        echo "<div class='card'>";
        echo "<h3>⬇️ Entradas del Producto ID 10</h3>";
        
        // Buscar columnas
        $stmt = $pdo->query("DESCRIBE `$tabla_entradas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columna_producto = null;
        $columna_cantidad = null;
        $columna_fecha = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'product') !== false) {
                $columna_producto = $col;
            }
            if (strpos(strtolower($col), 'cantidad') !== false) {
                $columna_cantidad = $col;
            }
            if (strpos(strtolower($col), 'fecha') !== false) {
                $columna_fecha = $col;
            }
        }
        
        if ($columna_producto && $columna_cantidad) {
            $query = "SELECT $columna_cantidad, $columna_fecha 
                     FROM `$tabla_entradas` 
                     WHERE $columna_producto = 10 
                     ORDER BY $columna_fecha DESC";
            
            $stmt = $pdo->query($query);
            $entradas = $stmt->fetchAll();
            
            $total_entradas = 0;
            echo "<table>";
            echo "<tr><th>Fecha</th><th>Cantidad Entrada</th></tr>";
            foreach ($entradas as $entrada) {
                $total_entradas += $entrada[$columna_cantidad];
                echo "<tr>";
                echo "<td>{$entrada[$columna_fecha]}</td>";
                echo "<td class='success'>{$entrada[$columna_cantidad]}</td>";
                echo "</tr>";
            }
            echo "<tr><th>TOTAL ENTRADAS</th><th class='success'>$total_entradas</th></tr>";
            echo "</table>";
        }
        echo "</div>";
    }
    
    // Revisar salidas del producto ID 10
    if ($tabla_salidas) {
        echo "<div class='card'>";
        echo "<h3>⬆️ Salidas del Producto ID 10</h3>";
        
        // Buscar columnas
        $stmt = $pdo->query("DESCRIBE `$tabla_salidas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columna_producto = null;
        $columna_cantidad = null;
        $columna_fecha = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'product') !== false) {
                $columna_producto = $col;
            }
            if (strpos(strtolower($col), 'cantidad') !== false) {
                $columna_cantidad = $col;
            }
            if (strpos(strtolower($col), 'fecha') !== false) {
                $columna_fecha = $col;
            }
        }
        
        if ($columna_producto && $columna_cantidad) {
            $query = "SELECT $columna_cantidad, $columna_fecha 
                     FROM `$tabla_salidas` 
                     WHERE $columna_producto = 10 
                     ORDER BY $columna_fecha DESC";
            
            $stmt = $pdo->query($query);
            $salidas = $stmt->fetchAll();
            
            $total_salidas = 0;
            echo "<table>";
            echo "<tr><th>Fecha</th><th>Cantidad Salida</th></tr>";
            foreach ($salidas as $salida) {
                $total_salidas += $salida[$columna_cantidad];
                echo "<tr>";
                echo "<td>{$salida[$columna_fecha]}</td>";
                echo "<td class='error'>{$salida[$columna_cantidad]}</td>";
                echo "</tr>";
            }
            echo "<tr><th>TOTAL SALIDAS</th><th class='error'>$total_salidas</th></tr>";
            echo "</table>";
        }
        echo "</div>";
    }
    
    // Revisar inventario actual
    if ($tabla_inventario) {
        echo "<div class='card'>";
        echo "<h3>📊 Inventario Actual del Producto ID 10</h3>";
        
        // Buscar columnas
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
            $query = "SELECT $columna_cantidad 
                     FROM `$tabla_inventario` 
                     WHERE $columna_producto = 10";
            
            $stmt = $pdo->query($query);
            $inventario = $stmt->fetch();
            
            $stock_actual = $inventario ? $inventario[$columna_cantidad] : 0;
            echo "<p><strong>Stock Actual en BD:</strong> <span class='info'>$stock_actual</span></p>";
        }
        echo "</div>";
    }
    
    // Cálculo manual
    echo "<div class='calculation'>";
    echo "<h3>🧮 Cálculo Manual</h3>";
    
    $total_entradas = 0;
    $total_salidas = 0;
    $stock_actual = 0;
    
    // Obtener totales
    if ($tabla_entradas) {
        $stmt = $pdo->query("DESCRIBE `$tabla_entradas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $columna_producto = null;
        $columna_cantidad = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'product') !== false) $columna_producto = $col;
            if (strpos(strtolower($col), 'cantidad') !== false) $columna_cantidad = $col;
        }
        
        if ($columna_producto && $columna_cantidad) {
            $stmt = $pdo->prepare("SELECT SUM($columna_cantidad) as total FROM `$tabla_entradas` WHERE $columna_producto = 10");
            $stmt->execute();
            $result = $stmt->fetch();
            $total_entradas = $result['total'] ?: 0;
        }
    }
    
    if ($tabla_salidas) {
        $stmt = $pdo->query("DESCRIBE `$tabla_salidas`");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $columna_producto = null;
        $columna_cantidad = null;
        
        foreach ($columnas as $col) {
            if (strpos(strtolower($col), 'product') !== false) $columna_producto = $col;
            if (strpos(strtolower($col), 'cantidad') !== false) $columna_cantidad = $col;
        }
        
        if ($columna_producto && $columna_cantidad) {
            $stmt = $pdo->prepare("SELECT SUM($columna_cantidad) as total FROM `$tabla_salidas` WHERE $columna_producto = 10");
            $stmt->execute();
            $result = $stmt->fetch();
            $total_salidas = $result['total'] ?: 0;
        }
    }
    
    $stock_calculado = $total_entradas - $total_salidas;
    
    echo "<p><strong>Total Entradas:</strong> <span class='success'>$total_entradas</span></p>";
    echo "<p><strong>Total Salidas:</strong> <span class='error'>$total_salidas</span></p>";
    echo "<p><strong>Stock Calculado:</strong> <span class='info'>$total_entradas - $total_salidas = $stock_calculado</span></p>";
    
    if ($stock_calculado != 1) {
        echo "<p class='warning'>⚠️ PROBLEMA DETECTADO: El stock debería ser $stock_calculado, no 1</p>";
        echo "<p class='info'>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>Registros faltantes en entradas o salidas</li>";
        echo "<li>Error en la actualización del inventario</li>";
        echo "<li>Datos inconsistentes en la base de datos</li>";
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ El cálculo es correcto</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al Dashboard</a></p>";
?> 