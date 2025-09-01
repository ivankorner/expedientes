<?php
session_start();

// Validar ID
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['mensaje'] = "ID de expediente inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=expedientes;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

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
            hl.fecha_cambio,
            DATE_FORMAT(hl.fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            hl.lugar_anterior,
            hl.lugar_nuevo,
            TIMESTAMPDIFF(HOUR, e.fecha_hora_ingreso, hl.fecha_cambio) as horas_desde_ingreso,
            TIMESTAMPDIFF(HOUR, 
                LAG(hl.fecha_cambio) OVER (ORDER BY hl.fecha_cambio),
                hl.fecha_cambio
            ) as horas_desde_ultimo_pase,
            0 as es_ingreso
        FROM historial_lugares hl
        JOIN expedientes e ON hl.expediente_id = e.id
        WHERE hl.expediente_id = ?
        ORDER BY hl.fecha_cambio ASC
    ");
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="/expedientes/publico/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Pases de Expediente <?= "{$expediente['numero']}/{$expediente['letra']}/{$expediente['anio']}" ?></h1>
                    <a href="listar_expedientes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Formulario de nuevo pase -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Registrar nuevo pase</h5>
                        <form id="formPase" action="procesar_pase.php" method="POST">
                            <input type="hidden" name="expediente_id" value="<?= $expediente['id'] ?>">
                            <input type="hidden" name="lugar_anterior" value="<?= htmlspecialchars($expediente['lugar']) ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="lugar_nuevo" class="form-label">Nuevo lugar *</label>
                                    <select class="form-select" id="lugar_nuevo" name="lugar_nuevo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="Mesa de Entrada">Mesa de Entrada</option>
                                        <option value="Comision I">Comisión I</option>
                                        <option value="Comision II">Comisión II</option>
                                        <option value="Comision III">Comisión III</option>
                                        <option value="Comision IV">Comisión IV</option>
                                        <option value="Comision V">Comisión V</option>
                                        <option value="Comision VI">Comisión VI</option>
                                        <option value="Comision VII">Comisión VII</option>
                                        <option value="Concejo Comisión">Concejo Comisión</option>
                                        <option value="Asesoria Legal">Asesoria Legal</option>
                                        <option value="Asesoria Contable">Asesoria Contable</option>
                                        <option value="Secretaria Legislativa Administrativa">Secretaria Legislativa Administrativa</option>
                                        <option value="Secretaria Legislativa Parlamentaria">Secretaria Legislativa Parlamentaria</option>
                                        <option value="Secretaria Legal y Técnica">Secretaria Legal y Técnica</option>
                                        <option value="Secretaria Comunicacion e Informacion Parlamentaria">Secretaria Comunicacion e Informacion Parlamentaria</option>
                                        <option value="Presidencia">Presidencia</option>
                                        <option value="Secretaria Comunicacion e Informacion Parlamentaria">D.E.M</option>
                                        <option value="Secretaria Comunicacion e Informacion Parlamentaria">Concejo Estudiantil</option>
                                        <option value="Archivo">Archivo</option>


                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_hora" class="form-label">Fecha y hora *</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="fecha_hora" 
                                           name="fecha_hora"
                                           required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Pase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de pases -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Historial de pases</h5>
                        <div class="table-responsive">
                            <div class="mb-3">
                                


                                <strong>Fecha de ingreso:</strong> <span class="badge rounded-pill text-bg-secondary"><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></span>
                                
                                
                                
                                
                                <br>

                                <strong>Lugar actual:</strong> <span class="badge rounded-pill text-bg-warning"><?= htmlspecialchars($expediente['lugar']) ?></span>
                                
                            </div>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Desde <i class="bi bi-arrow-right"></i></th>
                                        <th>Hacia</th>
                                        <th>Tiempo desde ingreso</th>
                                        <th>Tiempo desde último pase</th>
                                        <th>Línea de tiempo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $pase): 
    $horas_desde_ingreso = $pase['horas_desde_ingreso'];
    $dias_desde_ingreso = floor($horas_desde_ingreso / 24);
    $horas_resto_ingreso = $horas_desde_ingreso % 24;
    
    $horas_desde_ultimo = $pase['horas_desde_ultimo_pase'];
    $dias_desde_ultimo = $horas_desde_ultimo ? floor($horas_desde_ultimo / 24) : 0;
    $horas_resto_ultimo = $horas_desde_ultimo ? $horas_desde_ultimo % 24 : 0;
    
    $max_horas = max(array_column($historial, 'horas_desde_ingreso'));
    $porcentaje = ($horas_desde_ingreso / $max_horas) * 100;
?>
<tr>
    <td><?= $pase['fecha_formateada'] ?></td>
    <td><?= htmlspecialchars($pase['lugar_anterior']) ?></td>
    <td><?= htmlspecialchars($pase['lugar_nuevo']) ?></td>
    <td><?= $dias_desde_ingreso ?> días, <?= $horas_resto_ingreso ?> horas</td>
    <td>
        <?php if ($horas_desde_ultimo): ?>
            <?= $dias_desde_ultimo ?> días, <?= $horas_resto_ultimo ?> horas
        <?php else: ?>Primer pase<?php endif; ?>
    </td>
    <td style="width: 200px;">
        <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $porcentaje ?>%"
                 aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"
                 data-bs-toggle="tooltip" title="<?= "$dias_desde_ingreso días, $horas_resto_ingreso horas" ?>">
            </div>
        </div>
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarPaseModal('<?= $pase['fecha_cambio'] ?>','<?= htmlspecialchars($pase['lugar_nuevo'],ENT_QUOTES) ?>',<?= $pase['id'] ?? 0 ?>)">
            <i class="bi bi-pencil"></i> Editar
        </button>
    </td>
</tr>
<?php endforeach; ?>
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
function editarPaseModal(fecha, lugar, id) {
    const lugares = [
        'Mesa de Entrada', 'Comision I', 'Comision II', 'Comision III', 'Comision IV', 'Comision V', 'Comision VI', 'Comision VII',
        'Concejo Comisión', 'Asesoria Legal', 'Asesoria Contable', 'Secretaria Legislativa Administrativa',
        'Secretaria Legislativa Parlamentaria', 'Secretaria Legal y Técnica', 'Secretaria Comunicacion e Informacion Parlamentaria',
        'Presidencia', 'D.E.M', 'Concejo Estudiantil', 'Archivo'
    ];
    let selectHtml = `<select id='lugarEdit' class='swal2-input' style='width:100%;margin-top:8px;'>`;
    lugares.forEach(l => {
        selectHtml += `<option value="${l}"${l === lugar ? ' selected' : ''}>${l}</option>`;
    });
    selectHtml += `</select>`;
    Swal.fire({
        title: 'Editar pase',
        html: `<input type='datetime-local' id='fechaEdit' class='swal2-input' value='${fecha.replace(' ', 'T')}'><br>${selectHtml}`,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fechaVal = document.getElementById('fechaEdit').value;
            const lugarVal = document.getElementById('lugarEdit').value;
            if (!fechaVal || !lugarVal) {
                Swal.showValidationMessage('Todos los campos son obligatorios');
                return false;
            }
            return {fecha: fechaVal, lugar: lugarVal};
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('editar_pase.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&fecha=${encodeURIComponent(result.value.fecha)}&lugar_nuevo=${encodeURIComponent(result.value.lugar)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Actualizado', data.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(()=>Swal.fire('Error','No se pudo actualizar','error'));
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
            });
    });
    </script>
</body>
</html>