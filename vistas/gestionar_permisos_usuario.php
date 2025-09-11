<?php
session_start();

// Verificar permisos (solo admin y superuser pueden gestionar permisos)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superuser'])) {
    $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta sección';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

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

    // Obtener usuario seleccionado
    $user_id = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $selected_user = null;
    
    if ($user_id) {
        $stmt = $db->prepare('SELECT id, username, nombre, apellido, email, role, is_superuser FROM usuarios WHERE id = ? AND is_active = 1');
        $stmt->execute([$user_id]);
        $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener todos los usuarios activos
    $stmt = $db->query("SELECT id, username, nombre, apellido, role, is_superuser FROM usuarios WHERE is_active = 1 ORDER BY apellido, nombre");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener vistas del sistema agrupadas por categoría
    $stmt = $db->query("SELECT * FROM vistas_sistema WHERE activa = 1 ORDER BY categoria, nombre_amigable");
    $vistas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar vistas por categoría
    $vistas_por_categoria = [];
    foreach ($vistas_raw as $vista) {
        $vistas_por_categoria[$vista['categoria']][] = $vista;
    }
    
    // Obtener permisos del usuario seleccionado
    $permisos_usuario = [];
    if ($selected_user) {
        $stmt = $db->prepare("
            SELECT v.nombre_vista, COALESCE(p.puede_acceder, 0) as puede_acceder
            FROM vistas_sistema v
            LEFT JOIN permisos_usuario p ON v.nombre_vista = p.vista AND p.user_id = ?
            WHERE v.activa = 1
        ");
        $stmt->execute([$user_id]);
        $permisos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($permisos_raw as $permiso) {
            $permisos_usuario[$permiso['nombre_vista']] = $permiso['puede_acceder'];
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
    <title>Gestionar Permisos de Usuario - Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    
    <style>
        .page-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        
        .permissions-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(40, 167, 69, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .user-selector {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .category-section {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .category-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-header:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        
        .category-body {
            padding: 1.5rem;
            background: #f8f9fa;
        }
        
        .permission-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .permission-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #28a745;
        }
        
        .permission-switch {
            transform: scale(1.2);
        }
        
        .critical-badge {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
        }
        
        .user-info-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .btn-save-permissions {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-save-permissions:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            color: white;
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
                                    <i class="bi bi-shield-check me-2"></i>
                                    Gestionar Permisos de Usuario
                                </h1>
                                <p class="mb-0 opacity-75">
                                    Configure qué vistas puede acceder cada usuario del sistema
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
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

                <!-- Selector de usuario -->
                <div class="permissions-container">
                    <div class="user-selector">
                        <h5 class="mb-3">
                            <i class="bi bi-person-gear me-2"></i>
                            Seleccionar Usuario
                        </h5>
                        
                        <form method="get" action="">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Usuario a configurar:</label>
                                    <select name="user_id" class="form-select form-select-lg" onchange="this.form.submit()">
                                        <option value="">-- Seleccionar usuario --</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?= $usuario['id'] ?>" <?= $user_id == $usuario['id'] ? 'selected' : '' ?>>
                                                <?= e($usuario['apellido'] . ', ' . $usuario['nombre']) ?> 
                                                (@<?= e($usuario['username']) ?>) 
                                                - <?= $usuario['is_superuser'] ? 'Super Usuario' : ucfirst($usuario['role']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-search me-2"></i>Configurar Permisos
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if ($selected_user): ?>
                        <!-- Información del usuario seleccionado -->
                        <div class="user-info-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-1">
                                        <i class="bi bi-person-circle me-2"></i>
                                        <?= e($selected_user['apellido'] . ', ' . $selected_user['nombre']) ?>
                                    </h4>
                                    <p class="mb-0 opacity-75">
                                        @<?= e($selected_user['username']) ?> • <?= e($selected_user['email']) ?>
                                    </p>
                                    <span class="badge bg-light text-dark mt-2">
                                        <?= $selected_user['is_superuser'] ? 'Super Usuario' : ucfirst($selected_user['role']) ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <div class="stats-grid">
                                        <div class="stat-card">
                                            <div class="fs-4 fw-bold" id="totalPermisos">-</div>
                                            <small>Total Vistas</small>
                                        </div>
                                        <div class="stat-card">
                                            <div class="fs-4 fw-bold text-success" id="permisosActivos">-</div>
                                            <small>Con Acceso</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de permisos -->
                        <form method="post" action="procesar_permisos_usuario.php" id="permissionsForm">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="user_id" value="<?= $selected_user['id'] ?>">
                            
                            <?php if ($selected_user['is_superuser']): ?>
                                <!-- Mensaje para super usuario -->
                                <div class="alert alert-warning">
                                    <i class="bi bi-shield-exclamation me-2"></i>
                                    <strong>Super Usuario:</strong> Este usuario tiene acceso completo a todas las vistas del sistema. Los permisos individuales no se aplican.
                                </div>
                            <?php else: ?>
                                <!-- Controles generales -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list-check me-2"></i>
                                        Configurar Permisos por Vista
                                    </h5>
                                    <div>
                                        <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#gestionarVistasModal">
                                            <i class="bi bi-gear me-1"></i>Gestionar Vistas
                                        </button>
                                        <button type="button" class="btn btn-outline-success me-2" onclick="toggleAllPermissions(true)">
                                            <i class="bi bi-check-all me-1"></i>Permitir Todas
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="toggleAllPermissions(false)">
                                            <i class="bi bi-x-octagon me-1"></i>Denegar Todas
                                        </button>
                                    </div>
                                </div>

                                <!-- Permisos por categoría -->
                                <?php foreach ($vistas_por_categoria as $categoria => $vistas): ?>
                                    <div class="category-section">
                                        <div class="category-header" data-bs-toggle="collapse" data-bs-target="#categoria-<?= str_replace(' ', '-', strtolower($categoria)) ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="bi bi-folder me-2"></i>
                                                    <?= e($categoria) ?>
                                                </span>
                                                <div>
                                                    <span class="badge bg-light text-dark me-2" id="count-<?= str_replace(' ', '-', strtolower($categoria)) ?>">
                                                        <?= count($vistas) ?> vistas
                                                    </span>
                                                    <i class="bi bi-chevron-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="collapse show" id="categoria-<?= str_replace(' ', '-', strtolower($categoria)) ?>">
                                            <div class="category-body">
                                                <?php foreach ($vistas as $vista): ?>
                                                    <div class="permission-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="flex-grow-1 me-3">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <strong><?= e($vista['nombre_amigable']) ?></strong>
                                                                    <?php if ($vista['es_critica']): ?>
                                                                        <span class="critical-badge ms-2">
                                                                            <i class="bi bi-exclamation-triangle me-1"></i>Crítica
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <small class="text-muted d-block"><?= e($vista['descripcion']) ?></small>
                                                                <code class="small"><?= e($vista['nombre_vista']) ?></code>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger btn-sm me-2" 
                                                                        onclick="eliminarVista('<?= e($vista['nombre_vista']) ?>', '<?= e($vista['nombre_amigable']) ?>')"
                                                                        title="Eliminar vista del sistema"
                                                                        <?= $vista['es_critica'] ? 'disabled' : '' ?>>
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input permission-switch" 
                                                                           type="checkbox" 
                                                                           name="permisos[<?= e($vista['nombre_vista']) ?>]" 
                                                                           id="permiso-<?= e($vista['nombre_vista']) ?>"
                                                                           value="1"
                                                                           <?= !empty($permisos_usuario[$vista['nombre_vista']]) ? 'checked' : '' ?>
                                                                           onchange="updateStats()">
                                                                    <label class="form-check-label" for="permiso-<?= e($vista['nombre_vista']) ?>">
                                                                        <span class="text-success fw-bold">Permitir</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Botones de acción -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <div>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Los cambios se aplicarán inmediatamente después de guardar
                                        </small>
                                    </div>
                                    <div>
                                        <a href="gestionar_permisos_usuario.php" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-save-permissions">
                                            <i class="bi bi-floppy me-2"></i>Guardar Permisos
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal simplificado para gestionar vistas -->
    <div class="modal fade" id="gestionarVistasModal" tabindex="-1" aria-labelledby="gestionarVistasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="gestionarVistasModalLabel">
                        <i class="bi bi-gear-fill me-2"></i>Gestionar Archivos de Vistas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Todos los Archivos PHP en /vistas/</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="cargarArchivos()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                        </button>
                    </div>
                    
                    <div id="listaArchivos" style="max-height: 500px; overflow-y: auto;">
                        <!-- Los archivos se cargarán aquí dinámicamente -->
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-folder-open fs-1"></i>
                            <p class="mt-2">Haga clic en "Actualizar" para cargar los archivos</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStats() {
            const checkboxes = document.querySelectorAll('.permission-switch');
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.permission-switch:checked').length;
            
            document.getElementById('totalPermisos').textContent = total;
            document.getElementById('permisosActivos').textContent = checked;
        }
        
        function toggleAllPermissions(grant) {
            const checkboxes = document.querySelectorAll('.permission-switch');
            checkboxes.forEach(checkbox => {
                checkbox.checked = grant;
            });
            updateStats();
        }
        
        // Cargar archivos del directorio
        function cargarArchivos() {
            document.getElementById('listaArchivos').innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div><p class="mt-2">Cargando archivos...</p></div>';
            
            fetch('gestionar_vistas_sistema_simple.php?action=listarArchivos', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarListaArchivos(data.archivos);
                } else {
                    document.getElementById('listaArchivos').innerHTML = '<div class="alert alert-danger">Error al cargar archivos: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('listaArchivos').innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
            });
        }
        
        function mostrarListaArchivos(archivos) {
            let html = '';
            
            archivos.forEach(archivo => {
                const badgeClass = archivo.registrado ? 'bg-success' : 'bg-warning text-dark';
                const badgeText = archivo.registrado ? 'Registrado' : 'No registrado';
                const categoria = archivo.categoria || 'Sin categoría';
                const nombre = archivo.nombre || archivo.archivo.replace('.php', '').replace(/_/g, ' ');
                
                html += `
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <code class="me-3">${archivo.archivo}</code>
                                        <span class="badge ${badgeClass}">${badgeText}</span>
                                        ${archivo.registrado ? `<span class="badge bg-secondary ms-2">${categoria}</span>` : ''}
                                    </div>
                                    ${archivo.registrado ? `<small class="text-muted">${nombre}</small>` : ''}
                                </div>
                                <div>
                                    ${!archivo.registrado ? `
                                        <button type="button" class="btn btn-success btn-sm me-2" onclick="agregarArchivoSimple('${archivo.archivo}')">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar
                                        </button>
                                    ` : ''}
                                    ${archivo.registrado ? `
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarArchivoSimple('${archivo.archivo}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            if (archivos.length === 0) {
                html = '<div class="text-center text-muted py-4"><i class="bi bi-folder-x fs-1"></i><p class="mt-2">No se encontraron archivos PHP</p></div>';
            }
            
            document.getElementById('listaArchivos').innerHTML = html;
        }
        
        function agregarArchivoSimple(archivo) {
            // Generar nombre amigable automáticamente
            const nombreAmigable = archivo.replace('.php', '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            const formData = new FormData();
            formData.append('action', 'agregarVista');
            formData.append('archivo', archivo);
            formData.append('nombre', nombreAmigable);
            formData.append('categoria', 'General');
            
            fetch('gestionar_vistas_sistema_simple.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ ${archivo} agregado al sistema exitosamente`);
                    cargarArchivos(); // Recargar la lista
                    // Recargar la página completa para mostrar la nueva vista en permisos
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al agregar el archivo');
            });
        }
        
        function eliminarArchivoSimple(archivo) {
            if (confirm(`⚠️ ¿Está seguro de que desea eliminar "${archivo}" del sistema?\n\nEsto también eliminará todos los permisos asociados.`)) {
                const formData = new FormData();
                formData.append('action', 'eliminarVista');
                formData.append('archivo', archivo);
                
                fetch('gestionar_vistas_sistema_simple.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ ${archivo} eliminado del sistema exitosamente`);
                        cargarArchivos(); // Recargar la lista
                        // Recargar la página completa para actualizar permisos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al eliminar el archivo');
                });
            }
        }
        
        function eliminarVista(nombreVista, nombreAmigable) {
            eliminarArchivoSimple(nombreVista);
        }
        
        // Inicializar estadísticas
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            
            // Validación del formulario
            document.getElementById('permissionsForm')?.addEventListener('submit', function(e) {
                const checkedBoxes = document.querySelectorAll('.permission-switch:checked').length;
                if (checkedBoxes === 0) {
                    const confirmed = confirm('⚠️ Está quitando TODOS los permisos a este usuario.\n\nEsto significa que no podrá acceder a ninguna vista del sistema.\n\n¿Está seguro de continuar?');
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
