<?php
session_start();

// Validar ID del concejal
$concejal_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$concejal_id) {
    $_SESSION['mensaje'] = "ID de concejal inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

try {
    // Conectar a la base de datos
    require_once '../db/connection.php';
    $db = $pdo;

    // Obtener datos del concejal
    $stmt = $db->prepare("SELECT * FROM concejales WHERE id = ?");
    $stmt->execute([$concejal_id]);
    $concejal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$concejal) {
        throw new Exception("Concejal no encontrado");
    }

    // Obtener historial de bloques
    $stmt = $db->prepare("
        SELECT 
            nombre_bloque,
            fecha_inicio,
            fecha_fin,
            es_actual,
            observacion,
            fecha_registro,
            DATEDIFF(COALESCE(fecha_fin, CURDATE()), fecha_inicio) as dias_en_bloque
        FROM concejal_bloques_historial 
        WHERE concejal_id = ?
        ORDER BY fecha_inicio DESC, fecha_registro DESC
    ");
    $stmt->execute([$concejal_id]);
    $historial_bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al cargar el historial: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

require 'header.php';
require 'head.php';
?>

<!DOCTYPE html>
<html lang="es">
<body>
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        Historial de Bloques
                    </h1>
                    <div>
                        <a href="listar_concejales.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>
                        <button class="btn btn-success px-4" onclick="agregarNuevoBloque()">
                            <i class="bi bi-plus-circle"></i> Agregar Nuevo Bloque
                        </button>
                    </div>
                </div>

                <!-- Información del concejal -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-fill me-2"></i>
                            <?= htmlspecialchars($concejal['apellido'] . ', ' . $concejal['nombre']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>DNI:</strong> <?= htmlspecialchars($concejal['dni']) ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Bloque Actual:</strong> 
                                <span class="badge bg-warning text-dark fs-6">
                                    <?= htmlspecialchars($concejal['bloque']) ?>
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>Email:</strong> <?= htmlspecialchars($concejal['email'] ?: 'No registrado') ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Teléfono:</strong> <?= htmlspecialchars($concejal['cel'] ?: $concejal['tel'] ?: 'No registrado') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de bloques -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history text-info me-2"></i>
                            Historial Completo de Bloques
                        </h5>
                        <span class="badge bg-info">
                            <?= count($historial_bloques) ?> registros
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($historial_bloques)): ?>
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-exclamation-triangle fs-2"></i>
                                <h6 class="mt-3">No hay historial de bloques</h6>
                                <p class="mb-0">Este concejal no tiene registros de bloques en el historial.</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($historial_bloques as $index => $bloque): ?>
                                    <div class="timeline-item <?= $bloque['es_actual'] ? 'current' : '' ?>">
                                        <div class="timeline-marker <?= $bloque['es_actual'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <i class="bi bi-<?= $bloque['es_actual'] ? 'star-fill' : 'building' ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="card <?= $bloque['es_actual'] ? 'border-success' : '' ?>">
                                                <div class="card-header d-flex justify-content-between align-items-center py-2 <?= $bloque['es_actual'] ? 'bg-success text-white' : 'bg-light' ?>">
                                                    <h6 class="mb-0">
                                                        <i class="bi bi-building me-2"></i>
                                                        <?= htmlspecialchars($bloque['nombre_bloque']) ?>
                                                        <?php if ($bloque['es_actual']): ?>
                                                            <span class="badge bg-warning text-dark ms-2">ACTUAL</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="opacity-75">
                                                        Registrado: <?= date('d/m/Y', strtotime($bloque['fecha_registro'])) ?>
                                                    </small>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <strong>Fecha de Inicio:</strong><br>
                                                            <span class="text-success">
                                                                <i class="bi bi-calendar-check"></i>
                                                                <?= $bloque['fecha_inicio'] ? date('d/m/Y', strtotime($bloque['fecha_inicio'])) : 'No especificada' ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Fecha de Fin:</strong><br>
                                                            <span class="<?= $bloque['fecha_fin'] ? 'text-danger' : 'text-muted' ?>">
                                                                <i class="bi bi-calendar-x"></i>
                                                                <?= $bloque['fecha_fin'] ? date('d/m/Y', strtotime($bloque['fecha_fin'])) : ($bloque['es_actual'] ? 'Actualidad' : 'No especificada') ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Duración:</strong><br>
                                                            <span class="text-info">
                                                                <i class="bi bi-hourglass-split"></i>
                                                                <?php 
                                                                if ($bloque['dias_en_bloque'] > 0) {
                                                                    $años = floor($bloque['dias_en_bloque'] / 365);
                                                                    $días_restantes = $bloque['dias_en_bloque'] % 365;
                                                                    $meses = floor($días_restantes / 30);
                                                                    $días = $días_restantes % 30;
                                                                    
                                                                    $duracion = '';
                                                                    if ($años > 0) $duracion .= $años . ' año' . ($años > 1 ? 's' : '') . ' ';
                                                                    if ($meses > 0) $duracion .= $meses . ' mes' . ($meses > 1 ? 'es' : '') . ' ';
                                                                    if ($días > 0) $duracion .= $días . ' día' . ($días > 1 ? 's' : '');
                                                                    
                                                                    echo trim($duracion) ?: $bloque['dias_en_bloque'] . ' días';
                                                                } else {
                                                                    echo 'Menos de 1 día';
                                                                }
                                                                ?>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            <?php if (!$bloque['es_actual']): ?>
                                                                <button class="btn btn-outline-primary btn-sm" 
                                                                        onclick="editarBloque(<?= $concejal_id ?>, '<?= htmlspecialchars($bloque['nombre_bloque'], ENT_QUOTES) ?>')">
                                                                    <i class="bi bi-pencil"></i> Editar
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-outline-warning btn-sm" 
                                                                        onclick="cambiarBloqueActual(<?= $concejal_id ?>)">
                                                                    <i class="bi bi-arrow-repeat"></i> Cambiar
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php if ($bloque['observacion']): ?>
                                                        <div class="row mt-2">
                                                            <div class="col-12">
                                                                <strong>Observaciones:</strong><br>
                                                                <em class="text-muted"><?= htmlspecialchars($bloque['observacion']) ?></em>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 15px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #dee2e6;
        }

        .timeline-item.current .timeline-marker {
            box-shadow: 0 0 0 2px #198754;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 2px #198754;
            }
            50% {
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0.3);
            }
            100% {
                box-shadow: 0 0 0 2px #198754;
            }
        }

        .timeline-content {
            margin-left: 20px;
        }
    </style>

    <script>
        function agregarNuevoBloque() {
            Swal.fire({
                title: 'Agregar Nuevo Bloque',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Bloque:</label>
                            <input type="text" id="nuevo_bloque" class="form-control" placeholder="Ej: Frente para la Victoria">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha de Inicio:</label>
                            <input type="date" id="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observaciones:</label>
                            <textarea id="observaciones" class="form-control" rows="2" placeholder="Observaciones opcionales"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hacer_actual">
                            <label class="form-check-label" for="hacer_actual">
                                <strong>Marcar como bloque actual</strong>
                            </label>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Agregar Bloque',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                width: '500px',
                preConfirm: () => {
                    const nombre = document.getElementById('nuevo_bloque').value;
                    const fecha = document.getElementById('fecha_inicio').value;
                    const observaciones = document.getElementById('observaciones').value;
                    const hacer_actual = document.getElementById('hacer_actual').checked;

                    if (!nombre) {
                        Swal.showValidationMessage('El nombre del bloque es obligatorio');
                        return false;
                    }

                    return {
                        nombre_bloque: nombre,
                        fecha_inicio: fecha,
                        observaciones: observaciones,
                        hacer_actual: hacer_actual
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar indicador de carga
                    Swal.fire({
                        title: 'Agregando bloque...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar datos al servidor
                    const formData = new FormData();
                    formData.append('concejal_id', <?= $concejal_id ?>);
                    formData.append('nombre_bloque', result.value.nombre_bloque);
                    formData.append('fecha_inicio', result.value.fecha_inicio);
                    formData.append('observaciones', result.value.observaciones);
                    formData.append('hacer_actual', result.value.hacer_actual);

                    fetch('procesar_agregar_bloque_historial.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Bloque agregado!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    });
                }
            });
        }

        function cambiarBloqueActual(concejalId) {
            Swal.fire({
                title: 'Cambiar Bloque Actual',
                text: '¿Desea cambiar el concejal a un nuevo bloque?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107'
            }).then((result) => {
                if (result.isConfirmed) {
                    agregarNuevoBloque();
                }
            });
        }

        function editarBloque(concejalId, nombreBloque) {
            Swal.fire({
                title: 'Función en desarrollo',
                text: `Editar bloque: ${nombreBloque}`,
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }
    </script>
</body>
</html>