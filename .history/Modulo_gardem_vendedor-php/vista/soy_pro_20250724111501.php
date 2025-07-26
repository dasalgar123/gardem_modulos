<?php
/**
 * Página: Soy Pro
 * Módulo Vendedor - Sistema Gardem
 */

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?page=login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soy Pro - Sistema de Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h1 class="text-center">
                            <i class="fas fa-crown me-2"></i>
                            Soy Pro
                        </h1>
                    </div>
                    <div class="card-body text-center">
                        <h2>¡Eres Pro!</h2>
                        <p class="lead">Tienes acceso a todas las funcionalidades del sistema.</p>
                        <div class="mt-4">
                            <a href="index.php?page=ventas" class="btn btn-success me-2">
                                <i class="fas fa-shopping-cart"></i> Ventas
                            </a>
                            <a href="index.php?page=pedidos" class="btn btn-info me-2">
                                <i class="fas fa-list"></i> Pedidos
                            </a>
                            <a href="index.php?page=inventario" class="btn btn-warning me-2">
                                <i class="fas fa-boxes"></i> Inventario
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 