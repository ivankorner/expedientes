<?php
/**
 * Sistema de Verificación de Sesiones y Permisos
 * Herramienta completa para diagnosticar y gestionar permisos de usuarios
 */

session_start();

// Incluir el sistema de permisos si existe
if (file_exists('verificar_permisos.php')) {
    require_once 'verificar_permisos.php';
}

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función para verificar acceso a una vista específica
function verificarAccesoVista($vista, $rol) {
    $permisos = [
        // Páginas administrativas - Solo admin
        'crear_usuario.php' => ['admin'],
        'listar_usuarios.php' => ['admin'],
        'eliminar_usuario.php' => ['admin'],
        'procesar_usuario.php' => ['admin'],
        
        // Páginas de gestión de expedientes - Admin y usuario
        'carga_expedientes.php' => ['admin', 'usuario'],
        'listar_expedientes.php' => ['admin', 'usuario'],
        'actualizar_expedientes.php' => ['admin', 'usuario'],
        'eliminar_expediente.php' => ['admin'],
        'pases_expediente.php' => ['admin', 'usuario'],
        'editar_pase.php' => ['admin', 'usuario'],
        'eliminar_pase.php' => ['admin', 'usuario'],
        'historial_expediente.php' => ['admin', 'usuario'],
        'procesar_carga_expedientes.php' => ['admin', 'usuario'],
        'procesar_pase.php' => ['admin', 'usuario'],
        'procesar_actualizacion.php' => ['admin', 'usuario'],
        
        // Páginas de gestión de iniciadores - Admin y usuario
        'carga_iniciador.php' => ['admin', 'usuario'],
        'listar_iniciadores.php' => ['admin', 'usuario'],
        'carga_concejal.php' => ['admin', 'usuario'],
        'listar_concejales.php' => ['admin', 'usuario'],
        'carga_persona_juri_entidad.php' => ['admin', 'usuario'],
        'listar_persona_juri_entidad.php' => ['admin', 'usuario'],
        'procesar_carga_iniciador.php' => ['admin', 'usuario'],
        'procesar_carga_concejal.php' => ['admin', 'usuario'],
        'procesar_carga_entidad.php' => ['admin', 'usuario'],
        
        // Búsquedas y consultas - Todos los roles
        'buscar_expediente.php' => ['admin', 'usuario', 'consulta'],
        'busqueda_rapida.php' => ['admin', 'usuario', 'consulta'],
        'consulta.php' => ['admin', 'usuario', 'consulta'],
        'resultados.php' => ['admin', 'usuario', 'consulta'],
        
        // Dashboard - Todos los usuarios logueados
        'dashboard.php' => ['admin', 'usuario', 'consulta'],
        
        // PDFs y obtención de datos
        'pdf_auto_descarga.php' => ['admin', 'usuario'],
        'generar_pdf_expediente.php' => ['admin', 'usuario'],
        'obtener_expediente.php' => ['admin', 'usuario', 'consulta'],
        'obtener_historial.php' => ['admin', 'usuario', 'consulta'],
        'obtener_historial_pases.php' => ['admin', 'usuario', 'consulta'],
        
        // Páginas públicas
        'login.php' => ['todos'],
        'logout.php' => ['todos']
    ];

    if (!isset($permisos[$vista])) {
        return 'indefinido';
    }

    if (in_array('todos', $permisos[$vista])) {
        return 'permitido';
    }

    return in_array($rol, $permisos[$vista]) ? 'permitido' : 'denegado';
}

// Obtener información de la sesión actual
$sesionActiva = !empty($_SESSION);
$usuarioActual = $_SESSION['usuario'] ?? null;
$rolActual = $_SESSION['rol'] ?? null;
$usuarioId = $_SESSION['usuario_id'] ?? null;

// Obtener lista de usuarios de la base de datos
$usuarios = [];
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $db->query("SELECT id, username, nombre, apellido, email, role, is_active, created_at FROM usuarios ORDER BY role, username");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorDB = $e->getMessage();
}

// Lista de vistas para verificar permisos
$vistas = [
    'Gestión de Usuarios' => [
        'crear_usuario.php',
        'listar_usuarios.php',
        'eliminar_usuario.php'
    ],
    'Gestión de Expedientes' => [
        'carga_expedientes.php',
        'listar_expedientes.php',
        'actualizar_expedientes.php',
        'eliminar_expediente.php',
        'pases_expediente.php',
        'historial_expediente.php'
    ],
    'Gestión de Iniciadores' => [
        'carga_iniciador.php',
        'listar_iniciadores.php',
        'carga_concejal.php',
        'listar_concejales.php',
        'carga_persona_juri_entidad.php'
    ],
    'Búsquedas y Consultas' => [
        'buscar_expediente.php',
        'busqueda_rapida.php',
        'consulta.php',
        'resultados.php'
    ],
    'Reportes y PDFs' => [
        'pdf_auto_descarga.php',
        'generar_pdf_expediente.php'
    ]
];

// Procesar cambio de sesión de prueba
if ($_POST['accion'] ?? '' === 'simular_sesion') {
    $usuarioSimular = $_POST['usuario_simular'] ?? '';
    if ($usuarioSimular && $usuarioSimular !== 'limpiar') {
        foreach ($usuarios as $usuario) {
            if ($usuario['username'] === $usuarioSimular) {
                $_SESSION['modo_simulacion'] = true;
                $_SESSION['usuario_original'] = $_SESSION['usuario'] ?? null;
                $_SESSION['rol_original'] = $_SESSION['rol'] ?? null;
                $_SESSION['usuario'] = $usuario['username'];
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['rol'] = $usuario['role'];
                break;
            }
        }
    } elseif ($usuarioSimular === 'limpiar') {
        if (isset($_SESSION['modo_simulacion'])) {
            if (isset($_SESSION['usuario_original'])) {
                $_SESSION['usuario'] = $_SESSION['usuario_original'];
                $_SESSION['rol'] = $_SESSION['rol_original'];
            }
            unset($_SESSION['modo_simulacion'], $_SESSION['usuario_original'], $_SESSION['rol_original']);
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$modoSimulacion = $_SESSION['modo_simulacion'] ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Sesiones y Permisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .permiso-permitido { background-color: #d1edfa !important; }
        .permiso-denegado { background-color: #f8d7da !important; }
        .permiso-indefinido { background-color: #fff3cd !important; }
        .modo-simulacion { 
            border: 3px solid #ff6b6b; 
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            color: white;
        }
        .table-permisos th { position: sticky; top: 0; background: white; z-index: 10; }
        .usuario-activo { background-color: #d4edda; }
        .usuario-inactivo { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        
        <?php if ($modoSimulacion): ?>
        <div class="alert alert-warning modo-simulacion">
            <h5><i class="bi bi-exclamation-triangle"></i> MODO SIMULACIÓN ACTIVO</h5>
            <p class="mb-0">Estás simulando la sesión del usuario: <strong><?= e($_SESSION['usuario']) ?></strong> (<?= e($_SESSION['rol']) ?>)</p>
            <p class="mb-0">Usuario original: <strong><?= e($_SESSION['usuario_original'] ?? 'No definido') ?></strong></p>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Panel de Información de Sesión -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Sesión Actual</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($sesionActiva): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Sesión activa
                            </div>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Usuario:</strong></td>
                                    <td><?= e($usuarioActual) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><?= e($usuarioId) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Rol:</strong></td>
                                    <td>
                                        <span class="badge bg-<?= $rolActual === 'admin' ? 'danger' : ($rolActual === 'usuario' ? 'success' : 'info') ?>">
                                            <?= e($rolActual) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No hay sesión activa
                            </div>
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel de Simulación de Sesión -->
                <?php if ($sesionActiva && ($rolActual === 'admin' || $modoSimulacion)): ?>
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-play-circle"></i> Simulador de Sesión</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="simular_sesion">
                            <div class="mb-3">
                                <label class="form-label">Simular como usuario:</label>
                                <select name="usuario_simular" class="form-select form-select-sm">
                                    <option value="limpiar">🔄 Limpiar simulación</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= e($usuario['username']) ?>" 
                                                <?= $_SESSION['usuario'] === $usuario['username'] ? 'selected' : '' ?>>
                                            <?= e($usuario['username']) ?> (<?= e($usuario['role']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Cambiar Sesión
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lista de Usuarios -->
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-people"></i> Usuarios del Sistema</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (isset($errorDB)): ?>
                            <div class="alert alert-danger m-2">
                                <i class="bi bi-exclamation-circle"></i> Error de BD: <?= e($errorDB) ?>
                            </div>
                        <?php elseif (empty($usuarios)): ?>
                            <div class="alert alert-warning m-2">
                                <i class="bi bi-people"></i> No hay usuarios registrados
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <tr class="<?= $usuario['is_active'] ? 'usuario-activo' : 'usuario-inactivo' ?>">
                                            <td>
                                                <small>
                                                    <strong><?= e($usuario['username']) ?></strong><br>
                                                    <?= e($usuario['nombre']) ?> <?= e($usuario['apellido']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $usuario['role'] === 'admin' ? 'danger' : ($usuario['role'] === 'usuario' ? 'success' : 'info') ?>">
                                                    <?= e($usuario['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($usuario['is_active']): ?>
                                                    <i class="bi bi-check-circle text-success" title="Activo"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle text-danger" title="Inactivo"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel de Verificación de Permisos -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-shield-check"></i> Matriz de Permisos por Vista</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0 table-permisos">
                                <thead>
                                    <tr>
                                        <th style="width: 300px;">Vista / Página</th>
                                        <th class="text-center">Admin</th>
                                        <th class="text-center">Usuario</th>
                                        <th class="text-center">Consulta</th>
                                        <th class="text-center">Acceso Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vistas as $categoria => $listaVistas): ?>
                                        <tr class="table-secondary">
                                            <td colspan="5"><strong><i class="bi bi-folder"></i> <?= e($categoria) ?></strong></td>
                                        </tr>
                                        <?php foreach ($listaVistas as $vista): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= e($vista) ?>" target="_blank" class="text-decoration-none">
                                                    <?= e($vista) ?> <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            </td>
                                            <?php
                                            $permisoAdmin = verificarAccesoVista($vista, 'admin');
                                            $permisoUsuario = verificarAccesoVista($vista, 'usuario');
                                            $permisoConsulta = verificarAccesoVista($vista, 'consulta');
                                            $permisoActual = $rolActual ? verificarAccesoVista($vista, $rolActual) : 'sin_sesion';
                                            ?>
                                            <td class="text-center permiso-<?= $permisoAdmin ?>">
                                                <?php if ($permisoAdmin === 'permitido'): ?>
                                                    <i class="bi bi-check-circle text-success"></i>
                                                <?php elseif ($permisoAdmin === 'denegado'): ?>
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-question-circle text-warning"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center permiso-<?= $permisoUsuario ?>">
                                                <?php if ($permisoUsuario === 'permitido'): ?>
                                                    <i class="bi bi-check-circle text-success"></i>
                                                <?php elseif ($permisoUsuario === 'denegado'): ?>
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-question-circle text-warning"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center permiso-<?= $permisoConsulta ?>">
                                                <?php if ($permisoConsulta === 'permitido'): ?>
                                                    <i class="bi bi-check-circle text-success"></i>
                                                <?php elseif ($permisoConsulta === 'denegado'): ?>
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-question-circle text-warning"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center permiso-<?= $permisoActual ?>">
                                                <?php if ($permisoActual === 'permitido'): ?>
                                                    <i class="bi bi-check-circle text-success"></i> Permitido
                                                <?php elseif ($permisoActual === 'denegado'): ?>
                                                    <i class="bi bi-x-circle text-danger"></i> Denegado
                                                <?php elseif ($permisoActual === 'sin_sesion'): ?>
                                                    <i class="bi bi-person-x text-muted"></i> Sin sesión
                                                <?php else: ?>
                                                    <i class="bi bi-question-circle text-warning"></i> Indefinido
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leyenda -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Leyenda:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2 p-2 permiso-permitido" style="width: 20px; height: 20px;"></div>
                                    <span>Acceso Permitido</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2 p-2 permiso-denegado" style="width: 20px; height: 20px;"></div>
                                    <span>Acceso Denegado</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2 p-2 permiso-indefinido" style="width: 20px; height: 20px;"></div>
                                    <span>Permiso Indefinido</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Acciones -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-tools"></i> Herramientas de Administración</h6>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="login.php" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                            <a href="dashboard.php" class="btn btn-success">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a href="crear_usuario.php" class="btn btn-warning">
                                <i class="bi bi-person-plus"></i> Crear Usuario
                            </a>
                            <a href="listar_usuarios.php" class="btn btn-info">
                                <i class="bi bi-people"></i> Listar Usuarios
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>