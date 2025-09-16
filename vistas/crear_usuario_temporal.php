<?php
/**
 * Formulario temporal para crear usuarios sin verificación de permisos
 * SOLO PARA RESOLVER EL PROBLEMA INICIAL DE ACCESO
 */

session_start();

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conectar a la base de datos
        $db = new PDO(
            "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
            "c2810161_iniciad",
            "li62veMAdu",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'usuario';

        // Validaciones básicas
        if (empty($username) || empty($password) || empty($nombre) || empty($apellido) || empty($email)) {
            throw new Exception('Todos los campos son obligatorios');
        }

        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email no es válido');
        }

        // Verificar si el username ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('El nombre de usuario ya existe');
        }

        // Verificar si el email ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('El email ya está registrado');
        }

        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $db->prepare("
            INSERT INTO usuarios (username, password_hash, nombre, apellido, email, role, is_active, is_superuser) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?)
        ");
        
        $is_superuser = ($role === 'admin') ? 1 : 0;
        
        $stmt->execute([
            $username,
            $passwordHash,
            $nombre,
            $apellido,
            $email,
            $role,
            $is_superuser
        ]);

        $mensaje = "Usuario '{$username}' creado exitosamente con rol '{$role}'";
        $tipo_mensaje = 'success';

    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Modo Temporal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Crear Usuario - Modo Temporal
                        </h4>
                        <small>Este formulario bypasea la verificación de permisos temporalmente</small>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
                                <?= e($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">
                                            <i class="bi bi-person"></i> Nombre de Usuario *
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= e($_POST['username'] ?? '') ?>" required>
                                        <div class="form-text">Solo letras, números y guiones bajos</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="bi bi-lock"></i> Contraseña *
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="form-text">Mínimo 6 caracteres</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">
                                            <i class="bi bi-person-badge"></i> Nombre *
                                        </label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= e($_POST['nombre'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido" class="form-label">
                                            <i class="bi bi-person-badge"></i> Apellido *
                                        </label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               value="<?= e($_POST['apellido'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= e($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">
                                    <i class="bi bi-shield-check"></i> Rol del Usuario *
                                </label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Seleccionar rol...</option>
                                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                        Administrador - Acceso total al sistema
                                    </option>
                                    <option value="usuario" <?= ($_POST['role'] ?? '') === 'usuario' ? 'selected' : '' ?>>
                                        Usuario - Gestión de expedientes
                                    </option>
                                    <option value="consulta" <?= ($_POST['role'] ?? '') === 'consulta' ? 'selected' : '' ?>>
                                        Consulta - Solo búsquedas y visualización
                                    </option>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-person-plus"></i> Crear Usuario
                                </button>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Ir al Login
                                </a>
                            </div>
                        </form>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Información de Roles:</h6>
                                <ul class="mb-0">
                                    <li><strong>Administrador:</strong> Puede crear usuarios, gestionar todo el sistema</li>
                                    <li><strong>Usuario:</strong> Puede crear y gestionar expedientes</li>
                                    <li><strong>Consulta:</strong> Solo puede buscar y visualizar expedientes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <div class="btn-group" role="group">
                        <a href="crear_admin_emergencia.php" class="btn btn-outline-warning">
                            <i class="bi bi-tools"></i> Crear Admin de Emergencia
                        </a>
                        <a href="diagnostico_sesion.php" class="btn btn-outline-info">
                            <i class="bi bi-search"></i> Verificar Sesión
                        </a>
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>