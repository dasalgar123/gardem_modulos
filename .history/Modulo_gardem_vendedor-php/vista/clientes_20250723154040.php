<?php
// Página de Clientes

// Procesar formulario de nuevo cliente
if (isset($_POST['agregar_cliente'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contraseña = trim($_POST['contraseña']);
    $rol = $_POST['rol'];
    
    // Validaciones básicas
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($correo)) $errores[] = "El correo es obligatorio";
    if (empty($contraseña)) $errores[] = "La contraseña es obligatoria";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido";
    
    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) {
        $errores[] = "El correo ya está registrado";
    }
    
    if (empty($errores)) {
        // Insertar nuevo cliente
        $stmt = $pdo->prepare("INSERT INTO cliente (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $correo, $contraseña, $rol])) {
            $mensaje_exito = "Cliente agregado exitosamente";
        } else {
            $errores[] = "Error al guardar el cliente";
        }
    }
}

// Mostrar formulario de nuevo cliente
if (isset($_GET['action']) && $_GET['action'] == 'nuevo') {
    ?>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Nuevo Cliente
                </h1>
                <a href="index.php?page=clientes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($mensaje_exito)): ?>
        <div class="alert alert-success">
            <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contraseña" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol *</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="cliente" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="vendedor" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                                    <option value="administrador" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="agregar_cliente" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Guardar Cliente
                                </button>
                                <a href="index.php?page=clientes" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    return; // Salir aquí para no mostrar la lista
}

// Procesar edición de cliente
if (isset($_POST['editar_cliente'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contraseña = trim($_POST['contraseña']);
    $rol = $_POST['rol'];
    
    // Validaciones básicas
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($correo)) $errores[] = "El correo es obligatorio";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido";
    
    // Verificar si el correo ya existe (excluyendo el cliente actual)
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE correo = ? AND id != ?");
    $stmt->execute([$correo, $id]);
    if ($stmt->fetch()) {
        $errores[] = "El correo ya está registrado por otro cliente";
    }
    
    if (empty($errores)) {
        // Actualizar cliente
        if (!empty($contraseña)) {
            // Si se proporciona nueva contraseña
            $stmt = $pdo->prepare("UPDATE cliente SET nombre = ?, correo = ?, contraseña = ?, rol = ? WHERE id = ?");
            $resultado = $stmt->execute([$nombre, $correo, $contraseña, $rol, $id]);
        } else {
            // Si no se cambia la contraseña
            $stmt = $pdo->prepare("UPDATE cliente SET nombre = ?, correo = ?, rol = ? WHERE id = ?");
            $resultado = $stmt->execute([$nombre, $correo, $rol, $id]);
        }
        
        if ($resultado) {
            $mensaje_exito = "Cliente actualizado exitosamente";
        } else {
            $errores[] = "Error al actualizar el cliente";
        }
    }
}

// Mostrar formulario de editar cliente
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obtener datos del cliente
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        echo "<div class='alert alert-danger'>Cliente no encontrado</div>";
        return;
    }
    ?>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Cliente
                </h1>
                <a href="index.php?page=clientes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($mensaje_exito)): ?>
        <div class="alert alert-success">
            <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($cliente['nombre']); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($cliente['correo']); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contraseña" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="contraseña" name="contraseña" 
                                       placeholder="Dejar vacío para mantener la actual">
                                <small class="text-muted">Deja vacío si no quieres cambiar la contraseña</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol *</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol</option>
                                    <option value="cliente" <?php echo $cliente['rol'] == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="vendedor" <?php echo $cliente['rol'] == 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                                    <option value="administrador" <?php echo $cliente['rol'] == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="editar_cliente" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Cliente
                                </button>
                                <a href="index.php?page=clientes" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    return; // Salir aquí para no mostrar la lista
}

// Obtener filtros
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$rol_filtro = isset($_GET['rol']) ? $_GET['rol'] : '';

// Construir consulta
$where_conditions = [];
$params = [];

if ($busqueda) {
    $where_conditions[] = "(nombre LIKE ? OR correo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($rol_filtro) {
    $where_conditions[] = "rol = ?";
    $params[] = $rol_filtro;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(' AND ', $where_conditions);
}

// Obtener clientes
$sql = "SELECT * FROM cliente $where_clause ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

// Obtener estadísticas por rol
$stmt = $pdo->prepare("SELECT rol, COUNT(*) as total FROM cliente GROUP BY rol");
$stmt->execute();
$stats_por_rol = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-users me-2"></i>
                Clientes
            </h1>
            <a href="index.php?page=clientes&action=nuevo" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>
                Nuevo Cliente
            </a>
        </div>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="clientes">
                    
                    <div class="col-md-4">
                        <label for="rol" class="form-label">Filtrar por rol</label>
                        <select name="rol" id="rol" class="form-select">
                            <option value="">Todos los roles</option>
                            <option value="cliente" <?php echo $rol_filtro == 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                            <option value="vendedor" <?php echo $rol_filtro == 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                            <option value="administrador" <?php echo $rol_filtro == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="buscar" class="form-label">Buscar cliente</label>
                        <input type="text" name="buscar" id="buscar" class="form-control" 
                               value="<?php echo htmlspecialchars($busqueda); ?>" 
                               placeholder="Buscar por nombre o correo...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas por rol -->
<div class="row mb-4">
    <?php foreach ($stats_por_rol as $stat): ?>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?php echo ucfirst($stat['rol']); ?>
                            </div>
                            <div class="h50t-weight-bold text-gray-800">
                                <?php echo $stat['total']; ?> clientes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Lista de clientes -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0t-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>
                    Lista de Clientes (<?php echo count($clientes); ?>)
                </h6>
            </div>
            <div class="card-body">                <?php if (empty($clientes)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No se encontraron clientes</h5>
                        <p>Intenta cambiar los filtros de búsqueda</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?php echo $cliente['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($cliente['correo']); ?>">
                                                <?php echo htmlspecialchars($cliente['correo']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $rol_class = '';
                                            switch($cliente['rol']) {
                                                case 'administrador':
                                                    $rol_class = 'bg-danger';
                                                    break;
                                                case 'vendedor':
                                                    $rol_class = 'bg-warning';
                                                    break;
                                                case 'cliente':
                                                    $rol_class = 'bg-info';
                                                    break;
                                                default:
                                                    $rol_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $rol_class; ?>">
                                                <?php echo ucfirst($cliente['rol']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="index.php?page=clientes&action=ver&id=<?php echo $cliente['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="index.php?page=clientes&action=editar&id=<?php echo $cliente['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmarEliminar(<?php echo $cliente['id']; ?>)" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este cliente?')) {
        window.location.href = 'index.php?page=clientes&action=eliminar&id=' + id;
    }
}
</script> 