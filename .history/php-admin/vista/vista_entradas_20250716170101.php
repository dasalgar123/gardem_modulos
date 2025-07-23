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
            <button type="button" class="btn btn-primary" onclick="mostrarFormularioEntrada()">
                <i class="fas fa-plus"></i> Nueva Entrada
            </button>
        </div>
        
        <div class="entrada-content content-entradas">
            <div class="entrada-card">
                <h2><i class="fas fa-list"></i> Listado de Entradas</h2>
              
                <div class="entradas-container">
                    <table class="entradas-table">
                        <thead>
                            <tr>
                                <th>Entrada</th>
                                <th>Detalles</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Beneficiario</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entradas as $entrada): ?>
                            <tr>
                                <td>
                                    <div class="entrada-info">
                                        <div class="entrada-avatar">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <div>
                                            <div class="entrada-product"><?php echo htmlspecialchars($entrada['producto']); ?></div>
                                            <div class="entrada-id">ID: <?php echo htmlspecialchars($entrada['id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="entrada-details">
                                        <div class="entrada-quantity">
                                            <i class="fas fa-boxes"></i> <?php echo htmlspecialchars($entrada['cantidad']); ?> unidades
                                        </div>
                                        <div class="entrada-id">
                                            <i class="fas fa-tag"></i> Prod. ID: <?php echo htmlspecialchars($entrada['producto_id']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="entrada-date">
                                        <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($entrada['fecha']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="entrada-reason">
                                        <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($entrada['motivo']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="entrada-beneficiary">
                                        <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($entrada['beneficiario_tipo']); ?></div>
                                        <div><i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($entrada['beneficiario_id']); ?></div>
                                        <div><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($entrada['beneficiario']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="entrada-invoice">
                                        <i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($entrada['factura_remision']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div style="padding: 2rem; color: #666;">
                                        <i class="fas fa-arrow-down" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                        No hay entradas registradas
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