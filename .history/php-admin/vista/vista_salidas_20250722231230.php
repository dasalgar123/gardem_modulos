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
                                <th>Salida</th>
                                <th>Detalles</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Destinatario</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salidas as $salida): ?>
                            <tr>
                                <td>
                                    <div class="salida-info">
                                        <div class="salida-avatar">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                        <div>
                                            <div class="salida-product"><?php echo htmlspecialchars($salida['producto']); ?></div>
                                            <div class="salida-id">ID: <?php echo htmlspecialchars($salida['id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="salida-details">
                                        <div class="salida-quantity">
                                            <i class="fas fa-boxes"></i> <?php echo htmlspecialchars($salida['cantidad']); ?> unidades
                                        </div>
                                        <div class="salida-id">
                                            <i class="fas fa-tag"></i> Prod. ID: <?php echo htmlspecialchars($salida['producto_id']); ?>
                                        </div>
                                        <?php if (!empty($salida['color'])): ?>
                                        <div class="salida-color">
                                            <i class="fas fa-palette"></i> Color: <?php echo htmlspecialchars($salida['color']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($salida['talla'])): ?>
                                        <div class="salida-size">
                                            <i class="fas fa-ruler"></i> Talla: <?php echo htmlspecialchars($salida['talla']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="salida-date">
                                        <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($salida['fecha']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="salida-reason">
                                        <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($salida['motivo']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="salida-destinatario">
                                        <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($salida['beneficiario']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="salida-invoice">
                                        <i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($salida['factura_remision']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($salidas)): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div style="padding: 2rem; color: #666;">
                                        <i class="fas fa-arrow-up" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                        No hay salidas registradas
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 