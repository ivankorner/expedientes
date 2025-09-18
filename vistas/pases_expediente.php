<?php
session_start();

// Headers para evitar cache en páginas dinámicas
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Validar ID
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['mensaje'] = "ID de expediente inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}

try {
    // Conectar a la base de datos (usar base local)
    require_once '../db/connection.php';
    $db = $pdo;

    // Obtener datos del expediente
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    // Obtener historial de pases
    $stmt = $db->prepare("
        SELECT 
            hl.id,
            hl.fecha_cambio,
            DATE_FORMAT(hl.fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            hl.lugar_anterior,
            hl.lugar_nuevo,
            hl.tipo_movimiento,
            hl.numero_acta,
            hl.usuario_id,
            TIMESTAMPDIFF(HOUR, e.fecha_hora_ingreso, hl.fecha_cambio) as horas_desde_ingreso
        FROM historial_lugares hl
        JOIN expedientes e ON hl.expediente_id = e.id
        WHERE hl.expediente_id = ?
        ORDER BY hl.fecha_cambio DESC
    ");
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug temporal - comentar en producción
    error_log("Expediente ID: $id");
    error_log("Registros encontrados en historial: " . count($historial));
    if (!empty($historial)) {
        error_log("Primer registro: " . json_encode($historial[0]));
    }

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pases de Expediente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-primary {
            --bs-table-bg: rgba(13, 110, 253, 0.1);
            font-weight: 500;
        }

        .badge {
            margin-left: 0.5rem;
        }

        .text-muted {
            color: #6c757d !important;
        }

        /* Estilos para ordenamiento de tabla */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px !important;
        }

        .sortable:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .sortable::after {
            content: "⇅";
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 12px;
        }

        .sortable.asc::after {
            content: "↑";
            color: #0d6efd;
            font-weight: bold;
        }

        .sortable.desc::after {
            content: "↓";
            color: #0d6efd;
            font-weight: bold;
        }

        .non-sortable {
            cursor: default;
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require '../vistas/sidebar.php'; ?>

            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Pases de Expediente <?= "{$expediente['numero']}/{$expediente['letra']}/{$expediente['folio']}/{$expediente['libro']}/{$expediente['anio']}" ?></h1>
                    <a href="listar_expedientes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Formulario de nuevo pase -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>
                            Registrar nuevo pase
                        </h6>
                    </div>
                    <div class="card-body py-3">
                        <form id="formPase" action="procesar_pase.php" method="POST">
                            <input type="hidden" name="expediente_id" value="<?= $expediente['id'] ?>">
                            <input type="hidden" name="lugar_anterior" value="<?= htmlspecialchars($expediente['lugar']) ?>">
                            
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label for="lugar_nuevo" class="form-label fw-semibold mb-1">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        Nuevo lugar *
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="lugar_nuevo" 
                                           name="lugar_nuevo" 
                                           list="lugares_list" 
                                           required 
                                           placeholder="Escriba o seleccione un lugar">
                                    <datalist id="lugares_list">
                                        <option value="Mesa de Entrada">
                                        <option value="Comision I">
                                        <option value="Comision II">
                                        <option value="Comision III">
                                        <option value="Comision IV">
                                        <option value="Comision V">
                                        <option value="Comision VI">
                                        <option value="Comision VII">
                                        <option value="Concejo Comisión">
                                        <option value="Asesoria Legal">
                                        <option value="Asesoria Contable">
                                        <option value="Secretaria Legislativa Administrativa">
                                        <option value="Secretaria Legislativa Parlamentaria">
                                        <option value="Secretaria Legal y Técnica">
                                        <option value="Secretaria Comunicacion e Informacion Parlamentaria">
                                        <option value="Presidencia">
                                        <option value="Particular">
                                        <option value="D.E.M">
                                        <option value="Concejo Estudiantil">
                                        <option value="Archivo">
                                        <option value="Archivo Art. 75 R.I">
                                        <option value="Reunión">
                                    </datalist>
                                </div>
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="tipo_movimiento" class="form-label fw-semibold mb-1">
                                        <i class="bi bi-arrow-left-right me-1"></i>
                                        Tipo de movimiento *
                                    </label>
                                    <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Ingreso">📥 Ingreso</option>
                                        <option value="Salida">📤 Salida</option>
                                    </select>
                                </div>
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="fecha_hora" class="form-label fw-semibold mb-1">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Fecha y hora *
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="fecha_hora" 
                                           name="fecha_hora"
                                           required>
                                </div>
                                
                                <div class="col-lg-3 col-md-6">
                                    <label for="numero_acta" class="form-label fw-semibold mb-1">
                                        <i class="bi bi-file-text me-1"></i>
                                        Número de Acta
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="numero_acta" 
                                           name="numero_acta" 
                                           maxlength="30" 
                                           placeholder="Ej: 123/2025">
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-2"></i> 
                                    Guardar Pase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de pases -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>
                            Historial de pases (<?= count($historial) ?> registros)
                        </h5>
                        <?php if (count($historial) == 0): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay pases registrados para este expediente. Use el formulario de arriba para registrar el primer pase.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <div class="mb-3">
                                


                                <strong>Fecha de ingreso:</strong> <span class="badge rounded-pill text-bg-secondary"><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></span>
                                
                                
                                
                                
                                <br>

                                <strong>Lugar actual:</strong> <span class="badge rounded-pill text-bg-warning"><?= htmlspecialchars($expediente['lugar']) ?></span>
                                
                            </div>
                            <table class="table table-striped table-hover" id="historialTable">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="0" data-type="date">Fecha y Hora</th>
                                        <th class="sortable" data-column="1" data-type="text">Desde <i class="bi bi-arrow-right"></i></th>
                                        <th class="sortable" data-column="2" data-type="text">Hacia</th>
                                        <th class="sortable" data-column="3" data-type="text">Movimiento</th>
                                        <th class="sortable" data-column="4" data-type="text">N° de Acta</th>
                                        <th class="sortable" data-column="5" data-type="numeric">Tiempo desde ingreso</th>
                                        <th class="non-sortable">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($historial)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="bi bi-info-circle me-2"></i>
                                                No hay historial de pases para este expediente
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($historial as $pase): 
    $horas_desde_ingreso = $pase['horas_desde_ingreso'] ?? 0;
    $dias_desde_ingreso = floor($horas_desde_ingreso / 24);
    $horas_resto_ingreso = $horas_desde_ingreso % 24;
    $fecha_timestamp = strtotime($pase['fecha_cambio']);
?>
<tr>
    <td data-sort="<?= $fecha_timestamp ?>"><?= $pase['fecha_formateada'] ?></td>
    <td data-sort="<?= htmlspecialchars($pase['lugar_anterior'] ?? '') ?>"><?= htmlspecialchars($pase['lugar_anterior'] ?? '') ?></td>
    <td data-sort="<?= htmlspecialchars($pase['lugar_nuevo'] ?? '') ?>"><?= htmlspecialchars($pase['lugar_nuevo'] ?? '') ?></td>
    <td data-sort="<?= htmlspecialchars($pase['tipo_movimiento'] ?? 'Movimiento') ?>"><?= htmlspecialchars($pase['tipo_movimiento'] ?? 'Movimiento') ?></td>
    <td data-sort="<?= htmlspecialchars($pase['numero_acta'] ?? '') ?>"><?= htmlspecialchars($pase['numero_acta'] ?? '') ?></td>
    <td data-sort="<?= $horas_desde_ingreso ?>"><?= $dias_desde_ingreso ?> días, <?= $horas_resto_ingreso ?> horas</td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarPaseModal('<?= $pase['fecha_cambio'] ?>','<?= htmlspecialchars($pase['lugar_nuevo'] ?? '',ENT_QUOTES) ?>',<?= $pase['id'] ?? 0 ?>,'<?= htmlspecialchars($pase['tipo_movimiento'] ?? '',ENT_QUOTES) ?>','<?= htmlspecialchars($pase['numero_acta'] ?? '',ENT_QUOTES) ?>')">
            <i class="bi bi-pencil"></i> Editar
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="eliminarPase(<?= $pase['id'] ?? 0 ?>)">
            <i class="bi bi-trash"></i> Eliminar
        </button>
    </td>
</tr>
<?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Funcionalidad de ordenamiento de tabla
class TableSorter {
    constructor(tableId) {
        this.table = document.getElementById(tableId);
        this.tbody = this.table.querySelector('tbody');
        this.headers = this.table.querySelectorAll('th.sortable');
        this.currentSort = { column: -1, direction: 'asc' };
        this.init();
    }

    init() {
        this.headers.forEach((header, index) => {
            header.addEventListener('click', () => this.sortTable(index, header));
        });
    }

    sortTable(columnIndex, header) {
        const dataType = header.getAttribute('data-type');
        const rows = Array.from(this.tbody.querySelectorAll('tr'));
        
        // Determinar dirección del ordenamiento
        let direction = 'asc';
        if (this.currentSort.column === columnIndex && this.currentSort.direction === 'asc') {
            direction = 'desc';
        }
        
        // Limpiar clases de otros encabezados
        this.headers.forEach(h => h.classList.remove('asc', 'desc'));
        
        // Agregar clase al encabezado actual
        header.classList.add(direction);
        
        // Ordenar filas
        rows.sort((a, b) => {
            const aVal = this.getCellValue(a, columnIndex, dataType);
            const bVal = this.getCellValue(b, columnIndex, dataType);
            
            let result = this.compareValues(aVal, bVal, dataType);
            return direction === 'desc' ? -result : result;
        });
        
        // Reorganizar filas en el DOM
        rows.forEach(row => this.tbody.appendChild(row));
        
        // Actualizar estado actual
        this.currentSort = { column: columnIndex, direction: direction };
    }

    getCellValue(row, columnIndex, dataType) {
        const cell = row.cells[columnIndex];
        const sortValue = cell.getAttribute('data-sort');
        
        if (sortValue) {
            if (dataType === 'numeric' || dataType === 'date') {
                return parseFloat(sortValue) || 0;
            }
            return sortValue.toLowerCase();
        }
        
        const textValue = cell.textContent.trim();
        if (dataType === 'numeric') {
            // Extraer números del texto (para "X días, Y horas")
            const match = textValue.match(/(\d+)/);
            return match ? parseFloat(match[1]) : 0;
        }
        
        return textValue.toLowerCase();
    }

    compareValues(a, b, dataType) {
        if (dataType === 'numeric' || dataType === 'date') {
            return a - b;
        }
        
        if (a < b) return -1;
        if (a > b) return 1;
        return 0;
    }
}

// Inicializar ordenamiento cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('historialTable')) {
        new TableSorter('historialTable');
    }
});
</script>
<script>
// Manejar envío del formulario de nuevo pase
document.getElementById('formPase').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('procesar_pase.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                this.reset(); // Limpiar el formulario
                location.reload(); // Recargar para mostrar el nuevo pase
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo conectar con el servidor',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
});

function eliminarPase(id) {
    Swal.fire({
        title: '¿Eliminar pase?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_pase.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}`
            })
            .then(async res => {
                let data;
                try {
                    data = await res.json();
                } catch (e) {
                    Swal.fire('Error', 'Respuesta inesperada del servidor', 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            });
        }
    });
}
function editarPaseModal(fecha, lugar, id, tipoMovimiento = '', numeroActa = '') {
    const lugares = [
        'Mesa de Entrada', 'Comision I', 'Comision II', 'Comision III', 'Comision IV', 'Comision V', 'Comision VI', 'Comision VII',
        'Concejo Comisión', 'Asesoria Legal', 'Asesoria Contable', 'Secretaria Legislativa Administrativa',
        'Secretaria Legislativa Parlamentaria', 'Secretaria Legal y Técnica', 'Secretaria Comunicacion e Informacion Parlamentaria',
        'Presidencia', 'D.E.M', 'Concejo Estudiantil', 'Archivo', 'Archivo Art. 75 R.I'
    ];
    
    let selectLugarHtml = `<select id='lugarEdit' class='swal2-input' style='width:100%;margin-top:8px;'>`;
    lugares.forEach(l => {
        selectLugarHtml += `<option value="${l}"${l === lugar ? ' selected' : ''}>${l}</option>`;
    });
    selectLugarHtml += `</select>`;
    
    let selectTipoHtml = `<select id='tipoEdit' class='swal2-input' style='width:100%;margin-top:8px;'>
        <option value="">Seleccione tipo de movimiento...</option>
        <option value="Ingreso"${tipoMovimiento === 'Ingreso' ? ' selected' : ''}>Ingreso</option>
        <option value="Salida"${tipoMovimiento === 'Salida' ? ' selected' : ''}>Salida</option>
    </select>`;
    
    Swal.fire({
        title: 'Editar pase',
        html: `
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Fecha y hora:</label>
                <input type='datetime-local' id='fechaEdit' class='swal2-input' value='${fecha.replace(' ', 'T')}' style='margin-top: 0;'>
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Nuevo lugar:</label>
                ${selectLugarHtml}
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Tipo de movimiento:</label>
                ${selectTipoHtml}
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Número de acta:</label>
                <input type='text' id='actaEdit' class='swal2-input' value='${numeroActa}' placeholder='Ej: 123/2025' maxlength='30' style='margin-top: 0;'>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        width: '500px',
        preConfirm: () => {
            const fechaVal = document.getElementById('fechaEdit').value;
            const lugarVal = document.getElementById('lugarEdit').value;
            const tipoVal = document.getElementById('tipoEdit').value;
            const actaVal = document.getElementById('actaEdit').value;
            
            if (!fechaVal || !lugarVal || !tipoVal) {
                Swal.showValidationMessage('Los campos fecha, lugar y tipo de movimiento son obligatorios');
                return false;
            }
            return {
                fecha: fechaVal, 
                lugar: lugarVal, 
                tipo: tipoVal, 
                acta: actaVal
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('editar_pase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}&fecha=${encodeURIComponent(result.value.fecha)}&lugar_nuevo=${encodeURIComponent(result.value.lugar)}&tipo_movimiento=${encodeURIComponent(result.value.tipo)}&numero_acta=${encodeURIComponent(result.value.acta)}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        throw new Error('Respuesta del servidor no es JSON válido: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Forzar recarga completa de la página
                        window.location.reload(true);
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo actualizar el pase',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Verifique su conexión.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
}
</script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Crear gráfica de línea temporal
        const historialData = <?= json_encode(array_map(function($pase) {
            return [
                'fecha' => $pase['fecha_formateada'],
                'lugar' => $pase['lugar_nuevo'],
                'horas' => $pase['horas_desde_ingreso']
            ];
        }, $historial)) ?>;

        const canvas = document.createElement('canvas');
        canvas.id = 'timelineChart';
        document.querySelector('.card-body').appendChild(canvas);

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: historialData.map(d => d.fecha),
                datasets: [{
                    label: 'Horas transcurridas',
                    data: historialData.map(d => d.horas),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Línea de tiempo de pases'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const horas = context.raw;
                                const dias = Math.floor(horas / 24);
                                const horasResto = horas % 24;
                                return `${dias} días, ${horasResto} horas`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'DD/MM/YYYY HH:mm',
                            displayFormats: {
                                day: 'DD/MM',
                                hour: 'HH:mm'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Fecha y Hora'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Horas'
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>