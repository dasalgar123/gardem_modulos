<?php
// Conexión a la base de datos usando PDO
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controlador/ControladorSalidas.php';

// Obtener datos usando el controlador
$controladorSalidas = new ControladorSalidas($pdo);
$salidas = $controladorSalidas->obtenerSalidas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salidas</title>
    <link rel="stylesheet" href="../css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="salida-main-content">
        <div class="salida-header">
            <h1><i class="fas fa-arrow-up"></i> Gestión de Salidas</h1>
            <button type="button" class="btn btn-primary" onclick="mostrarFormularioSalida()">
                <i class="fas fa-plus"></i> Nueva Salida
            </button>
        </div>
        
        <div class="salida-content content-salidas">
            <div class="salida-card">
                <h2><i class="fas fa-list"></i> Listado de Salidas</h2>
              
                <div class="salidas-container">
                    <table class="salidas-table">
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
                            <?php if (empty($salidas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay salidas registradas
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($salidas as $salida): ?>
                                <tr>
                                    <td><?php echo $salida['id'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($salida['producto'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($salida['color']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($salida['color']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin color</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($salida['talla']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($salida['talla']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin talla</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $salida['cantidad'] ?? 'N/A'; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($salida['fecha'])); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst($salida['motivo'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($salida['beneficiario'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($salida['factura_remision'] ?? 'N/A'); ?></td>
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