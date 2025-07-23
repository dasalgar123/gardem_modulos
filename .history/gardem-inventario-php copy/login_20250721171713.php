<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    
    if (!empty($correo) && !empty($contraseña)) {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND contraseña = ?");
        $stmt->execute([$correo, $contraseña]);
        $usuario = $stmt->fetch();
        
        if ($usuario && ($usuario['rol'] === 'administrador' || $usuario['rol'] === 'almacenista')) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Credenciales incorrectas o sin permisos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); padding: 40px; width: 100%; max-width: 400px; }
        .btn-login { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); border: none; padding: 12px; font-weight: 600; width: 100%; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h2>Sistema de Inventario</h2>
            <p class="text-muted">GARDEM - Almacenista</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="mt-3 text-center">
            <small class="text-muted">Usa tus credenciales existentes</small>
        </div>
    </div>
</body>
</html> 