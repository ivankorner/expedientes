<?php
session_start();
require 'header.php';
require 'head.php';

try {
     // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
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



<body>
   
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
                        $expediente_id = $_SESSION['expediente_id'] ?? null;
                        
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
                                <?php if ($tipo === 'success' && $expediente_id): ?>
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
                                    icon: '<?= $icon ?>',
                                    html: '<p class="mb-3">El expediente se ha guardado correctamente en el sistema.</p>' +
                                          '<div class="d-grid gap-2">' +
                                          '<button type="button" class="btn btn-primary btn-lg" onclick="generarPDF(<?= $expediente_id ?>)">' +
                                          '<i class="bi bi-file-earmark-pdf"></i> Descargar Comprobante PDF' +
                                          '</button>' +
                                          '</div>',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Continuar',
                                    confirmButtonColor: '#0d6efd',
                                    allowOutsideClick: false,
                                    width: '500px'
                                });
                                <?php else: ?>
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
                                    icon: '<?= $icon ?>',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#0d6efd'
                                });
                                <?php endif; ?>
                            });
                            
                            function generarPDF(expedienteId) {
                                // Abrir PDF con descarga automática y nombre específico
                                window.open('pdf_auto_descarga.php?id=' + expedienteId, '_blank');
                            }
                        </script>
                        <?php
                        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['expediente_id']);
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
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
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
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
                                    required>
                                
                            </div>
                            <!--  Libro-->
                            <div class="col-md-4 mb-2">
                                <label for="libro" class="form-label">Libro *</label>
                                <input type="text"
                                    id="libro"
                                    name="libro"
                                    class="form-control"
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
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
                                    
                                    <option value="Mesa de Entrada">Mesa de Entrada</option>
                                    
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un lugar</div>
                            </div>


                            <!--  Extracto -->
                            <div class="col-12 mb-2">
                                <label for="extracto" class="form-label">Extracto *</label>
                                <textarea id="extracto" 
                                          name="extracto" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Ingrese un extracto"
                                          required></textarea>
                                <div class="form-text">Sin límite de caracteres.</div>
                                <div class="invalid-feedback">Por favor ingrese un extracto</div>
                            </div>

                            <!--  Iniciador -->
                            <div class="col-12 mb-4">
                                <div class="card border-success shadow-sm">
                                    <div class="card-header bg-success bg-opacity-10 border-bottom-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="mb-0 text-success fw-bold">
                                                <i class="bi bi-person-plus-fill me-2"></i>
                                                ¿Quién inicia este expediente?
                                            </h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-asterisk"></i> Obligatorio
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <!-- Búsqueda inteligente unificada -->
                                        <div class="mb-4">
                                            <label for="buscar_iniciador" class="form-label fw-bold mb-3">
                                                <i class="bi bi-search me-2 text-primary"></i>
                                                Buscar Iniciador
                                            </label>
                                            <div class="input-group input-group-lg shadow-sm">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" 
                                                       id="buscar_iniciador" 
                                                       class="form-control fs-5" 
                                                       placeholder="Escriba el nombre, DNI, CUIT o bloque del iniciador..."
                                                       autocomplete="off">
                                                <button type="button" 
                                                        id="limpiar_busqueda" 
                                                        class="btn btn-outline-secondary"
                                                        title="Limpiar búsqueda">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Resultados de búsqueda en vivo -->
                                            <div id="resultados_busqueda" class="mt-3" style="display: none;">
                                                <div class="border rounded-3 bg-light p-3">
                                                    <h6 class="text-muted mb-2">
                                                        <i class="bi bi-list-ul me-1"></i>
                                                        Resultados encontrados:
                                                    </h6>
                                                    <div id="lista_resultados"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Selección actual -->
                                        <div id="iniciador_seleccionado" class="mb-3" style="display: none;">
                                            <label class="form-label fw-bold text-success">
                                                <i class="bi bi-check-circle-fill me-2"></i>
                                                Iniciador Seleccionado
                                            </label>
                                            <div class="alert alert-success border-success d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-person-check-fill me-2"></i>
                                                    <span id="nombre_seleccionado" class="fw-bold"></span>
                                                    <small id="tipo_seleccionado" class="text-muted ms-2"></small>
                                                </div>
                                                <button type="button" 
                                                        id="cambiar_iniciador" 
                                                        class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-pencil"></i> Cambiar
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Campo oculto para el valor -->
                                        <select id="iniciador" name="iniciador" class="d-none" required>
                                            <option value="">Seleccione un iniciador...</option>
                                            <?php if (!empty($personas_fisicas)): ?>
                                                <optgroup label="👤 Personas Físicas">
                                                    <?php foreach ($personas_fisicas as $persona): ?>
                                                        <option value="PF-<?= $persona['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($persona['nombre_completo']) ?>"
                                                                data-tipo="Persona Física"
                                                                data-search="<?= strtolower(htmlspecialchars($persona['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($persona['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>

                                            <?php if (!empty($personas_juridicas)): ?>
                                                <optgroup label="🏢 Personas Jurídicas">
                                                    <?php foreach ($personas_juridicas as $entidad): ?>
                                                        <option value="PJ-<?= $entidad['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($entidad['nombre_completo']) ?>"
                                                                data-tipo="Persona Jurídica"
                                                                data-search="<?= strtolower(htmlspecialchars($entidad['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>

                                            <?php if (!empty($concejales)): ?>
                                                <optgroup label="🏛️ Concejales">
                                                    <?php foreach ($concejales as $concejal): ?>
                                                        <option value="CO-<?= $concejal['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($concejal['nombre_completo']) ?>"
                                                                data-tipo="Concejal"
                                                                data-search="<?= strtolower(htmlspecialchars($concejal['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>
                                        </select>

                                        <!-- Mensaje de ayuda -->
                                        <div id="mensaje_ayuda" class="alert alert-info border-info">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-info-circle-fill me-3 mt-1 text-info"></i>
                                                <div>
                                                    <h6 class="mb-2">💡 ¿Cómo buscar?</h6>
                                                    <ul class="mb-0 small">
                                                        <li><strong>Por nombre:</strong> "Juan", "María", "González"</li>
                                                        <li><strong>Por documento:</strong> "12345678", "20-12345678-9"</li>
                                                        <li><strong>Por bloque:</strong> "Frente", "Partido", "Bloque"</li>
                                                    </ul>
                                                    <p class="mb-0 mt-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-lightbulb"></i>
                                                            Tip: Escriba solo unas pocas letras y aparecerán las coincidencias
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Enlaces de acceso rápido -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <h6 class="text-muted mb-3">
                                                    <i class="bi bi-plus-circle me-1"></i>
                                                    ¿No encuentra al iniciador?
                                                </h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="carga_iniciador.php" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       target="_blank"
                                                       title="Se abrirá en una nueva ventana">
                                                        <i class="bi bi-person-plus"></i>
                                                        Agregar Persona Física
                                                    </a>
                                                    <a href="carga_persona_juri_entidad.php" 
                                                       class="btn btn-outline-info btn-sm" 
                                                       target="_blank"
                                                       title="Se abrirá en una nueva ventana">
                                                        <i class="bi bi-building-add"></i>
                                                        Agregar Persona Jurídica
                                                    </a>
                                                    <a href="carga_concejal.php" 
                                                       class="btn btn-outline-success btn-sm" 
                                                       target="_blank"
                                                       title="Se abrirá en una nueva ventana">
                                                        <i class="bi bi-person-badge"></i>
                                                        Agregar Concejal
                                                    </a>
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    <i class="bi bi-info-circle"></i>
                                                    Después de agregar un nuevo iniciador, actualice esta página para verlo en la lista
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>












                        </div>

                       





                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="reset" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Estilos adicionales para la búsqueda intuitiva -->
    <style>
        /* Estilos para el resaltado de búsqueda */
        mark {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            color: #856404;
        }
        
        /* Card de iniciador mejorado */
        .card.border-success {
            box-shadow: 0 0.25rem 1rem rgba(25, 135, 84, 0.15);
            transition: all 0.3s ease;
            border-width: 2px;
        }
        
        .card.border-success:hover {
            box-shadow: 0 0.5rem 2rem rgba(25, 135, 84, 0.25);
            transform: translateY(-2px);
        }
        
        /* Header del card mejorado */
        .card-header.bg-success.bg-opacity-10 {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
            border-bottom: 2px solid rgba(25, 135, 84, 0.2);
        }
        
        /* Campo de búsqueda principal */
        .input-group-lg .form-control.fs-5 {
            font-size: 1.25rem !important;
            padding: 1rem 1.25rem;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .input-group-lg .form-control.fs-5:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            transform: scale(1.02);
        }
        
        .input-group-lg .input-group-text.bg-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            border: 2px solid #0d6efd;
            font-size: 1.25rem;
            padding: 1rem 1.25rem;
        }
        
        /* Resultados de búsqueda */
        #resultados_busqueda {
            animation: fadeInDown 0.3s ease-out;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .resultado-item {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }
        
        .resultado-item:hover {
            border-color: #0d6efd;
            background: #f8f9ff;
            transform: translateX(5px);
            box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.15);
        }
        
        .resultado-item:active {
            transform: translateX(3px) scale(0.98);
        }
        
        .tipo-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
        }
        
        /* Iniciador seleccionado */
        #iniciador_seleccionado {
            animation: slideInUp 0.4s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #iniciador_seleccionado .alert {
            border-width: 2px;
            box-shadow: 0 0.25rem 0.5rem rgba(25, 135, 84, 0.15);
        }
        
        /* Botones de acción */
        #limpiar_busqueda {
            border: 2px solid #6c757d;
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
        }
        
        #limpiar_busqueda:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            transform: scale(1.05);
        }
        
        #cambiar_iniciador {
            transition: all 0.3s ease;
        }
        
        #cambiar_iniciador:hover {
            transform: scale(1.05);
            box-shadow: 0 0.25rem 0.5rem rgba(25, 135, 84, 0.3);
        }
        
        /* Enlaces de acceso rápido */
        .btn-outline-primary, .btn-outline-info, .btn-outline-success {
            transition: all 0.3s ease;
            border-width: 2px;
        }
        
        .btn-outline-primary:hover, .btn-outline-info:hover, .btn-outline-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        /* Alerts mejorados */
        .alert {
            border-width: 2px;
            border-radius: 0.75rem;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #cff4fc 0%, #b6effb 100%);
            border-color: #0dcaf0;
            color: #055160;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1eddd 0%, #badbcc 100%);
            border-color: #198754;
            color: #0f5132;
        }
        
        /* Badges mejorados */
        .badge.bg-success-subtle {
            background: linear-gradient(135deg, #d1eddd 0%, #badbcc 100%) !important;
            color: #0f5132 !important;
            border: 1px solid #198754 !important;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 1rem;
        }
        
        /* Efectos de hover para elementos interactivos */
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: #86b7fe;
        }
        
        /* Animación de pulso para elementos importantes */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }
        
        /* Responsividad mejorada */
        @media (max-width: 768px) {
            .input-group-lg .form-control.fs-5 {
                font-size: 1rem !important;
                padding: 0.75rem 1rem;
            }
            
            .input-group-lg .input-group-text {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .resultado-item {
                padding: 0.5rem 0.75rem;
            }
            
            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            
            .btn-sm {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Estados de carga */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mejoras visuales adicionales */
        .fw-bold {
            font-weight: 600 !important;
        }
        
        .text-success {
            color: #198754 !important;
        }
        
        .border-success {
            border-color: #198754 !important;
        }
        
        /* Iconos con animación */
        .bi {
            transition: transform 0.2s ease;
        }
        
        .btn:hover .bi {
            transform: scale(1.1);
        }
        
        /* Shadow personalizado */
        .shadow-sm {
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.075) !important;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const buscarIniciador = document.getElementById('buscar_iniciador');
        const limpiarBusqueda = document.getElementById('limpiar_busqueda');
        const resultadosBusqueda = document.getElementById('resultados_busqueda');
        const listaResultados = document.getElementById('lista_resultados');
        const iniciadorSeleccionado = document.getElementById('iniciador_seleccionado');
        const nombreSeleccionado = document.getElementById('nombre_seleccionado');
        const tipoSeleccionado = document.getElementById('tipo_seleccionado');
        const cambiarIniciador = document.getElementById('cambiar_iniciador');
        const selectIniciador = document.getElementById('iniciador');
        const mensajeAyuda = document.getElementById('mensaje_ayuda');

        // Obtener todas las opciones del select
        const todasLasOpciones = Array.from(selectIniciador.options)
            .filter(option => option.value !== '')
            .map(option => ({
                value: option.value,
                nombre: option.dataset.nombre,
                tipo: option.dataset.tipo,
                search: option.dataset.search,
                element: option
            }));

        console.log('Iniciadores cargados:', todasLasOpciones.length);

        // Función para buscar iniciadores
        function buscarIniciadores(termino) {
            if (!termino || termino.length < 2) {
                ocultarResultados();
                return;
            }

            const terminoLimpio = termino.toLowerCase().trim();
            const resultados = todasLasOpciones.filter(opcion => 
                opcion.search.includes(terminoLimpio)
            );

            mostrarResultados(resultados, terminoLimpio);
        }

        // Función para mostrar resultados
        function mostrarResultados(resultados, termino) {
            if (resultados.length === 0) {
                listaResultados.innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-search"></i>
                        <p class="mb-0">No se encontraron iniciadores con: "<strong>${termino}</strong>"</p>
                        <small>Pruebe con otros términos de búsqueda</small>
                    </div>
                `;
            } else {
                listaResultados.innerHTML = resultados.map(resultado => {
                    const nombreResaltado = resaltarTermino(resultado.nombre, termino);
                    const iconoTipo = obtenerIconoTipo(resultado.tipo);
                    const colorTipo = obtenerColorTipo(resultado.tipo);
                    
                    return `
                        <div class="resultado-item" data-value="${resultado.value}" data-nombre="${resultado.nombre}" data-tipo="${resultado.tipo}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${nombreResaltado}</div>
                                    <small class="text-muted">
                                        <i class="${iconoTipo} me-1"></i>
                                        ${resultado.tipo}
                                    </small>
                                </div>
                                <span class="tipo-badge bg-${colorTipo} text-white">
                                    ${resultado.tipo}
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');

                // Agregar eventos de clic a los resultados
                document.querySelectorAll('.resultado-item').forEach(item => {
                    item.addEventListener('click', function() {
                        seleccionarIniciador(
                            this.dataset.value,
                            this.dataset.nombre,
                            this.dataset.tipo
                        );
                    });
                });
            }

            mostrarContenedorResultados();
        }

        // Función para resaltar términos de búsqueda
        function resaltarTermino(texto, termino) {
            if (!termino) return texto;
            const regex = new RegExp(`(${termino})`, 'gi');
            return texto.replace(regex, '<mark>$1</mark>');
        }

        // Función para obtener icono según tipo
        function obtenerIconoTipo(tipo) {
            switch(tipo) {
                case 'Persona Física': return 'bi bi-person-fill';
                case 'Persona Jurídica': return 'bi bi-building-fill';
                case 'Concejal': return 'bi bi-person-badge-fill';
                default: return 'bi bi-person';
            }
        }

        // Función para obtener color según tipo
        function obtenerColorTipo(tipo) {
            switch(tipo) {
                case 'Persona Física': return 'primary';
                case 'Persona Jurídica': return 'info';
                case 'Concejal': return 'success';
                default: return 'secondary';
            }
        }

        // Función para seleccionar iniciador
        function seleccionarIniciador(value, nombre, tipo) {
            // Actualizar el select oculto
            selectIniciador.value = value;
            
            // Mostrar la selección
            nombreSeleccionado.textContent = nombre;
            tipoSeleccionado.textContent = `(${tipo})`;
            
            // Mostrar el área de selección y ocultar otros elementos
            iniciadorSeleccionado.style.display = 'block';
            ocultarResultados();
            mensajeAyuda.style.display = 'none';
            buscarIniciador.value = '';
            
            // Agregar clase de pulso para llamar la atención
            iniciadorSeleccionado.classList.add('pulse');
            setTimeout(() => {
                iniciadorSeleccionado.classList.remove('pulse');
            }, 3000);

            console.log('Iniciador seleccionado:', value, nombre, tipo);
        }

        // Función para mostrar contenedor de resultados
        function mostrarContenedorResultados() {
            resultadosBusqueda.style.display = 'block';
            mensajeAyuda.style.display = 'none';
        }

        // Función para ocultar resultados
        function ocultarResultados() {
            resultadosBusqueda.style.display = 'none';
            if (!selectIniciador.value) {
                mensajeAyuda.style.display = 'block';
            }
        }

        // Función para limpiar selección
        function limpiarSeleccion() {
            selectIniciador.value = '';
            iniciadorSeleccionado.style.display = 'none';
            mensajeAyuda.style.display = 'block';
            buscarIniciador.value = '';
            buscarIniciador.focus();
            ocultarResultados();
        }

        // Eventos del campo de búsqueda
        buscarIniciador.addEventListener('input', function() {
            const termino = this.value.trim();
            
            if (termino.length === 0) {
                ocultarResultados();
                return;
            }
            
            if (termino.length >= 2) {
                buscarIniciadores(termino);
            }
        });

        // Evento para limpiar búsqueda
        limpiarBusqueda.addEventListener('click', limpiarSeleccion);

        // Evento para cambiar iniciador
        cambiarIniciador.addEventListener('click', limpiarSeleccion);

        // Eventos de teclado
        buscarIniciador.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const primeraOpcion = document.querySelector('.resultado-item');
                if (primeraOpcion) {
                    primeraOpcion.click();
                }
            }
            
            if (e.key === 'Escape') {
                ocultarResultados();
                this.blur();
            }
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!buscarIniciador.contains(e.target) && !resultadosBusqueda.contains(e.target)) {
                if (!selectIniciador.value) {
                    ocultarResultados();
                }
            }
        });

        // Validación del formulario mejorada
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (!selectIniciador.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Iniciador requerido',
                    text: 'Debe seleccionar un iniciador para el expediente',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#198754'
                });
                
                // Enfocar el campo de búsqueda y agregar clase de error
                buscarIniciador.focus();
                buscarIniciador.classList.add('is-invalid');
                setTimeout(() => {
                    buscarIniciador.classList.remove('is-invalid');
                }, 3000);
                
                return false;
            }
        });

        // Auto-foco en el campo de búsqueda
        setTimeout(() => {
            buscarIniciador.focus();
        }, 500);

        // Mensaje de bienvenida si no hay iniciadores
        if (todasLasOpciones.length === 0) {
            listaResultados.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                    <h6 class="mt-3">No hay iniciadores disponibles</h6>
                    <p class="text-muted">Debe agregar al menos un iniciador antes de crear expedientes.</p>
                    <div class="mt-3">
                        <a href="carga_persona_fisica.php" class="btn btn-primary btn-sm me-2" target="_blank">
                            <i class="bi bi-person-plus"></i> Agregar Persona
                        </a>
                        <a href="carga_concejal.php" class="btn btn-success btn-sm" target="_blank">
                            <i class="bi bi-person-badge"></i> Agregar Concejal
                        </a>
                    </div>
                </div>
            `;
            mostrarContenedorResultados();
            mensajeAyuda.style.display = 'none';
        }
    });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Validar campos numéricos (preservando ceros a la izquierda)
    const numeroInputs = document.querySelectorAll('#numero, #folio, #libro');
    numeroInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Solo permitir números, conservando ceros a la izquierda
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
        
        // Agregar evento para ayuda visual
        input.addEventListener('focus', function() {
            if (this.placeholder) {
                this.dataset.originalPlaceholder = this.placeholder;
                this.placeholder = 'Ej: 0001, 0123, 1234';
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.dataset.originalPlaceholder) {
                this.placeholder = this.dataset.originalPlaceholder;
            }
        });
    });

    // Validar letra mayúscula
    const letraSelect = document.getElementById('letra');
    letraSelect.addEventListener('change', function() {
        this.value = this.value.toUpperCase();
    });

    // Validar extracto (sin límite de caracteres)
    const extracto = document.getElementById('extracto');
    
    extracto.addEventListener('input', function() {
        // Mostrar contador de caracteres sin límite
        const caracteresActuales = this.value.length;
        this.nextElementSibling.textContent = `Caracteres: ${caracteresActuales}`;
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