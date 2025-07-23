<?php
/**
 * Funciones auxiliares para el Sistema de Almacenista
 */

/**
 * Formatear fecha para mostrar
 */
function formatearFecha($fecha) {
    if (!$fecha) return '';
    return date('d/m/Y', strtotime($fecha));
}

/**
 * Formatear fecha y hora para mostrar
 */
function formatearFechaHora($fecha) {
    if (!$fecha) return '';
    return date('d/m/Y H:i', strtotime($fecha));
}

/**
 * Formatear moneda
 */
function formatearMoneda($cantidad) {
    return '$' . number_format($cantidad, 2);
}

/**
 * Formatear número
 */
function formatearNumero($numero) {
    return number_format($numero, 0);
}

/**
 * Obtener estado de stock
 */
function obtenerEstadoStock($cantidad, $stock_minimo) {
    if ($cantidad <= 0) {
        return '<span class="badge bg-danger">Sin Stock</span>';
    } elseif ($cantidad <= $stock_minimo) {
        return '<span class="badge bg-warning">Stock Bajo</span>';
    } else {
        return '<span class="badge bg-success">Disponible</span>';
    }
}

/**
 * Obtener estado de movimiento
 */
function obtenerEstadoMovimiento($estado) {
    switch ($estado) {
        case 'pendiente':
            return '<span class="badge bg-warning">Pendiente</span>';
        case 'aprobado':
            return '<span class="badge bg-success">Aprobado</span>';
        case 'rechazado':
            return '<span class="badge bg-danger">Rechazado</span>';
        case 'completado':
            return '<span class="badge bg-primary">Completado</span>';
        default:
            return '<span class="badge bg-secondary">' . ucfirst($estado) . '</span>';
    }
}

/**
 * Obtener tipo de movimiento
 */
function obtenerTipoMovimiento($tipo) {
    switch ($tipo) {
        case 'entrada':
            return '<span class="badge bg-success">Entrada</span>';
        case 'salida':
            return '<span class="badge bg-danger">Salida</span>';
        case 'traslado':
            return '<span class="badge bg-info">Traslado</span>';
        case 'devolucion':
            return '<span class="badge bg-warning">Devolución</span>';
        default:
            return '<span class="badge bg-secondary">' . ucfirst($tipo) . '</span>';
    }
}

/**
 * Validar email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validar teléfono
 */
function validarTelefono($telefono) {
    return preg_match('/^[\d\s\-\+\(\)]+$/', $telefono);
}

/**
 * Sanitizar entrada
 */
function sanitizar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

/**
 * Generar código único
 */
function generarCodigo($prefijo = '', $longitud = 8) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $prefijo . $codigo;
}

/**
 * Obtener nombre de usuario por ID
 */
function obtenerNombreUsuario($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Usuario Desconocido';
}

/**
 * Obtener nombre de proveedor por ID
 */
function obtenerNombreProveedor($pdo, $proveedor_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM proveedores WHERE id = ?");
    $stmt->execute([$proveedor_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Proveedor Desconocido';
}

/**
 * Obtener nombre de producto por ID
 */
function obtenerNombreProducto($pdo, $producto_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Producto Desconocido';
}

/**
 * Obtener nombre de almacén por ID
 */
function obtenerNombreAlmacen($pdo, $almacen_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM almacenes WHERE id = ?");
    $stmt->execute([$almacen_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Almacén Desconocido';
}

/**
 * Obtener nombre de color por ID
 */
function obtenerNombreColor($pdo, $color_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM colores WHERE id = ?");
    $stmt->execute([$color_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Color Desconocido';
}

/**
 * Obtener nombre de talla por ID
 */
function obtenerNombreTalla($pdo, $talla_id) {
    $stmt = $pdo->prepare("SELECT nombre FROM tallas WHERE id = ?");
    $stmt->execute([$talla_id]);
    $resultado = $stmt->fetch();
    return $resultado ? $resultado['nombre'] : 'Talla Desconocida';
}

/**
 * Verificar permisos de usuario
 */
function verificarPermisos($rol_requerido) {
    if (!isset($_SESSION['usuario_rol'])) {
        return false;
    }
    
    $roles = [
        'admin' => ['admin'],
        'almacenista' => ['admin', 'almacenista'],
        'vendedor' => ['admin', 'almacenista', 'vendedor']
    ];
    
    return in_array($_SESSION['usuario_rol'], $roles[$rol_requerido] ?? []);
}

/**
 * Registrar log de actividad
 */
function registrarLog($pdo, $usuario_id, $accion, $detalles = '') {
    $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$usuario_id, $accion, $detalles]);
}

/**
 * Obtener estadísticas de inventario
 */
function obtenerEstadisticasInventario($pdo) {
    $stats = [];
    
    // Total de productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $stats['total_productos'] = $stmt->fetch()['total'];
    
    // Productos con stock bajo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario WHERE cantidad <= stock_minimo");
    $stats['stock_bajo'] = $stmt->fetch()['total'];
    
    // Productos sin stock
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventario WHERE cantidad = 0");
    $stats['sin_stock'] = $stmt->fetch()['total'];
    
    // Valor total del inventario
    $stmt = $pdo->query("SELECT SUM(i.cantidad * p.precio) as valor_total FROM inventario i JOIN productos p ON i.producto_id = p.id");
    $stats['valor_total'] = $stmt->fetch()['valor_total'] ?? 0;
    
    return $stats;
}

/**
 * Obtener movimientos recientes
 */
function obtenerMovimientosRecientes($pdo, $limite = 10) {
    $stmt = $pdo->prepare("
        SELECT 
            'entrada' as tipo,
            e.fecha,
            e.numero_documento,
            p.nombre as producto_nombre,
            e.cantidad,
            e.precio_unitario
        FROM entradas e
        JOIN productos p ON e.producto_id = p.id
        UNION ALL
        SELECT 
            'salida' as tipo,
            s.fecha,
            s.numero_documento,
            p.nombre as producto_nombre,
            s.cantidad,
            s.precio_unitario
        FROM salidas s
        JOIN productos p ON s.producto_id = p.id
        ORDER BY fecha DESC
        LIMIT ?
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

/**
 * Calcular edad
 */
function calcularEdad($fecha_nacimiento) {
    $fecha = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha);
    return $edad->y;
}

/**
 * Formatear tamaño de archivo
 */
function formatearTamañoArchivo($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($unidades) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $unidades[$pow];
}

/**
 * Validar archivo de imagen
 */
function validarImagen($archivo) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tamaño_maximo = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return 'Tipo de archivo no permitido. Solo se permiten JPG, PNG y GIF.';
    }
    
    if ($archivo['size'] > $tamaño_maximo) {
        return 'El archivo es demasiado grande. Máximo 5MB.';
    }
    
    return true;
}

/**
 * Subir archivo
 */
function subirArchivo($archivo, $directorio_destino, $nombre_personalizado = null) {
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = $nombre_personalizado ? $nombre_personalizado . '.' . $extension : uniqid() . '.' . $extension;
    $ruta_completa = $directorio_destino . '/' . $nombre_archivo;
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return $nombre_archivo;
    }
    
    return false;
}

/**
 * Eliminar archivo
 */
function eliminarArchivo($ruta_archivo) {
    if (file_exists($ruta_archivo)) {
        return unlink($ruta_archivo);
    }
    return false;
}

/**
 * Generar PDF simple
 */
function generarPDF($contenido, $nombre_archivo = 'documento.pdf') {
    // Implementación básica - en producción usar librería como TCPDF o FPDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    
    // Aquí iría la lógica de generación de PDF
    echo $contenido;
}

/**
 * Enviar email
 */
function enviarEmail($destinatario, $asunto, $mensaje, $remitente = null) {
    if (!$remitente) {
        $remitente = 'noreply@sistema-almacenista.com';
    }
    
    $headers = "From: $remitente\r\n";
    $headers .= "Reply-To: $remitente\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($destinatario, $asunto, $mensaje, $headers);
}

/**
 * Obtener IP del cliente
 */
function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Verificar si es una petición AJAX
 */
function esAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Respuesta JSON
 */
function respuestaJSON($datos, $estado = 200) {
    http_response_code($estado);
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit();
}

/**
 * Redireccionar con mensaje
 */
function redireccionarConMensaje($url, $mensaje, $tipo = 'success') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header('Location: ' . $url);
    exit();
}

/**
 * Mostrar mensaje
 */
function mostrarMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
        
        return "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                    {$mensaje}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

/**
 * Validar fecha
 */
function validarFecha($fecha) {
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
    return $fecha_obj && $fecha_obj->format('Y-m-d') === $fecha;
}

/**
 * Obtener días entre fechas
 */
function obtenerDiasEntreFechas($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $diferencia = $inicio->diff($fin);
    return $diferencia->days;
}

/**
 * Formatear tiempo transcurrido
 */
function tiempoTranscurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    
    if ($tiempo < 60) {
        return 'Hace un momento';
    } elseif ($tiempo < 3600) {
        $minutos = floor($tiempo / 60);
        return "Hace {$minutos} minuto" . ($minutos > 1 ? 's' : '');
    } elseif ($tiempo < 86400) {
        $horas = floor($tiempo / 3600);
        return "Hace {$horas} hora" . ($horas > 1 ? 's' : '');
    } else {
        $dias = floor($tiempo / 86400);
        return "Hace {$dias} día" . ($dias > 1 ? 's' : '');
    }
}

/**
 * Generar contraseña aleatoria
 */
function generarContraseña($longitud = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $contraseña = '';
    for ($i = 0; $i < $longitud; $i++) {
        $contraseña .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $contraseña;
}

/**
 * Encriptar contraseña
 */
function encriptarContraseña($contraseña) {
    return password_hash($contraseña, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verificarContraseña($contraseña, $hash) {
    return password_verify($contraseña, $hash);
}

/**
 * Obtener información del navegador
 */
function obtenerNavegador() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (strpos($user_agent, 'Chrome') !== false) {
        return 'Chrome';
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        return 'Firefox';
    } elseif (strpos($user_agent, 'Safari') !== false) {
        return 'Safari';
    } elseif (strpos($user_agent, 'Edge') !== false) {
        return 'Edge';
    } else {
        return 'Desconocido';
    }
}

/**
 * Obtener sistema operativo
 */
function obtenerSistemaOperativo() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (strpos($user_agent, 'Windows') !== false) {
        return 'Windows';
    } elseif (strpos($user_agent, 'Mac') !== false) {
        return 'macOS';
    } elseif (strpos($user_agent, 'Linux') !== false) {
        return 'Linux';
    } elseif (strpos($user_agent, 'Android') !== false) {
        return 'Android';
    } elseif (strpos($user_agent, 'iOS') !== false) {
        return 'iOS';
    } else {
        return 'Desconocido';
    }
}
?> 