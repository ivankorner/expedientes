<?php
session_start();

// Mostrar información de la sesión para diagnóstico
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="bi bi-info-circle"></i> Diagnóstico de Sesión</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($_SESSION)): ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle"></i> No hay sesión activa</h5>
                                <p>No tienes una sesión iniciada. Necesitas hacer login primero.</p>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle"></i> Sesión Activa</h5>
                                <p>Tienes una sesión iniciada correctamente.</p>
                            </div>
                            
                            <h6><i class="bi bi-person"></i> Información del Usuario:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <td><strong>ID de Usuario:</strong></td>
                                        <td><?= isset($_SESSION['usuario_id']) ? htmlspecialchars($_SESSION['usuario_id']) : '<span class="text-danger">No definido</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nombre de Usuario:</strong></td>
                                        <td><?= isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : '<span class="text-danger">No definido</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rol:</strong></td>
                                        <td>
                                            <?php if (isset($_SESSION['rol'])): ?>
                                                <span class="badge bg-<?= $_SESSION['rol'] === 'admin' ? 'danger' : ($_SESSION['rol'] === 'usuario' ? 'success' : 'info') ?>">
                                                    <?= htmlspecialchars($_SESSION['rol']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-danger">No definido</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <h6><i class="bi bi-list"></i> Todas las variables de sesión:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <?php foreach ($_SESSION as $key => $value): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($key) ?></code></td>
                                        <td><?= htmlspecialchars(is_string($value) ? $value : json_encode($value)) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <h6><i class="bi bi-shield-check"></i> Verificación de Permisos:</h6>
                                <?php
                                $puedeCrearUsuarios = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
                                ?>
                                
                                <div class="alert alert-<?= $puedeCrearUsuarios ? 'success' : 'danger' ?>">
                                    <?php if ($puedeCrearUsuarios): ?>
                                        <i class="bi bi-check-circle"></i> Tienes permisos para crear usuarios.
                                        <div class="mt-2">
                                            <a href="crear_usuario.php" class="btn btn-success">
                                                <i class="bi bi-person-plus"></i> Ir a Crear Usuario
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle"></i> No tienes permisos para crear usuarios. 
                                        Solo los administradores pueden crear usuarios.
                                        <?php if (isset($_SESSION['rol'])): ?>
                                            <br><small>Tu rol actual es: <strong><?= htmlspecialchars($_SESSION['rol']) ?></strong></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h6><i class="bi bi-gear"></i> Acciones:</h6>
                            <div class="btn-group" role="group">
                                <a href="dashboard.php" class="btn btn-primary">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a>
                                <a href="logout.php" class="btn btn-outline-danger">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>