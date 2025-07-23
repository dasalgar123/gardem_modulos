<?php
session_start();

// Si ya está logueado, redirigir al panel principal
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

// Incluir configuración de base de datos
require_once 'config/database_existing.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    
    if (!empty($correo) && !empty($contraseña)) {
        try {
            // Autenticar usuario con la estructura existente
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND contraseña = ?");
            $stmt->execute([$correo, $contraseña]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Verificar si es almacenista o administrador
                if ($usuario['rol'] === 'almacenista' || $usuario['rol'] === 'administrador') {
                    // Crear sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_correo'] = $usuario['correo'];
                    $_SESSION['usuario_rol'] = $usuario['rol'];
                    
                    // Redirigir al panel principal
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'No tienes permisos para acceder al sistema de almacenista.';
                }
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexión: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 40px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .system-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }
        .system-info h6 {
            color: #495057;
            margin-bottom: 10px;
        }
        .system-info ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-warehouse fa-3x mb-3"></i>
            <h2>Sistema de Inventario</h2>
            <p>GARDEM - Módulo Almacenista</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-floating">
                    <input type="email" class="form-control" id="correo" name="correo" 
                           placeholder="correo@ejemplo.com" required 
                           value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>">
                    <label for="correo">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="contraseña" name="contraseña" 
                           placeholder="Contraseña" required>
                    <label for="contraseña">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="system-info">
                <h6><i class="fas fa-info-circle"></i> Información del Sistema</h6>
                <ul>
                    <li>Acceso solo para almacenistas y administradores</li>
                    <li>Gestión completa de inventario</li>
                    <li>Control de entregas y movimientos</li>
                    <li>Reportes en tiempo real</li>
                </ul>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt"></i> Sistema Seguro
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus en el primer campo
        document.getElementById('correo').focus();
        
        // Mostrar/ocultar contraseña
        document.getElementById('contraseña').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html> 