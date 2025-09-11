<?php
session_start();

// Incluir el middleware de verificación de permisos
require_once __DIR__ . '/verificar_permisos.php';

// Verificar que el usuario tenga permisos para acceder a esta vista
verificarPermisoVista('crear_usuario.php');

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $isEdit = false;
    $user = ['id'=>'','username'=>'','nombre'=>'','apellido'=>'','email'=>'','role'=>'usuario'];
    
    if (!empty($_GET['id'])) {
        $isEdit = true;
        $id = (int)$_GET['id'];
        $stmt = $db->prepare('SELECT id, username, nombre, apellido, email, role FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $_SESSION['mensaje'] = 'Usuario no encontrado';
            $_SESSION['tipo_mensaje'] = 'warning';
            header('Location: listar_usuarios.php'); 
            exit;
        }
    }

    // CSRF token
    if (empty($_SESSION['csrf_token'])) { 
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); 
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
    <title><?= $isEdit ? 'Editar' : 'Crear' ?> Usuario - Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(70, 89, 125, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            border-left: 4px solid #667eea;
            padding-left: 1rem;
            margin-bottom: 2rem;
        }
        
        .form-section h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .user-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .role-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 0.5rem;
        }
        
        .btn-action {
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            margin: 0 0.5rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
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
                            <div class="col-md-6">
                                <h1 class="mb-1">
                                    <i class="bi bi-<?= $isEdit ? 'person-gear' : 'person-plus' ?> me-2"></i>
                                    <?= $isEdit ? 'Editar' : 'Crear' ?> Usuario
                                </h1>
                                <p class="mb-0 opacity-75">
                                    <?= $isEdit ? 'Modifica la información del usuario' : 'Agrega un nuevo usuario al sistema' ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="listar_usuarios.php" class="btn btn-light btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Volver a Usuarios
                                </a>
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

                <div class="row">
                    <!-- Formulario -->
                    <div class="col-lg-8">
                        <div class="form-container">
                            <form method="post" action="procesar_usuario.php" id="userForm">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                                
                                <!-- Información Básica -->
                                <div class="form-section">
                                    <h5><i class="bi bi-person-circle me-2"></i>Información Básica</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field">Nombre de Usuario</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-person"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="username" 
                                                       value="<?= e($user['username']) ?>"
                                                       placeholder="Ej: jperez"
                                                       required
                                                       autocomplete="username">
                                            </div>
                                            <div class="form-text">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Solo letras, números y guiones bajos
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-envelope"></i>
                                                </span>
                                                <input type="email" 
                                                       class="form-control" 
                                                       name="email" 
                                                       value="<?= e($user['email']) ?>"
                                                       placeholder="usuario@ejemplo.com"
                                                       required
                                                       autocomplete="email">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field">Apellido</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-person-badge"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="apellido" 
                                                       value="<?= e($user['apellido']) ?>"
                                                       placeholder="Apellido del usuario"
                                                       required
                                                       autocomplete="family-name">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field">Nombre</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-person-badge-fill"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="nombre" 
                                                       value="<?= e($user['nombre']) ?>"
                                                       placeholder="Nombre del usuario"
                                                       required
                                                       autocomplete="given-name">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permisos y Seguridad -->
                                <div class="form-section">
                                    <h5><i class="bi bi-shield-check me-2"></i>Permisos y Seguridad</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field">Rol del Usuario</label>
                                            <select name="role" class="form-select" required onchange="updateRoleInfo()">
                                                <option value="">Seleccionar rol...</option>
                                                <?php 
                                                // Solo mostrar el rol superuser si el usuario actual es superuser
                                                $currentUserRole = $_SESSION['user_role'] ?? '';
                                                $editingSuperuser = ($user['role'] === 'superuser');
                                                
                                                if ($currentUserRole === 'superuser' && !$editingSuperuser): ?>
                                                    <option value="superuser" <?= ($user['role']==='superuser')? 'selected':'' ?>>
                                                        Super Administrador
                                                    </option>
                                                <?php endif; ?>
                                                <option value="admin" <?= ($user['role']==='admin')? 'selected':'' ?>>
                                                    Administrador
                                                </option>
                                                <option value="usuario" <?= ($user['role']==='usuario')? 'selected':'' ?>>
                                                    Usuario
                                                </option>
                                                <option value="editor" <?= ($user['role']==='editor')? 'selected':'' ?>>
                                                    Editor
                                                </option>
                                                <option value="viewer" <?= ($user['role']==='viewer')? 'selected':'' ?>>
                                                    Solo Lectura
                                                </option>
                                                <?php if ($editingSuperuser): ?>
                                                    <option value="superuser" selected disabled>
                                                        Super Administrador (No modificable)
                                                    </option>
                                                    <input type="hidden" name="role" value="superuser">
                                                <?php endif; ?>
                                            </select>
                                            
                                            <div class="role-info mt-2" id="roleInfo">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Selecciona un rol para ver los permisos
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label<?= !$isEdit ? ' required-field' : '' ?>">
                                                Contraseña
                                                <?php if ($isEdit): ?>
                                                    <small class="text-muted">(dejar vacía para no cambiar)</small>
                                                <?php endif; ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                                <input type="password" 
                                                       class="form-control" 
                                                       name="password" 
                                                       placeholder="<?= $isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña segura' ?>"
                                                       <?= $isEdit ? '' : 'required' ?>
                                                       autocomplete="new-password"
                                                       minlength="6">
                                            </div>
                                            <?php if (!$isEdit): ?>
                                                <div class="form-text">
                                                    <i class="bi bi-shield-lock me-1"></i>
                                                    Mínimo 6 caracteres
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div>
                                        <small class="text-muted">
                                            <i class="bi bi-asterisk text-danger me-1" style="font-size: 0.7rem;"></i>
                                            Los campos marcados con * son obligatorios
                                        </small>
                                    </div>
                                    <div>
                                        <a href="listar_usuarios.php" class="btn btn-outline-secondary btn-action">
                                            <i class="bi bi-x-lg me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-action">
                                            <i class="bi bi-<?= $isEdit ? 'check-lg' : 'plus-lg' ?> me-2"></i>
                                            <?= $isEdit ? 'Actualizar Usuario' : 'Crear Usuario' ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Panel lateral con vista previa -->
                    <div class="col-lg-4">
                        <?php if ($isEdit && isset($user['role']) && $user['role'] === 'superuser'): ?>
                            <!-- Alerta especial para super usuario -->
                            <div class="alert alert-warning border-warning mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-4"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1">Super Usuario</h6>
                                        <small class="mb-0">
                                            Este usuario tiene control total del sistema. 
                                            Solo puede cambiar su contraseña desde dentro del sistema.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="user-preview">
                            <div class="user-avatar-large" id="userAvatar">
                                <?= !empty($user['nombre']) ? strtoupper(substr($user['nombre'], 0, 1)) : '?' ?>
                            </div>
                            <h4 id="userFullName">
                                <?= !empty($user['nombre']) && !empty($user['apellido']) 
                                    ? e($user['apellido'] . ', ' . $user['nombre']) 
                                    : 'Nuevo Usuario' ?>
                            </h4>
                            <p class="opacity-75" id="userEmail">
                                <i class="bi bi-envelope me-2"></i>
                                <?= !empty($user['email']) ? e($user['email']) : 'email@ejemplo.com' ?>
                            </p>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark fs-6" id="userRole">
                                    <i class="bi bi-shield me-1"></i>
                                    <?= !empty($user['role']) ? ucfirst(e($user['role'])) : 'Sin rol asignado' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-lightbulb me-2"></i>Consejos
                                </h6>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Usa nombres de usuario únicos y fáciles de recordar
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Las contraseñas deben ser seguras (mín. 6 caracteres)
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Asigna el rol apropiado según las responsabilidades
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Verifica que el email sea correcto para notificaciones
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar vista previa en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('userForm');
            const nombreInput = form.querySelector('input[name="nombre"]');
            const apellidoInput = form.querySelector('input[name="apellido"]');
            const emailInput = form.querySelector('input[name="email"]');
            const roleSelect = form.querySelector('select[name="role"]');

            function updatePreview() {
                const nombre = nombreInput.value.trim();
                const apellido = apellidoInput.value.trim();
                const email = emailInput.value.trim();

                // Actualizar avatar
                const avatar = document.getElementById('userAvatar');
                avatar.textContent = nombre ? nombre.charAt(0).toUpperCase() : '?';

                // Actualizar nombre completo
                const fullName = document.getElementById('userFullName');
                if (apellido && nombre) {
                    fullName.textContent = apellido + ', ' + nombre;
                } else if (nombre) {
                    fullName.textContent = nombre;
                } else {
                    fullName.textContent = 'Nuevo Usuario';
                }

                // Actualizar email
                const emailElement = document.getElementById('userEmail');
                emailElement.innerHTML = '<i class="bi bi-envelope me-2"></i>' + (email || 'email@ejemplo.com');
            }

            // Event listeners para actualización en tiempo real
            nombreInput.addEventListener('input', updatePreview);
            apellidoInput.addEventListener('input', updatePreview);
            emailInput.addEventListener('input', updatePreview);

            // Actualizar información del rol
            updateRoleInfo();
        });

        function updateRoleInfo() {
            const roleSelect = document.querySelector('select[name="role"]');
            const roleInfo = document.getElementById('roleInfo');
            const userRole = document.getElementById('userRole');
            const selectedRole = roleSelect.value;

            const roleDescriptions = {
                'superuser': {
                    description: 'Control total del sistema - No puede ser modificado por otros usuarios',
                    icon: 'shield-fill-exclamation',
                    class: 'text-danger fw-bold'
                },
                'admin': {
                    description: 'Acceso completo al sistema, puede gestionar usuarios y configuraciones',
                    icon: 'shield-fill-check',
                    class: 'text-danger'
                },
                'usuario': {
                    description: 'Puede gestionar expedientes y realizar operaciones básicas',
                    icon: 'person-fill',
                    class: 'text-primary'
                },
                'editor': {
                    description: 'Puede crear y modificar expedientes, sin acceso a administración',
                    icon: 'pencil-fill',
                    class: 'text-warning'
                },
                'viewer': {
                    description: 'Solo puede consultar expedientes, sin permisos de modificación',
                    icon: 'eye-fill',
                    class: 'text-info'
                }
            };

            if (selectedRole && roleDescriptions[selectedRole]) {
                const roleData = roleDescriptions[selectedRole];
                roleInfo.innerHTML = `
                    <small class="${roleData.class}">
                        <i class="bi bi-${roleData.icon} me-1"></i>
                        ${roleData.description}
                    </small>
                `;
                userRole.innerHTML = `
                    <i class="bi bi-${roleData.icon} me-1"></i>
                    ${selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}
                `;
            } else {
                roleInfo.innerHTML = `
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Selecciona un rol para ver los permisos
                    </small>
                `;
                userRole.innerHTML = `
                    <i class="bi bi-shield me-1"></i>
                    Sin rol asignado
                `;
            }
        }

        // Validación del formulario
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const username = this.querySelector('input[name="username"]').value.trim();
            const password = this.querySelector('input[name="password"]').value;
            const isEdit = this.querySelector('input[name="id"]').value !== '';

            if (username.length < 3) {
                e.preventDefault();
                alert('El nombre de usuario debe tener al menos 3 caracteres');
                return;
            }

            if (!isEdit && password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }

            if (isEdit && password.length > 0 && password.length < 6) {
                e.preventDefault();
                alert('Si cambias la contraseña, debe tener al menos 6 caracteres');
                return;
            }

            // Verificar si se está intentando editar un super usuario
            const roleSelect = this.querySelector('select[name="role"]');
            const isSuperuser = roleSelect.value === 'superuser' || roleSelect.querySelector('option[value="superuser"]:checked');
            if (isSuperuser && isEdit) {
                const confirmed = confirm('⚠️ ADVERTENCIA: Está modificando un Super Usuario.\n\nEste usuario tiene control total del sistema.\n¿Está seguro de continuar?');
                if (!confirmed) {
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>
