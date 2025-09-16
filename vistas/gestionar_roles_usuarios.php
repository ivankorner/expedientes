<?php
/**
 * Gestión de Roles de Usuarios - Solo para Superuser
 * Permite al superuser cambiar los roles de otros usuarios del sistema
 */

session_start();

// Verificar que el usuario esté logueado y sea superuser
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superuser') {
    $_SESSION['mensaje'] = 'Solo los super administradores pueden acceder a esta página';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

require_once '../db/connection.php';

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$tipo_mensaje = '';

// Obtener todos los usuarios excepto el superuser actual
try {
    $stmt = $pdo->prepare("SELECT id, username, email, nombre, apellido, role, created_at, is_active 
                          FROM usuarios 
                          WHERE id != ? 
                          ORDER BY role DESC, username ASC");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuarios = $stmt->fetchAll();
} catch (Exception $e) {
    $mensaje = 'Error al obtener la lista de usuarios: ' . $e->getMessage();
    $tipo_mensaje = 'danger';
    $usuarios = [];
}

// Definir roles disponibles y sus descripciones
$rolesDisponibles = [
    'superuser' => [
        'nombre' => 'Super Administrador',
        'descripcion' => 'Acceso total al sistema incluyendo gestión de roles',
        'color' => 'danger',
        'icono' => 'shield-fill'
    ],
    'admin' => [
        'nombre' => 'Administrador',
        'descripcion' => 'Acceso completo excepto gestión de roles',
        'color' => 'warning',
        'icono' => 'shield-check'
    ],
    'usuario' => [
        'nombre' => 'Usuario',
        'descripcion' => 'Gestión de expedientes y reportes',
        'color' => 'success',
        'icono' => 'person-check'
    ],
    'editor' => [
        'nombre' => 'Editor',
        'descripcion' => 'Edición de expedientes existentes',
        'color' => 'info',
        'icono' => 'pencil-square'
    ],
    'viewer' => [
        'nombre' => 'Solo Lectura',
        'descripcion' => 'Consulta y visualización únicamente',
        'color' => 'secondary',
        'icono' => 'eye'
    ],
    'consulta' => [
        'nombre' => 'Consulta',
        'descripcion' => 'Búsquedas y consultas públicas',
        'color' => 'primary',
        'icono' => 'search'
    ]
];

// Contar usuarios por rol
$conteoRoles = [];
foreach ($usuarios as $usuario) {
    $rol = $usuario['role'];
    $conteoRoles[$rol] = ($conteoRoles[$rol] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Sistema de Expedientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .user-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .role-badge {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .role-selector {
            min-width: 200px;
        }
        
        .change-history {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .activity-item {
            border-left: 3px solid #6c757d;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .btn-change-role {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
             
            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <!-- Header de la página -->
                <div class="page-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="mb-1">
                                    <i class="bi bi-shield-fill-exclamation me-2"></i>
                                    Gestión de Roles de Usuarios
                                </h1>
                                <p class="mb-0 opacity-75">
                                    Control total sobre los permisos y roles del sistema
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="btn-group">
                                    <a href="listar_usuarios.php" class="btn btn-light">
                                        <i class="bi bi-people me-2"></i>Ver Usuarios
                                    </a>
                                    <a href="dashboard.php" class="btn btn-outline-light">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de estado -->
                <?php if (!empty($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?? 'info' ?> alert-dismissible fade show">
                        <i class="bi bi-<?= $_SESSION['tipo_mensaje'] === 'success' ? 'check-circle' : ($_SESSION['tipo_mensaje'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                        <?= e($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <!-- Estadísticas de Roles -->
                <div class="stats-card">
                    <h4 class="mb-3"><i class="bi bi-bar-chart me-2"></i>Distribución de Roles</h4>
                    <div class="row">
                        <?php foreach ($rolesDisponibles as $rol => $info): ?>
                            <div class="col-md-2 mb-3">
                                <div class="text-center">
                                    <div class="h2 mb-1">
                                        <span class="badge bg-<?= $info['color'] ?>">
                                            <?= $conteoRoles[$rol] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="small">
                                        <i class="bi bi-<?= $info['icono'] ?> me-1"></i>
                                        <?= e($info['nombre']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Lista de Usuarios -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="bi bi-people me-2"></i>Usuarios del Sistema</h4>
                            <span class="badge bg-secondary fs-6">
                                Total: <?= count($usuarios) ?> usuarios
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if (empty($usuarios)): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <h3 class="text-muted mt-3">No hay usuarios registrados</h3>
                                <p class="text-muted">Crea el primer usuario para comenzar.</p>
                                <a href="crear_usuario.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-2"></i>Crear Usuario
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="user-card card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="user-avatar bg-<?= $rolesDisponibles[$usuario['role']]['color'] ?? 'secondary' ?>">
                                                <?= strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1)) ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">
                                                    <?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
                                                </h5>
                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-person me-1"></i>@<?= e($usuario['username']) ?>
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-envelope me-1"></i><?= e($usuario['email']) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Rol Actual -->
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">ROL ACTUAL:</label>
                                            <div>
                                                <span class="role-badge bg-<?= $rolesDisponibles[$usuario['role']]['color'] ?? 'secondary' ?> text-white">
                                                    <i class="bi bi-<?= $rolesDisponibles[$usuario['role']]['icono'] ?? 'person' ?>"></i>
                                                    <?= e($rolesDisponibles[$usuario['role']]['nombre'] ?? ucfirst($usuario['role'])) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <?= e($rolesDisponibles[$usuario['role']]['descripcion'] ?? 'Sin descripción') ?>
                                            </small>
                                        </div>

                                        <!-- Selector de Nuevo Rol -->
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">CAMBIAR A:</label>
                                            <select class="form-select role-selector" 
                                                    data-user-id="<?= $usuario['id'] ?>"
                                                    data-current-role="<?= e($usuario['role']) ?>"
                                                    data-user-name="<?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                                <option value="">Seleccionar nuevo rol...</option>
                                                <?php foreach ($rolesDisponibles as $rol => $info): ?>
                                                    <?php if ($rol !== $usuario['role']): ?>
                                                        <option value="<?= e($rol) ?>" 
                                                                data-color="<?= $info['color'] ?>"
                                                                data-icon="<?= $info['icono'] ?>">
                                                            <?= e($info['nombre']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Botón de Cambio -->
                                        <button class="btn btn-warning btn-change-role w-100" 
                                                onclick="cambiarRol(<?= $usuario['id'] ?>, '<?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?>')"
                                                disabled>
                                            <i class="bi bi-arrow-repeat me-2"></i>
                                            Cambiar Rol
                                        </button>

                                        <!-- Información adicional -->
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <small class="text-muted">Estado</small>
                                                    <div>
                                                        <?php if ($usuario['is_active']): ?>
                                                            <span class="badge bg-success">Activo</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inactivo</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Registro</small>
                                                    <div class="small">
                                                        <?= date('d/m/Y', strtotime($usuario['created_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Información de Ayuda -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Información sobre Roles del Sistema
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($rolesDisponibles as $rol => $info): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="d-flex align-items-start">
                                                <span class="role-badge bg-<?= $info['color'] ?> text-white me-3">
                                                    <i class="bi bi-<?= $info['icono'] ?>"></i>
                                                </span>
                                                <div>
                                                    <h6 class="mb-1"><?= e($info['nombre']) ?></h6>
                                                    <small class="text-muted"><?= e($info['descripcion']) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Importante:</strong> Los cambios de rol son inmediatos y permanentes. 
                                    El usuario afectado deberá cerrar sesión e iniciar sesión nuevamente para que los cambios surtan efecto.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Habilitar/deshabilitar botón cuando se selecciona un rol
        document.querySelectorAll('.role-selector').forEach(select => {
            select.addEventListener('change', function() {
                const button = this.parentElement.nextElementSibling;
                button.disabled = !this.value;
                
                if (this.value) {
                    const option = this.options[this.selectedIndex];
                    const color = option.getAttribute('data-color');
                    button.className = `btn btn-${color} btn-change-role w-100`;
                }
            });
        });

        function cambiarRol(userId, userName) {
            const selector = document.querySelector(`[data-user-id="${userId}"]`);
            const nuevoRol = selector.value;
            const rolActual = selector.getAttribute('data-current-role');
            
            if (!nuevoRol) {
                Swal.fire('Error', 'Debe seleccionar un rol', 'error');
                return;
            }

            // Buscar información del nuevo rol
            const option = selector.options[selector.selectedIndex];
            const nombreRol = option.text;

            Swal.fire({
                title: '¿Confirmar cambio de rol?',
                html: `
                    <div class="text-start">
                        <p><strong>Usuario:</strong> ${userName}</p>
                        <p><strong>Rol actual:</strong> <span class="badge bg-secondary">${rolActual}</span></p>
                        <p><strong>Nuevo rol:</strong> <span class="badge bg-warning">${nombreRol}</span></p>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        El usuario deberá cerrar sesión e iniciar sesión nuevamente.
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cambiar rol',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear formulario y enviarlo
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'procesar_cambio_rol.php';
                    
                    const inputUserId = document.createElement('input');
                    inputUserId.type = 'hidden';
                    inputUserId.name = 'user_id';
                    inputUserId.value = userId;
                    
                    const inputNuevoRol = document.createElement('input');
                    inputNuevoRol.type = 'hidden';
                    inputNuevoRol.name = 'nuevo_rol';
                    inputNuevoRol.value = nuevoRol;
                    
                    const inputToken = document.createElement('input');
                    inputToken.type = 'hidden';
                    inputToken.name = 'csrf_token';
                    inputToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                    
                    form.appendChild(inputUserId);
                    form.appendChild(inputNuevoRol);
                    form.appendChild(inputToken);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Generar token CSRF si no existe
        <?php if (!isset($_SESSION['csrf_token'])): ?>
            <?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
        <?php endif; ?>
    </script>
</body>
</html>