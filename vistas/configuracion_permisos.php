<?php
/**
 * Configuración Avanzada de Permisos
 * Permite modificar y gestionar permisos de forma granular
 */

session_start();

// Verificar que solo administradores puedan acceder
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    $_SESSION['mensaje'] = 'Solo los administradores pueden acceder a esta página';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: login.php');
    exit;
}

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$tipo_mensaje = '';

// Configuración actual de permisos
$configuracionPermisos = [
    'superuser' => [
        'descripcion' => 'Super Administrador del Sistema',
        'color' => 'danger',
        'icono' => 'shield-fill-exclamation',
        'permisos' => [
            'crear_usuarios' => true,
            'gestionar_usuarios' => true,
            'eliminar_usuarios' => true,
            'cambiar_roles' => true,
            'crear_expedientes' => true,
            'editar_expedientes' => true,
            'eliminar_expedientes' => true,
            'ver_reportes' => true,
            'configurar_sistema' => true,
            'acceso_total' => true,
            'gestionar_roles' => true
        ]
    ],
    'admin' => [
        'descripcion' => 'Administrador del Sistema',
        'color' => 'warning',
        'icono' => 'shield-check',
        'permisos' => [
            'crear_usuarios' => true,
            'gestionar_usuarios' => true,
            'eliminar_usuarios' => true,
            'cambiar_roles' => false,
            'crear_expedientes' => true,
            'editar_expedientes' => true,
            'eliminar_expedientes' => true,
            'ver_reportes' => true,
            'configurar_sistema' => false,
            'acceso_total' => true,
            'gestionar_roles' => false
        ]
    ],
    'usuario' => [
        'descripcion' => 'Usuario de Gestión',
        'color' => 'success',
        'icono' => 'person-check',
        'permisos' => [
            'crear_usuarios' => false,
            'gestionar_usuarios' => false,
            'eliminar_usuarios' => false,
            'cambiar_roles' => false,
            'crear_expedientes' => true,
            'editar_expedientes' => true,
            'eliminar_expedientes' => false,
            'ver_reportes' => true,
            'configurar_sistema' => false,
            'acceso_limitado' => true,
            'gestionar_roles' => false
        ]
    ],
    'consulta' => [
        'descripcion' => 'Usuario de Consulta',
        'color' => 'info',
        'icono' => 'search',
        'permisos' => [
            'crear_usuarios' => false,
            'gestionar_usuarios' => false,
            'eliminar_usuarios' => false,
            'cambiar_roles' => false,
            'crear_expedientes' => false,
            'editar_expedientes' => false,
            'eliminar_expedientes' => false,
            'ver_reportes' => true,
            'configurar_sistema' => false,
            'solo_lectura' => true,
            'gestionar_roles' => false
        ]
    ]
];

// Mapeo de vistas a permisos específicos
$mapaVistasPermisos = [
    // Gestión de Usuarios
    'crear_usuario.php' => 'crear_usuarios',
    'listar_usuarios.php' => 'gestionar_usuarios',
    'eliminar_usuario.php' => 'eliminar_usuarios',
    'procesar_usuario.php' => 'gestionar_usuarios',
    
    // Gestión de Roles (exclusivo superuser)
    'gestionar_roles_usuarios.php' => 'cambiar_roles',
    'procesar_cambio_rol.php' => 'cambiar_roles',
    
    // Gestión de Expedientes
    'carga_expedientes.php' => 'crear_expedientes',
    'listar_expedientes.php' => 'ver_reportes',
    'actualizar_expedientes.php' => 'editar_expedientes',
    'eliminar_expediente.php' => 'eliminar_expedientes',
    'pases_expediente.php' => 'editar_expedientes',
    'editar_pase.php' => 'editar_expedientes',
    'eliminar_pase.php' => 'editar_expedientes',
    
    // Reportes y consultas
    'buscar_expediente.php' => 'ver_reportes',
    'busqueda_rapida.php' => 'ver_reportes',
    'consulta.php' => 'ver_reportes',
    'resultados.php' => 'ver_reportes',
    'pdf_auto_descarga.php' => 'ver_reportes',
    'generar_pdf_expediente.php' => 'ver_reportes',
    
    // Sistema
    'dashboard.php' => 'acceso_basico',
    'configuracion_permisos.php' => 'configurar_sistema',
    'verificacion_permisos.php' => 'configurar_sistema'
];

// Función para verificar si un rol tiene un permiso específico
function tienePermiso($rol, $permiso, $config) {
    return isset($config[$rol]['permisos'][$permiso]) && $config[$rol]['permisos'][$permiso];
}

// Función para obtener el estado de acceso a una vista
function obtenerEstadoAcceso($vista, $rol, $mapa, $config) {
    // Vistas públicas
    $vistasPublicas = ['login.php', 'logout.php', 'resultados_publico.php'];
    if (in_array($vista, $vistasPublicas)) {
        return ['estado' => 'publico', 'razon' => 'Vista pública'];
    }
    
    // Dashboard es accesible para todos los usuarios autenticados
    if ($vista === 'dashboard.php') {
        return ['estado' => 'permitido', 'razon' => 'Acceso básico'];
    }
    
    // Verificar permiso específico
    if (isset($mapa[$vista])) {
        $permisoRequerido = $mapa[$vista];
        if (tienePermiso($rol, $permisoRequerido, $config)) {
            return ['estado' => 'permitido', 'razon' => "Permiso: {$permisoRequerido}"];
        } else {
            return ['estado' => 'denegado', 'razon' => "Falta permiso: {$permisoRequerido}"];
        }
    }
    
    return ['estado' => 'indefinido', 'razon' => 'Vista no configurada'];
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'probar_acceso') {
        $vista = $_POST['vista'] ?? '';
        $rol = $_POST['rol'] ?? '';
        
        if ($vista && $rol) {
            $resultado = obtenerEstadoAcceso($vista, $rol, $mapaVistasPermisos, $configuracionPermisos);
            $mensaje = "Acceso a '{$vista}' para rol '{$rol}': {$resultado['estado']} ({$resultado['razon']})";
            $tipo_mensaje = $resultado['estado'] === 'permitido' ? 'success' : ($resultado['estado'] === 'denegado' ? 'danger' : 'warning');
        }
    }
}

// Obtener todas las vistas disponibles
$todasLasVistas = array_unique(array_merge(
    array_keys($mapaVistasPermisos),
    [
        'login.php', 'logout.php', 'dashboard.php', 'resultados_publico.php',
        'historial_expediente.php', 'obtener_expediente.php', 'obtener_historial.php'
    ]
));
sort($todasLasVistas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Permisos - Sistema de Expedientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .permiso-activo { background-color: #d1f2eb; }
        .permiso-inactivo { background-color: #fadbd8; }
        .card-rol { transition: transform 0.2s; }
        .card-rol:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-gear-fill"></i> Configuración de Permisos del Sistema</h2>
                    <div class="btn-group">
                        <a href="verificacion_permisos.php" class="btn btn-outline-primary">
                            <i class="bi bi-shield-check"></i> Ver Verificación
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </div>
                </div>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
                        <?= e($mensaje) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Configuración de Roles -->
        <div class="row mb-4">
            <div class="col-12">
                <h4><i class="bi bi-people-fill"></i> Configuración de Roles</h4>
            </div>
            <?php foreach ($configuracionPermisos as $rol => $config): ?>
            <div class="col-md-4 mb-3">
                <div class="card card-rol h-100">
                    <div class="card-header bg-<?= $config['color'] ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-<?= $config['icono'] ?>"></i> 
                            <?= strtoupper($rol) ?>
                        </h5>
                        <small><?= e($config['descripcion']) ?></small>
                    </div>
                    <div class="card-body">
                        <h6>Permisos:</h6>
                        <div class="row">
                            <?php foreach ($config['permisos'] as $permiso => $activo): ?>
                            <div class="col-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 rounded <?= $activo ? 'permiso-activo' : 'permiso-inactivo' ?>">
                                    <span class="small"><?= e(str_replace('_', ' ', ucfirst($permiso))) ?></span>
                                    <?php if ($activo): ?>
                                        <i class="bi bi-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle text-danger"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Probador de Acceso -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-play-circle"></i> Probador de Acceso</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="accion" value="probar_acceso">
                            <div class="col-md-5">
                                <label for="vista" class="form-label">Vista/Página:</label>
                                <select name="vista" id="vista" class="form-select" required>
                                    <option value="">Seleccionar vista...</option>
                                    <?php foreach ($todasLasVistas as $vista): ?>
                                        <option value="<?= e($vista) ?>" <?= ($_POST['vista'] ?? '') === $vista ? 'selected' : '' ?>>
                                            <?= e($vista) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rol" class="form-label">Rol:</label>
                                <select name="rol" id="rol" class="form-select" required>
                                    <option value="">Seleccionar rol...</option>
                                    <?php foreach (array_keys($configuracionPermisos) as $rol): ?>
                                        <option value="<?= e($rol) ?>" <?= ($_POST['rol'] ?? '') === $rol ? 'selected' : '' ?>>
                                            <?= e(ucfirst($rol)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-play"></i> Probar Acceso
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matriz de Permisos Completa -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-table"></i> Matriz Completa de Acceso</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="width: 300px;">Vista</th>
                                        <th class="text-center">Permiso Requerido</th>
                                        <th class="text-center">Admin</th>
                                        <th class="text-center">Usuario</th>
                                        <th class="text-center">Consulta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todasLasVistas as $vista): ?>
                                    <tr>
                                        <td>
                                            <code><?= e($vista) ?></code>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($mapaVistasPermisos[$vista])): ?>
                                                <span class="badge bg-secondary"><?= e($mapaVistasPermisos[$vista]) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No configurado</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php foreach (['admin', 'usuario', 'consulta'] as $rol): ?>
                                        <?php 
                                        $resultado = obtenerEstadoAcceso($vista, $rol, $mapaVistasPermisos, $configuracionPermisos);
                                        $clase = $resultado['estado'] === 'permitido' ? 'success' : ($resultado['estado'] === 'denegado' ? 'danger' : 'warning');
                                        ?>
                                        <td class="text-center table-<?= $clase ?>">
                                            <?php if ($resultado['estado'] === 'permitido'): ?>
                                                <i class="bi bi-check-circle text-success"></i>
                                            <?php elseif ($resultado['estado'] === 'denegado'): ?>
                                                <i class="bi bi-x-circle text-danger"></i>
                                            <?php elseif ($resultado['estado'] === 'publico'): ?>
                                                <i class="bi bi-globe text-info"></i>
                                            <?php else: ?>
                                                <i class="bi bi-question-circle text-warning"></i>
                                            <?php endif; ?>
                                            <br><small><?= e($resultado['razon']) ?></small>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Ayuda -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información y Ayuda</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Estados de Acceso:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success"></i> <strong>Permitido:</strong> El rol tiene acceso a la vista</li>
                                    <li><i class="bi bi-x-circle text-danger"></i> <strong>Denegado:</strong> El rol no tiene acceso a la vista</li>
                                    <li><i class="bi bi-globe text-info"></i> <strong>Público:</strong> Vista accesible sin autenticación</li>
                                    <li><i class="bi bi-question-circle text-warning"></i> <strong>Indefinido:</strong> Vista sin configuración específica</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Roles del Sistema:</h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-danger">Admin</span> Acceso total, gestión de usuarios</li>
                                    <li><span class="badge bg-success">Usuario</span> Gestión de expedientes y reportes</li>
                                    <li><span class="badge bg-info">Consulta</span> Solo lectura y búsquedas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>