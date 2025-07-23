<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener estadísticas
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) FROM productos");
$stats['productos'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM inventario WHERE stock_actual <= stock_minimo");
$stats['stock_bajo'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM ventas_productos WHERE estado = 'pendiente_entrega'");
$stats['pendientes'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM movimientos");
$stats['movimientos'] = $stmt->fetchColumn();

// Obtener movimientos recientes
$stmt = $pdo->query("
    SELECT m.*, p.nombre as producto_nombre, u.nombre as usuario_nombre 
    FROM movimientos m 
    JOIN productos p ON m.producto_id = p.id 
    JOIN usuario u ON m.usuario_id = u.id 
    ORDER BY m.fecha DESC LIMIT 5
");
$movimientos = $stmt->fetchAll();

// Obtener ventas pendientes
$stmt = $pdo->query("
    SELECT v.*, vp.*, p.nombre as producto_nombre 
    FROM ventas v 
    JOIN ventas_productos vp ON v.id = vp.venta_id 
    JOIN productos p ON vp.producto_id = p.id 
    WHERE vp.estado = 'pendiente_entrega' 
    ORDER BY v.fecha DESC LIMIT 5
");
$ventas_pendientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - GARDEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stats-card { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border-radius: 15px; padding: 20px; }
        .btn-primary { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); border: none; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-warehouse"></i> GARDEM Inventario
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Menú</h5>
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action active">
                                <i class="fas fa-tachometer-alt"></i> Panel Principal
                            </a>
                            <a href="ventas.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart"></i> Ver Ventas
                            </a>
                            <a href="entregas.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-truck"></i> Gestionar Entregas
                            </a>
                            <a href="inventario.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-boxes"></i> Inventario
                            </a>
                            <a href="movimientos.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-exchange-alt"></i> Movimientos
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <h2 class="mb-4">Panel Principal - Almacenista</h2>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['productos']; ?></h3>
                            <p>Productos</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['stock_bajo']; ?></h3>
                            <p>Stock Bajo</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['pendientes']; ?></h3>
                            <p>Entregas Pendientes</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3><?php echo $stats['movimientos']; ?></h3>
                            <p>Movimientos</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Ventas Pendientes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clock"></i> Ventas Pendientes de Entrega</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ventas_pendientes)): ?>
                                    <p class="text-muted">No hay ventas pendientes de entrega.</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($ventas_pendientes as $venta): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($venta['cliente_nombre']); ?></strong>
                                                        <br>
                                                        <small><?php echo htmlspecialchars($venta['producto_nombre']); ?> - Cantidad: <?php echo $venta['cantidad']; ?></small>
                                                    </div>
                                                    <a href="entregas.php?id=<?php echo $venta['venta_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-truck"></i> Entregar
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Movimientos Recientes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Movimientos Recientes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($movimientos)): ?>
                                    <p class="text-muted">No hay movimientos recientes.</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($movimientos as $mov): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($mov['producto_nombre']); ?></strong>
                                                        <br>
                                                        <small><?php echo ucfirst($mov['tipo']); ?> - <?php echo $mov['cantidad']; ?> unidades</small>
                                                    </div>
                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 