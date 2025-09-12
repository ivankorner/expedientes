<?php
/**
 * Generador de comprobante de expediente
 * Versión HTML optimizada para imprimir como PDF
 */
session_start();

// Validar que se reciba el ID del expediente
$expediente_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$expediente_id) {
    $_SESSION['mensaje'] = "ID de expediente inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener datos del expediente
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$expediente_id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    // URL para el QR
    $qr_url = 'https://expedientescde.online/resultados_publico.php?numero=' . $expediente['numero'] . 
              '&letra=' . $expediente['letra'] . 
              '&folio=' . $expediente['folio'] . 
              '&libro=' . $expediente['libro'] . 
              '&anio=' . $expediente['anio'];

    // Generar QR usando API de QR Server (gratuita y confiable)
    $qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_url);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al generar comprobante: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= date('Ymd_Hi') ?>_Expediente_<?= htmlspecialchars($expediente['numero']) ?>_<?= htmlspecialchars($expediente['letra']) ?>_<?= htmlspecialchars($expediente['folio']) ?>_<?= htmlspecialchars($expediente['libro']) ?>_<?= htmlspecialchars($expediente['anio']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            body { 
                font-size: 12pt; 
                line-height: 1.4;
                color: #000 !important;
                background: white !important;
            }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .container { max-width: none !important; margin: 0 !important; padding: 0 !important; }
            .btn { display: none !important; }
            a { color: #000 !important; text-decoration: none !important; }
        }
        
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .comprobante {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .titulo {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin: 10px 0;
        }
        
        .subtitulo {
            font-size: 18px;
            color: #6c757d;
        }
        
        .expediente-numero {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: black;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 30px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .datos-expediente {
            background: #f8f9fa;
            border-left: 5px solid #0d6efd;
            padding: 25px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .campo {
            margin: 15px 0;
            display: flex;
            align-items: flex-start;
        }
        
        .campo-label {
            font-weight: bold;
            min-width: 150px;
            color: #495057;
        }
        
        .campo-valor {
            flex: 1;
            margin-left: 15px;
        }
        
        .extracto {
            background: white;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        
        .info-carga {
            border-top: 2px solid #dee2e6;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .seguimiento {
            background: linear-gradient(135deg, #e7f3ff, #cce7ff);
            border: 2px solid #0d6efd;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: center;
        }
        
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .qr-code {
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: white;
            max-width: 200px;
            height: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .qr-fallback {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            color: #856404;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }
        
        .url-link {
            font-weight: bold;
            color: #0d6efd;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Botones de acción (solo en pantalla) -->
        <div class="no-print mb-4 text-center">
            <button onclick="descargarPDF()" class="btn btn-primary btn-lg me-3">
                <i class="bi bi-download"></i> Descargar PDF
            </button>
            <button onclick="window.print()" class="btn btn-success btn-lg me-3">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Cerrar
            </button>
        </div>

        <div class="comprobante">
            <!-- Header -->
            <div class="header">
                <img src="../publico/imagen/LOGOCDE.png" alt="Logo CDE" class="logo">
                <div class="titulo">CONCEJO DELIBERANTE ELDORADO</div>
                <div class="subtitulo">COMPROBANTE DE EXPEDIENTE</div>
            </div>

            <!-- Número de expediente destacado -->
            <div class="expediente-numero">
                EXPEDIENTE N° <?= htmlspecialchars($expediente['numero']) ?>/<?= htmlspecialchars($expediente['letra']) ?>/<?= htmlspecialchars($expediente['folio']) ?>/<?= htmlspecialchars($expediente['libro']) ?>/<?= htmlspecialchars($expediente['anio']) ?>
            </div>

            <!-- Datos del expediente -->
            <div class="datos-expediente">
                <div class="campo">
                    <div class="campo-label">Fecha de Ingreso:</div>
                    <div class="campo-valor"><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></div>
                </div>

                <div class="campo">
                    <div class="campo-label">Iniciador:</div>
                    <div class="campo-valor"><?= htmlspecialchars($expediente['iniciador']) ?></div>
                </div>

                <div class="campo">
                    <div class="campo-label">Lugar Actual:</div>
                    <div class="campo-valor"><?= htmlspecialchars($expediente['lugar']) ?></div>
                </div>

                <div class="campo">
                    <div class="campo-label">Extracto:</div>
                    <div class="campo-valor">
                        <div class="extracto">
                            <?= nl2br(htmlspecialchars($expediente['extracto'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de carga -->
            <div class="info-carga">
                <h5 class="mb-3"><i class="bi bi-info-circle"></i> Información de Carga</h5>
                <div class="campo">
                    <div class="campo-label">Fecha de Carga:</div>
                    <div class="campo-valor"><?= date('d/m/Y H:i') ?></div>
                </div>
                <div class="campo">
                    <div class="campo-label">Usuario:</div>
                    <div class="campo-valor"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Sistema') ?></div>
                </div>
            </div>

            <!-- Información de seguimiento -->
            <div class="seguimiento">
                <h4 class="mb-3"><i class="bi bi-search"></i> SEGUIMIENTO DEL EXPEDIENTE</h4>
                <p class="mb-3">Puede seguir el movimiento de su expediente a través de esta web:</p>
                <div class="url-link">https://expedientescde.online/</div>
            </div>

            <!-- Código QR -->
            <div class="qr-section">
                <h5 class="mb-3"><i class="bi bi-qr-code"></i> Escanee el código QR para acceso directo:</h5>
                <div style="display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <img src="<?= $qr_image_url ?>" 
                         alt="Código QR para expediente <?= htmlspecialchars($expediente['numero']) ?>/<?= htmlspecialchars($expediente['letra']) ?>" 
                         class="qr-code" 
                         onerror="this.style.display='none'; document.getElementById('qr-fallback-<?= $expediente_id ?>').style.display='block';"
                         onload="console.log('QR Code cargado correctamente');">
                    
                    <!-- Fallback si no carga el QR -->
                    <div id="qr-fallback-<?= $expediente_id ?>" style="display: none; text-align: center; padding: 20px; border: 2px dashed #ccc; border-radius: 5px;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #ffc107;"></i>
                        <p class="mt-2 mb-2">No se pudo cargar el código QR</p>
                        <p class="small">Acceda manualmente a: <br><strong><?= htmlspecialchars($qr_url) ?></strong></p>
                    </div>
                </div>
                <p class="mt-3 small text-muted">
                    Escanee este código con su dispositivo móvil para acceder directamente al seguimiento de su expediente
                </p>
                <p class="mt-2 small" style="color: #0d6efd;">
                    <i class="bi bi-link-45deg"></i> URL: <?= htmlspecialchars($qr_url) ?>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><i class="bi bi-calendar"></i> Este documento fue generado automáticamente el <?= date('d/m/Y H:i:s') ?></p>
                <p><i class="bi bi-building"></i> Sistema de Gestión de Expedientes - Concejo Deliberante Eldorado</p>
            </div>
        </div>
    </div>

    <script>
        // Configurar nombre del archivo para descarga
        const fechaHoy = new Date();
        const fechaFormateada = fechaHoy.getFullYear() + 
                               String(fechaHoy.getMonth() + 1).padStart(2, '0') + 
                               String(fechaHoy.getDate()).padStart(2, '0') + '_' +
                               String(fechaHoy.getHours()).padStart(2, '0') + 
                               String(fechaHoy.getMinutes()).padStart(2, '0');
        
        const numeroExpediente = '<?= htmlspecialchars($expediente['numero']) ?>_<?= htmlspecialchars($expediente['letra']) ?>_<?= htmlspecialchars($expediente['folio']) ?>_<?= htmlspecialchars($expediente['libro']) ?>_<?= htmlspecialchars($expediente['anio']) ?>';
        
        const nombreArchivo = fechaFormateada + '_Expediente_' + numeroExpediente + '.pdf';
        
        // Auto-abrir diálogo de impresión después de cargar la página
        window.addEventListener('load', function() {
            setTimeout(function() {
                // Configurar el título del documento para la descarga
                document.title = nombreArchivo.replace('.pdf', '');
                
                // Mostrar modal con opciones
                if (confirm('El comprobante está listo.\n\n¿Desea descargar el PDF automáticamente?\n\n(Seleccione "Guardar como PDF" en la ventana de impresión)')) {
                    // Configurar la descarga automática
                    window.print();
                }
            }, 500);
        });
        
        // Función para descargar manualmente
        function descargarPDF() {
            window.print();
        }
        
        // Cerrar ventana después de imprimir (si se abrió como popup)
        window.addEventListener('afterprint', function() {
            if (window.opener) {
                setTimeout(function() {
                    if (confirm('¿Desea cerrar esta ventana?')) {
                        window.close();
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>