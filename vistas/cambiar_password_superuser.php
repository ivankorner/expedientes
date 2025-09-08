<?php
session_start();

// Verificar que el usuario esté logueado y sea superuser
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superuser') {
    $_SESSION['mensaje'] = 'Acceso denegado. Solo el Super Usuario puede acceder a esta página.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener información del super usuario
    $stmt = $db->prepare('SELECT username, nombre, apellido, email FROM usuarios WHERE id = ? AND is_superuser = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $superuser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$superuser) {
        $_SESSION['mensaje'] = 'Error: Usuario no encontrado o no es super usuario.';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error de conexión: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña de Super Usuario - Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .security-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(220, 53, 69, 0.15);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 2px solid rgba(220, 53, 69, 0.1);
        }
        
        .security-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }
        
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .btn-security {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-security:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            color: white;
        }
        
        .superuser-badge {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
             
            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 py-4">
                <!-- Header de la página -->
                <div class="page-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="mb-1">
                                    <i class="bi bi-shield-fill-exclamation me-2"></i>
                                    Cambiar Contraseña de Super Usuario
                                </h1>
                                <p class="mb-0 opacity-75">
                                    Configuración de seguridad del administrador principal del sistema
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="superuser-badge">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Super Usuario
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de estado -->
                <?php if (!empty($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= e($_SESSION['tipo_mensaje'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $_SESSION['tipo_mensaje'] === 'success' ? 'check-circle' : ($_SESSION['tipo_mensaje'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                        <?= e($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Advertencia de seguridad -->
                        <div class="security-warning">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-2"></i>
                                <div>
                                    <h5 class="mb-1 text-warning">⚠️ Área de Máxima Seguridad</h5>
                                    <p class="mb-0 text-dark">
                                        Está accediendo a la configuración del Super Usuario. Esta es la única forma de cambiar 
                                        la contraseña del administrador principal del sistema. Mantenga esta información segura.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información del usuario -->
                        <div class="security-container">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Usuario Actual</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="bi bi-person-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0"><?= e($superuser['apellido'] . ', ' . $superuser['nombre']) ?></h5>
                                            <small class="text-muted">@<?= e($superuser['username']) ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Email</h6>
                                    <p class="mb-0">
                                        <i class="bi bi-envelope me-2"></i>
                                        <?= e($superuser['email']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Formulario de cambio de contraseña -->
                            <form method="post" action="procesar_cambio_password_superuser.php" id="passwordForm">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-lock me-1"></i>
                                            Contraseña Actual
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               name="current_password" 
                                               placeholder="Ingrese su contraseña actual"
                                               required
                                               autocomplete="current-password">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-lock-fill me-1"></i>
                                            Nueva Contraseña
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               name="new_password" 
                                               id="newPassword"
                                               placeholder="Ingrese la nueva contraseña"
                                               required
                                               autocomplete="new-password"
                                               minlength="8">
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <small class="form-text text-muted">
                                            Mínimo 8 caracteres, incluya mayúsculas, minúsculas, números y símbolos
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-check2-square me-1"></i>
                                            Confirmar Nueva Contraseña
                                        </label>
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               name="confirm_password" 
                                               id="confirmPassword"
                                               placeholder="Confirme la nueva contraseña"
                                               required
                                               autocomplete="new-password">
                                        <div class="invalid-feedback" id="passwordMatch"></div>
                                    </div>
                                </div>
                                
                                <!-- Botones -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-security">
                                        <i class="bi bi-shield-check me-2"></i>
                                        Cambiar Contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const form = document.getElementById('passwordForm');

            // Validador de fuerza de contraseña
            newPassword.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                passwordStrength.className = 'password-strength strength-' + strength.level;
                passwordStrength.style.width = strength.percentage + '%';
            });

            // Validador de coincidencia de contraseñas
            confirmPassword.addEventListener('input', function() {
                if (this.value !== newPassword.value) {
                    this.classList.add('is-invalid');
                    passwordMatch.textContent = 'Las contraseñas no coinciden';
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    passwordMatch.textContent = '';
                }
            });

            // Validación del formulario
            form.addEventListener('submit', function(e) {
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
                
                if (newPassword.value.length < 8) {
                    e.preventDefault();
                    alert('La nueva contraseña debe tener al menos 8 caracteres');
                    return false;
                }
                
                const strength = checkPasswordStrength(newPassword.value);
                if (strength.level === 'weak') {
                    const confirmed = confirm('La contraseña es débil. ¿Está seguro de que desea continuar?');
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            function checkPasswordStrength(password) {
                let score = 0;
                
                if (password.length >= 8) score += 25;
                if (password.match(/[a-z]/)) score += 25;
                if (password.match(/[A-Z]/)) score += 25;
                if (password.match(/[0-9]/)) score += 15;
                if (password.match(/[^a-zA-Z0-9]/)) score += 10;
                
                if (score < 50) return { level: 'weak', percentage: score };
                if (score < 80) return { level: 'medium', percentage: score };
                return { level: 'strong', percentage: 100 };
            }
        });
    </script>
</body>
</html>
