<?php
// Archivo de prueba para verificar el controlador
require_once 'config/database.php';
require_once 'controlador/ControladorSalidas.php';

$controlador = new ControladorSalidas($pdo);

// Verificar si el método existe
if (method_exists($controlador, 'guardarSalida')) {
    echo "✅ El método guardarSalida() SÍ existe";
} else {
    echo "❌ El método guardarSalida() NO existe";
}

// Listar todos los métodos
echo "<br><br>Métodos disponibles:<br>";
$methods = get_class_methods($controlador);
foreach ($methods as $method) {
    echo "- $method<br>";
}
?> 