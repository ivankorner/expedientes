<?php
session_start();

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=Iniciadores;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consultar personas físicas
    $stmt = $db->query("SELECT id, CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo 
                        FROM persona_fisica 
                        ORDER BY apellido, nombre");
    $personas_fisicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar personas jurídicas
    $stmt = $db->query("SELECT id, CONCAT(razon_social, ' (', cuit, ')') as nombre_completo 
                        FROM persona_juri_entidad 
                        ORDER BY razon_social");
    $personas_juridicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar concejales
    $stmt = $db->query("SELECT id, CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo 
                        FROM concejales 
                        ORDER BY apellido, nombre");
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar iniciadores: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Remover los var_dump de debug
// var_dump($personas_fisicas);
// var_dump($personas_juridicas);
// var_dump($concejales);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carga de Expediente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="/expedientes/publico/css/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body>
    <!-- HEADER CON LOGO (igual que dashboard) -->
    <nav class="navbar navbar-expand-lg header-dashboard shadow-sm py-3">
        <div class="container-fluid d-flex align-items-center justify-content-between px-0">
            <div class="d-flex align-items-center">
                <img src="/expedientes/publico/imagen/LOGOCDE.png" alt="Logo" class="logo-header me-3" style="height:76px;">
                <span class="fs-4 fw-bold titulo-header">Expedientes</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-secondary">Usuario: <strong>Admin</strong></span>
                <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">


            <!-- Sidebar -->
            <?php require '../vistas/sidebar.php'; ?>
            <!-- Sidebar -->


            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="main-box carga">
                    <h1 class="titulo-principal mb-4 text-center">Carga de Expediente</h1>
                    <?php
                    session_start();

                    if (isset($_SESSION['mensaje'])) {
                        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
                        // Convertir tipo de Bootstrap a SweetAlert2
                        $icon = match($tipo) {
                            'success' => 'success',
                            'danger' => 'error',
                            'warning' => 'warning',
                            default => 'info'
                        };
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
                                    icon: '<?= $icon ?>',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#0d6efd'
                                });
                            });
                        </script>
                        <?php
                        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
                    }
                    ?>
                    <form action="procesar_carga_expedientes.php" method="post" autocomplete="off">
                        <div class="row g-7 mb-4">
                            <!--  Numero-->
                            <div class="col-md-4 mb-2">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text"
                                    id="numero"
                                    name="numero"
                                    class="form-control"
                                    placeholder="Ej: 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos"
                                    required>
                            </div>
                            <!--  Letra-->
                            <div class="col-md-4 mb-2">
                                <label for="letra" class="form-label">Letra *</label>
                                <select id="letra"
                                    name="letra"
                                    class="form-select"
                                    required>
                                    <option value="">Elige una letra</option>
                                    <?php foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $l): ?>
                                        <option value="<?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!--  Folio-->
                            <div class="col-md-4 mb-2">
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
                            <!--  Libro-->
                            <div class="col-md-4 mb-2">
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
                            <div class="col-md-3 mb-2">
                                <label for="anio" class="form-label">Año *</label>
                                <select id="anio" name="anio" class="form-select" required>
                                    <option value="">Elige un año</option>
                                    <?php for ($y = 1973; $y <= 2030; $y++): ?>
                                        <option value="<?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <!--  Fecha y hora de ingreso -->
                            <div class="col-md-5 mb-2">
                                <label for="fecha_hora_ingreso" class="form-label">Fecha y Hora de Ingreso *</label>
                                <input type="datetime-local" id="fecha_hora_ingreso" name="fecha_hora_ingreso" class="form-control" required>
                            </div>

                            <!--  Lugar -->
                            <div class="col-md-4 mb-2">
                                <label for="lugar" class="form-label">Lugar *</label>
                                <select id="lugar" name="lugar" class="form-select" required>
                                    <option value="">Seleccione un lugar</option>
                                    <option value="Mesa de Entrada">Mesa de Entrada</option>
                                    <option value="Comision I">Comisión I</option>
                                    <option value="Comision II">Comisión II</option>
                                    <option value="Comision III">Comisión III</option>
                                    <option value="Comision IV">Comisión IV</option>
                                    <option value="Comision V">Comisión V</option>
                                    <option value="Comision VI">Comisión VI</option>
                                    <option value="Comision VII">Comisión VII</option>
                                    <option value="Archivo">Archivo</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un lugar</div>
                            </div>


                            <!--  Extracto -->
                            <div class="col-12 mb-2">
                                <label for="extracto" class="form-label">Extracto *</label>
                                <textarea id="extracto" 
                                          name="extracto" 
                                          class="form-control" 
                                          maxlength="300" 
                                          rows="3" 
                                          placeholder="Ingrese un extracto (máximo 300 caracteres)"
                                          required></textarea>
                                <div class="form-text">Máximo 300 caracteres.</div>
                                <div class="invalid-feedback">Por favor ingrese un extracto</div>
                            </div>

                            <!--  Iniciador -->
                            <div class="col-12 mb-2">
                                <label for="iniciador" class="form-label">Iniciador *</label>
                                <select id="iniciador" name="iniciador" class="form-select" required>
                                    <option value="">Seleccione un iniciador...</option>
                                    <?php if (!empty($personas_fisicas)): ?>
                                        <optgroup label="Personas Físicas">
                                            <?php foreach ($personas_fisicas as $persona): ?>
                                                <option value="PF-<?= $persona['id'] ?>">
                                                    <?= htmlspecialchars($persona['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php if (!empty($personas_juridicas)): ?>
                                        <optgroup label="Personas Jurídicas">
                                            <?php foreach ($personas_juridicas as $entidad): ?>
                                                <option value="PJ-<?= $entidad['id'] ?>">
                                                    <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php if (!empty($concejales)): ?>
                                        <optgroup label="Concejales">
                                            <?php foreach ($concejales as $concejal): ?>
                                                <option value="CO-<?= $concejal['id'] ?>">
                                                    <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un iniciador</div>
                            </div>












                        </div>

                       





                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 en el campo iniciador
        $('#iniciador').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccione o busque un iniciador...',
            allowClear: true,
            language: 'es'
        });
    });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Validar números positivos
    const numeroInputs = document.querySelectorAll('input[pattern="[0-9]{1,6}"]');
    numeroInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
    });

    // Validar letra mayúscula
    const letraSelect = document.getElementById('letra');
    letraSelect.addEventListener('change', function() {
        this.value = this.value.toUpperCase();
    });

    // Validar extracto
    const extracto = document.getElementById('extracto');
    const MAX_EXTRACTO = 300;
    
    extracto.addEventListener('input', function() {
        const remaining = MAX_EXTRACTO - this.value.length;
        this.nextElementSibling.textContent = `Caracteres restantes: ${remaining}`;
        
        if (this.value.length > MAX_EXTRACTO) {
            this.value = this.value.substring(0, MAX_EXTRACTO);
        }
    });
});
</script>
<script>
    // Función para mostrar errores
    function mostrarError(mensaje) {
        Swal.fire({
            title: 'Error',
            text: mensaje,
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0d6efd'
        });
    }

    // Función para validar el formulario
    function validarFormulario(form) {
        const campos = {
            numero: 'Número',
            letra: 'Letra',
            folio: 'Folio',
            libro: 'Libro',
            anio: 'Año',
            fecha_hora_ingreso: 'Fecha y Hora de Ingreso',
            lugar: 'Lugar',
            extracto: 'Extracto',
            iniciador: 'Iniciador'
        };

        for (let [id, nombre] of Object.entries(campos)) {
            const campo = form.querySelector(`#${id}`);
            if (!campo.value.trim()) {
                mostrarError(`El campo "${nombre}" es obligatorio`);
                campo.focus();
                return false;
            }
        }

        return true;
    }
</script>

</body>
</html>