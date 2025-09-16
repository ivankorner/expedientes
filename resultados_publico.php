<?php
/**
 * Resultados de búsqueda pública de expedientes
 */

// Iniciar sesión y configuración
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Función para escapar output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

try {
    // Validar campos requeridos
    $campos_requeridos = ['numero', 'letra', 'folio', 'libro', 'anio', 'captcha'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo '$campo' es requerido");
        }
    }

    // Debug: mostrar valores recibidos (comentar en producción)
    error_log("Valores recibidos - Número: " . $_POST['numero'] . ", Letra: " . $_POST['letra'] . ", Folio: " . $_POST['folio'] . ", Libro: " . $_POST['libro'] . ", Año: " . $_POST['anio']);

    // Sanitizar y validar inputs - MANTENER CEROS A LA IZQUIERDA
    $numero_original = trim($_POST['numero']);
    $letra = strtoupper(trim($_POST['letra']));
    $folio_original = trim($_POST['folio']);
    $libro_original = trim($_POST['libro']);
    $anio = (int)trim($_POST['anio']);

    // Validar que sean solo números pero conservar formato original
    if (!preg_match('/^[0-9]{1,6}$/', $numero_original)) {
        throw new Exception("El número del expediente debe contener solo dígitos (1-6 caracteres).");
    }
    if (!preg_match('/^[0-9]{1,6}$/', $folio_original)) {
        throw new Exception("El folio debe contener solo dígitos (1-6 caracteres).");
    }
    if (!preg_match('/^[0-9]{1,6}$/', $libro_original)) {
        throw new Exception("El libro debe contener solo dígitos (1-6 caracteres).");
    }

    // Convertir a enteros para validaciones de rango, pero conservar strings originales para la consulta
    $numero_int = (int)$numero_original;
    $folio_int = (int)$folio_original;
    $libro_int = (int)$libro_original;

    // Debug: mostrar valores después de validación
    error_log("Después de validación - Número original: '$numero_original' (int: $numero_int), Folio original: '$folio_original' (int: $folio_int), Libro original: '$libro_original' (int: $libro_int), Año: $anio");

    // Validar datos con mejor manejo de errores
    if ($numero_int < 1 || $numero_int > 999999) {
        throw new Exception("El número del expediente debe estar entre 1 y 999999.");
    }
    if ($folio_int < 1 || $folio_int > 999999) {
        throw new Exception("El folio debe estar entre 1 y 999999.");
    }
    if ($libro_int < 1 || $libro_int > 999999) {
        throw new Exception("El libro debe estar entre 1 y 999999.");
    }
    if ($anio < 1973 || $anio > 2030) {
        throw new Exception("El año debe estar entre 1973 y 2030.");
    }
    
    if (!preg_match('/^[A-Z]$/', $letra)) {
        throw new Exception("La letra es inválida. Debe ser una letra de A a Z.");
    }

    // Validar CAPTCHA
    if (!isset($_SESSION['captcha_code'])) {
        throw new Exception("Sesión de CAPTCHA inválida. Recargue la página.");
    }
    
    $captcha_ingresado = strtoupper(trim($_POST['captcha']));
    $captcha_correcto = $_SESSION['captcha_code'];
    
    if ($captcha_ingresado !== $captcha_correcto) {
        throw new Exception("El código de verificación es incorrecto. Código ingresado: '$captcha_ingresado', esperado: '$captcha_correcto'");
    }

   // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consultar expediente - Usar tanto valores originales como enteros para máxima compatibilidad
    $sql = "SELECT * FROM expedientes 
            WHERE (numero = :numero_original OR numero = :numero_int)
            AND letra = :letra 
            AND (folio = :folio_original OR folio = :folio_int)
            AND (libro = :libro_original OR libro = :libro_int)
            AND anio = :anio 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    
    // Debug: mostrar la consulta y parámetros
    error_log("Consulta SQL: " . $sql);
    error_log("Parámetros: numero_original='$numero_original', numero_int=$numero_int, letra='$letra', folio_original='$folio_original', folio_int=$folio_int, libro_original='$libro_original', libro_int=$libro_int, anio=$anio");
    
    $stmt->execute([
        ':numero_original' => $numero_original,
        ':numero_int' => $numero_int,
        ':letra' => $letra,
        ':folio_original' => $folio_original,
        ':folio_int' => $folio_int,
        ':libro_original' => $libro_original,
        ':libro_int' => $libro_int,
        ':anio' => $anio
    ]);

    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: mostrar resultado
    error_log("Expediente encontrado: " . ($expediente ? "SI" : "NO"));

    // Agregar después de obtener el expediente
    if ($expediente) {
    // Consultar historial de lugares
    $sql = "SELECT 
            fecha_cambio,
            DATE_FORMAT(fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            lugar_anterior,
            lugar_nuevo,
            tipo_movimiento
        FROM historial_lugares 
        WHERE expediente_id = :id
        ORDER BY fecha_cambio ASC";
                
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $expediente['id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar después de obtener el historial
    if ($expediente && !empty($historial)) {
        // Calcular días transcurridos
        $primera_fecha = strtotime($expediente['fecha_hora_ingreso']);
        $ultima_fecha = strtotime(end($historial)['fecha_cambio']);
        
        $diferencia = $ultima_fecha - $primera_fecha;
        $dias_transcurridos = floor($diferencia / (60 * 60 * 24));
        $horas_transcurridas = floor(($diferencia % (60 * 60 * 24)) / (60 * 60));
    }

    // Limpiar CAPTCHA usado
    unset($_SESSION['captcha_code']);

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Consulta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="publico/css/estilos.css">
    <style>
.tracking-timeline {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.tracking-timeline::after {
    content: '';
    position: absolute;
    width: 6px;
    background-color: #e9ecef;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -3px;
}

.tracking-container {
    padding: 10px 40px;
    position: relative;
    width: 50%;
}

.tracking-container::after {
    content: '';
    position: absolute;
    width: 25px;
    height: 25px;
    right: -17px;
    background-color: white;
    border: 4px solid #0d6efd;
    top: 15px;
    border-radius: 50%;
    z-index: 1;
}

.tracking-left {
    left: 0;
}

.tracking-right {
    left: 50%;
}

.tracking-right::after {
    left: -16px;
}

.tracking-content {
    padding: 20px;
    background-color: white;
    position: relative;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.tracking-content h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.tracking-content p {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.progress {
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
}

.progress-bar {
    font-size: 0.9rem;
    line-height: 25px;
    font-weight: 500;
}

.card-title {
    color: #495057;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.bi-calendar-range {
    color: #198754;
    margin-right: 0.5rem;
}

@media screen and (max-width: 600px) {
    .tracking-timeline::after {
        left: 31px;
    }
    
    .tracking-container {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .tracking-right {
        left: 0%;
    }
    
    .tracking-container::after {
        left: 15px;
    }
}
</style>
</head>
<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <img src="publico/imagen/LOGOCDE.png" alt="Logo" style="height:116px;">
            <h2 class="titulo-principal mt-2">Resultado de la Consulta</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4 text-center" style="font-size:1.5rem; font-weight:bold; color:#0d6efd; background:#e9ecef; padding:15px; border-radius:8px;">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>
                    Expediente N°: <?= e($numero_original) ?>/<?= e($letra) ?>/<?= e($folio_original) ?>/<?= e($libro_original) ?>/<?= e($anio) ?>
                </h3>

                <?php if ($expediente): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 200px">Número:</th>
                                    <td><?= e($expediente['numero']) ?></td>
                                </tr>
                                <tr>
                                    <th>Letra:</th>
                                    <td><?= e($expediente['letra']) ?></td>
                                </tr>
                                <tr>
                                    <th>Folio:</th>
                                    <td><?= e($expediente['folio']) ?></td>
                                </tr>
                                <tr>
                                    <th>Libro:</th>
                                    <td><?= e($expediente['libro']) ?></td>
                                </tr>
                                <tr>
                                    <th>Año:</th>
                                    <td><?= e($expediente['anio']) ?></td>
                                </tr>
                                <tr>
                                    <th>Fecha de Ingreso:</th>
                                    <td><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></td>
                                </tr>
                               
                                <tr>
                                    <th>Extracto:</th>
                                    <td><?= e($expediente['extracto']) ?></td>
                                </tr>
                                <tr>
                                    <th>Iniciador:</th>
                                    <td><?= e($expediente['iniciador']) ?></td>
                                </tr>
                               
                            </tbody>
                        </table>
                    </div>

                    <!-- Agregar después de la tabla de datos del expediente -->
                    <?php if ($expediente && !empty($historial)): ?>
                        <div class="mt-5">
                            <h4 class="mb-4">Historial de Ubicaciones</h4>
                            <div class="tracking-timeline">
                                <!-- Mostrar lugar inicial -->
                                <div class="tracking-container tracking-left">
                                    <div class="tracking-content">
                                        <h3>Ingreso del Expediente</h3>
                                        <p>Ubicación: Mesa de Entrada</p>
                                        <p class="text-muted">
                                            <i class="bi bi-clock"></i> 
                                            <?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Mostrar historial de cambios -->
                                <?php foreach ($historial as $index => $pase): ?>
                                    <div class="tracking-container <?= $index % 2 == 0 ? 'tracking-right' : 'tracking-left' ?>">
                                        <div class="tracking-content">
                                            <h3>
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                Traslado de Expediente
                                            </h3>
                                            <p>
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-arrow-right-circle"></i>
                                                    <?= e($pase['tipo_movimiento']) ?>
                                                </span>
                                            </p>
                                          
                                            <p>
                                                <span class="fw-semibold text-secondary">
                                                    <i class="bi bi-box-arrow-in-right"></i> A:
                                                </span>
                                                <span class="badge bg-success"><?= e($pase['lugar_nuevo']) ?></span>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar-event"></i>
                                                <?= $pase['fecha_formateada'] ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Agregar después del div tracking-timeline -->
                    <?php if ($expediente && !empty($historial)): ?>
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-range"></i> 
                                    Tiempo Total de Tramitación
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 25px;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: 100%;" 
                                             aria-valuenow="100" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $dias_transcurridos ?> días, <?= $horas_transcurridas ?> horas
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <p class="mb-0">
                                        <strong>Desde:</strong> <?= date('d/m/Y H:i', $primera_fecha) ?>
                                        <strong class="ms-3">Hasta:</strong> <?= date('d/m/Y H:i', $ultima_fecha) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No se encontró el expediente solicitado
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left"></i> Nueva Consulta
                    </a>
                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>