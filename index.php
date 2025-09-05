<?php
// Iniciar sesión de forma segura
session_start();
session_regenerate_id(true);

// Configurar headers de seguridad mejorados
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; script-src 'self' https://cdn.jsdelivr.net;");
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), camera=()');

// Función para escapar output de forma segura
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generar captcha más seguro
$caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha = '';
try {
    for ($i = 0; $i < 4; $i++) {
        $captcha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    $_SESSION['captcha'] = password_hash($captcha, PASSWORD_DEFAULT);
} catch (Exception $e) {
    error_log('Error al generar captcha: ' . $e->getMessage());
    die('Error del sistema');
}

// Validar que la sesión esté activa
if (session_status() !== PHP_SESSION_ACTIVE) {
    die('Error de sesión');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Consulta de Expedientes</title>

    <!-- CSS con SRI hash -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
        crossorigin="anonymous">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="publico/css/estilos.css">

</head>

<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <img src="publico/imagen/LOGOCDE.png" alt="Logo" style="height:116px;">
            <h2 class="titulo-principal mt-2">Consulta de Expedientes</h2>
        </div>
        <div class="card card-form-publica">
            <div class="card-body px-4 py-5">

                <p class="mb-4 text-center">
                    Este sistema permite consultar el estado de expedientes ingresados en el <strong>Concejo Deliberante de Eldorado</strong>.<br>
                    Si tiene dudas, comuníquese con Mesa de Entradas al <strong>(03751) 424340</strong>.<br>
                    Complete el formulario para realizar su consulta sobre expedientes legislativos.
                </p>

                <!-- Leyenda de campos requeridos -->
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle"></i> Los campos marcados con <span class="text-danger">*</span> son requeridos
                </p>

            <form action="resultados_publico.php" method="post" autocomplete="off">
                    <!-- Campo oculto para CSRF -->


                    <div class="row g-3 my-1">
                        <!--  Numero-->
                        <div class="col-md-4">
                            <label for="numero" class="form-label">Número *</label>
                            <input type="text"
                                id="numero"
                                name="numero"
                                class="form-control"
                                placeholder="Ej: 132"
                                pattern="[0-9]{1,6}"
                                maxlength="6"
                                title="Solo números, máximo 6 dígitos"
                                required>
                        </div>

                        <!--  Letra-->
                        <div class="col-md-4">
                            <label for="letra" class="form-label">Letra *</label>
                            <select id="letra"
                                name="letra"
                                class="form-select"
                                required>
                                <option value="">Elige una letra</option>
                                <?php foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $l): ?>
                                    <option value="<?= e($l) ?>"><?= e($l) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!--  Folio-->
                        <div class="col-md-4">
                            <label for="folio" class="form-label">Folio *</label>
                            <input type="text"
                                id="folio"
                                name="folio"
                                class="form-control"
                                placeholder="Ej: 1234"
                                pattern="[0-9]{1,6}"
                                maxlength="6"
                                title="Solo números, máximo 6 dígitos"
                                required>
                        </div>
                    </div>
                    <div class="row g-3 my-1">

                        <!--  Libro-->
                        <div class="col-md-4">
                            <label for="libro" class="form-label">Libro *</label>
                            <input type="text"
                                id="libro"
                                name="libro"
                                class="form-control"
                                placeholder="Ej: 1234"
                                pattern="[0-9]{1,6}"
                                maxlength="6"
                                title="Solo números, máximo 6 dígitos"
                                required>
                        </div>
                        <!--  Año-->
                        <div class="col-md-4">
                            <label for="anio" class="form-label">Año *</label>
                            <select id="anio" name="anio" class="form-select" required>
                                <option value="">Elige un año</option>
                                <?php for ($y = 1973; $y <= 2030; $y++): ?>
                                    <option value="<?= e($y) ?>"><?= e($y) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 my-1">
                        <!--  Captcha-->
                    <div class="col-md-8">
                        <label for="captcha" class="form-label">Ingrese el código *</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                id="captcha"
                                name="captcha"
                                class="form-control "
                                maxlength="4"
                                pattern="[A-Z0-9]{4}"
                                autocomplete="off"
                                oninput="this.value = this.value.toUpperCase();"
                                required>
                            <span class="badge bg-secondary fs-5" style="letter-spacing:2px; user-select: none;"><?= e($captcha) ?></span>
                        </div>
                        <div class="form-text">Ingrese los 4 caracteres que ve en el recuadro exactamente como aparecen.</div>
                    </div>
                    </div>





                    
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <button type="reset" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-eraser"></i> Limpiar Campos
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        
                    </div>
            </div>
            </form>
            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='alert alert-danger'>" . e($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);
            }
            ?>
        </div>
    </div>
    </div>
</body>
</html>