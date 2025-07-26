<?php
// Conexión a la base de datos usando PDO
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controlador/ControladorEntradas.php';

// Obtener datos usando el controlador
$controladorEntradas = new ControladorEntradas($pdo);
$entradas = $controladorEntradas->obtenerEntradas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entradas</title>
    <link rel="stylesheet" href="../css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="entrada-main-content">
        <div class="entrada-header">
            <h1><i class="fas fa-arrow-down"></i> Gestión de Entradas</h1>
            
        </div>
        
        <div class="entrada-content content-entradas">
            <div class="entrada-card">
                <h2><i class="fas fa-list"></i> Listado de Entradas</h2>
              
                <div class="entradas-container">
                    <table class="entradas-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Color</th>
                                <th>Talla</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Beneficiario</th>
                                <th>Factura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No hay entradas registradas
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($entradas as $entrada): ?>
                                <tr>
                                    <td><?php echo $entrada['id']; ?></td>
                                    <td><strong><?php echo !empty($entrada['producto']) ? htmlspecialchars($entrada['producto']) : 'N/A'; ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo !empty($entrada['color']) ? $entrada['color'] : 'N/A'; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo !empty($entrada['talla']) ? $entrada['talla'] : 'N/A'; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $entrada['cantidad']; ?></span></td>
                                    <td><small><?php echo date('d/m/Y H:i', strtotime($entrada['fecha'])); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($entrada['motivo'] ?? 'N/A'); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($entrada['beneficiario'] ?? 'N/A'); ?></small></td>
                                    <td><code><?php echo htmlspecialchars($entrada['factura_remision'] ?? 'N/A'); ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 