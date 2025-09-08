<?php
session_start();

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

try {
    // Conectar a la base de datos
  // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $db->query("SELECT id, username, nombre, apellido, email, role, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración de Usuarios - Sistema de Expedientes</title>
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
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(70, 89, 125, 0.08);
            overflow: hidden;
        }
        
        .btn-action {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 0 2px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .role-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
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
                                    <i class="bi bi-people-fill me-2"></i>Administración de Usuarios
                                </h1>
                                <p class="mb-0 opacity-75">Gestiona los usuarios del sistema de expedientes</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="crear_usuario.php" class="btn btn-light btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <h3 class="mb-1"><?= count($usuarios) ?></h3>
                            <p class="mb-0 opacity-75">Total Usuarios</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="bi bi-shield-check fs-1 mb-2"></i>
                            <h3 class="mb-1"><?= count(array_filter($usuarios, fn($u) => $u['role'] === 'admin')) ?></h3>
                            <p class="mb-0 opacity-75">Administradores</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="bi bi-person-workspace fs-1 mb-2"></i>
                            <h3 class="mb-1"><?= count(array_filter($usuarios, fn($u) => $u['role'] === 'usuario')) ?></h3>
                            <p class="mb-0 opacity-75">Usuarios</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="bi bi-calendar-plus fs-1 mb-2"></i>
                            <h3 class="mb-1"><?= count(array_filter($usuarios, fn($u) => date('Y-m-d', strtotime($u['fecha_creacion'])) === date('Y-m-d'))) ?></h3>
                            <p class="mb-0 opacity-75">Nuevos Hoy</p>
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

                <!-- Tabla de usuarios -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 py-3">
                                        <i class="bi bi-person me-2"></i>Usuario
                                    </th>
                                    <th class="border-0 py-3">
                                        <i class="bi bi-card-text me-2"></i>Nombre Completo
                                    </th>
                                    <th class="border-0 py-3">
                                        <i class="bi bi-envelope me-2"></i>Email
                                    </th>
                                    <th class="border-0 py-3">
                                        <i class="bi bi-shield me-2"></i>Rol
                                    </th>
                                    <th class="border-0 py-3">
                                        <i class="bi bi-calendar me-2"></i>Fecha Creación
                                    </th>
                                    <th class="border-0 py-3 text-center">
                                        <i class="bi bi-gear me-2"></i>Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        <?= strtoupper(substr(e($usuario['nombre']), 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= e($usuario['username']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <?= e($usuario['apellido'] . ', ' . $usuario['nombre']) ?>
                                            </td>
                                            <td class="py-3">
                                                <i class="bi bi-envelope-fill text-muted me-2"></i>
                                                <?= e($usuario['email']) ?>
                                            </td>
                                            <td class="py-3">
                                                <?php
                                                $roleBadgeClass = $usuario['role'] === 'admin' ? 'bg-danger' : 'bg-primary';
                                                $roleIcon = $usuario['role'] === 'admin' ? 'shield-fill-check' : 'person-fill';
                                                ?>
                                                <span class="role-badge badge <?= $roleBadgeClass ?>">
                                                    <i class="bi bi-<?= $roleIcon ?> me-1"></i>
                                                    <?= ucfirst(e($usuario['role'])) ?>
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    <?= date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) ?>
                                                </small>
                                            </td>
                                            <td class="py-3 text-center">
                                                <a href="crear_usuario.php?id=<?= $usuario['id'] ?>" 
                                                   class="btn btn-outline-primary btn-action" 
                                                   title="Editar usuario">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="eliminar_usuario.php?id=<?= $usuario['id'] ?>" 
                                                   class="btn btn-outline-danger btn-action" 
                                                   title="Eliminar usuario"
                                                   onclick="return confirm('¿Está seguro que desea eliminar este usuario?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-people fs-1 mb-3"></i>
                                                <h5>No hay usuarios registrados</h5>
                                                <p>Crea el primer usuario del sistema</p>
                                                <a href="crear_usuario.php" class="btn btn-primary">
                                                    <i class="bi bi-person-plus me-2"></i>Crear Usuario
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer con información adicional -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="mb-2">
                                            <i class="bi bi-info-circle me-2"></i>Información
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            Total de usuarios activos en el sistema de expedientes
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-2">
                                            <i class="bi bi-shield-check me-2"></i>Permisos
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            Solo administradores pueden gestionar usuarios
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-2">
                                            <i class="bi bi-question-circle me-2"></i>Ayuda
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            ¿Necesitas ayuda? Contacta al soporte técnico
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

