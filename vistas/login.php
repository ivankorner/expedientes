<?php
session_start();

$error = '';

// Si ya está logueado, redirige al dashboard
// Corregido: La ruta es simplemente 'dashboard.php' porque ambos archivos están en la misma carpeta 'vistas'.
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php'); // RUTA CORREGIDA AQUÍ
    exit;
}

require_once __DIR__ . '/../db/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();
    if ($user && password_verify($contrasena, $user['password_hash'])) {
        $_SESSION['usuario'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../publico/css/estilos.css">
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="main-box login w-100" style="max-width: 400px;">
            <div class="login-logo">
                <img src="../publico/imagen/LOGOCDE.png" alt="Logo Consejo">
            </div>
            <div class="login-title">Sistema de Expedientes</div>
            <div class="login-subtext mb-4">Ingrese su usuario y contraseña para acceder</div>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form action="" method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login mt-3 w-100">
                    <i class="bi bi-door-open-fill"></i> Ingresar
                </button>
                
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>