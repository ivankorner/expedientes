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
                                          maxlength="500" 
                                          rows="3" 
                                          placeholder="Ingrese un extracto (máximo 500 caracteres)"
                                          required></textarea>
                                <div class="form-text">Máximo 500 caracteres.</div>
                                <div class="invalid-feedback">Por favor ingrese un extracto</div>
                            </div>

                            <!--  Iniciador -->
                            <div class="col-12 mb-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0 text-primary">
                                            <i class="bi bi-person-plus-fill me-2"></i>
                                            Iniciador del Expediente *
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Campo de búsqueda rápida -->
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold mb-2">
                                                    <i class="bi bi-search me-1"></i>
                                                    Búsqueda Rápida
                                                </label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light">
                                                        <i class="bi bi-search text-muted"></i>
                                                    </span>
                                                    <input type="text" 
                                                           id="buscar_iniciador" 
                                                           class="form-control" 
                                                           placeholder="Escriba para buscar por nombre, apellido, DNI, CUIT o bloque..."
                                                           autocomplete="off">
                                                    <button type="button" 
                                                            id="limpiar_busqueda" 
                                                            class="btn btn-outline-danger">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text mt-2">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Puede buscar por cualquier parte del nombre, documento o bloque político
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Separador visual -->
                                        <hr class="my-3 border-primary border-opacity-25">

                                        <!-- Select de iniciador -->
                                        <div class="row">
                                            <div class="col-12">
                                                <label for="iniciador" class="form-label fw-semibold mb-2">
                                                    <i class="bi bi-person-check me-1"></i>
                                                    Seleccionar Iniciador
                                                </label>
                                                <select id="iniciador" name="iniciador" class="form-select form-select-lg" required>
                                                    <option value="">Seleccione un iniciador...</option>
                                                    <?php if (!empty($personas_fisicas)): ?>
                                                        <optgroup label="👤 Personas Físicas">
                                                            <?php foreach ($personas_fisicas as $persona): ?>
                                                                <option value="PF-<?= $persona['id'] ?>">
                                                                    <?= htmlspecialchars($persona['nombre_completo']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endif; ?>

                                                    <?php if (!empty($personas_juridicas)): ?>
                                                        <optgroup label="🏢 Personas Jurídicas">
                                                            <?php foreach ($personas_juridicas as $entidad): ?>
                                                                <option value="PJ-<?= $entidad['id'] ?>">
                                                                    <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endif; ?>

                                                    <?php if (!empty($concejales)): ?>
                                                        <optgroup label="🏛️ Concejales">
                                                            <?php foreach ($concejales as $concejal): ?>
                                                                <option value="CO-<?= $concejal['id'] ?>">
                                                                    <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    <?php endif; ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Por favor seleccione un iniciador
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Información adicional -->
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="alert alert-info alert-sm mb-0">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-lightbulb-fill me-2"></i>
                                                        <small>
                                                            <strong>Sugerencia:</strong> Use la búsqueda rápida para encontrar iniciadores más fácilmente. 
                                                            Presione <kbd>Enter</kbd> para seleccionar el primer resultado encontrado.
                                                        </small>
                                                    </div>
                                                </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Estilos adicionales para la búsqueda -->
    <style>
        /* Estilos para el resaltado de búsqueda */
        mark {
            background-color: #fff3cd;
            padding: 1px 2px;
            border-radius: 2px;
            font-weight: bold;
        }
        
        /* Card de iniciador mejorado */
        .card.border-primary {
            box-shadow: 0 0.125rem 0.5rem rgba(13, 110, 253, 0.15);
            transition: box-shadow 0.15s ease-in-out;
        }
        
        .card.border-primary:hover {
            box-shadow: 0 0.25rem 1rem rgba(13, 110, 253, 0.25);
        }
        
        /* Header del card */
        .card-header.bg-primary.bg-opacity-10 {
            border-bottom: 2px solid rgba(13, 110, 253, 0.2);
        }
        
        /* Mejorar el campo de búsqueda rápida */
        .input-group-lg .form-control {
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
        }
        
        .input-group-lg .input-group-text {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }
        
        #buscar_iniciador {
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        #limpiar_busqueda {
            border-radius: 0 0.5rem 0.5rem 0;
            transition: all 0.3s ease;
        }
        
        #limpiar_busqueda:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            transform: scale(1.05);
        }
        
        /* Mejorar Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 1rem + 2px);
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .select2-container--bootstrap-5 .select2-selection:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        /* Animación para el campo de búsqueda */
        #buscar_iniciador:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: #86b7fe;
            transform: scale(1.02);
        }
        
        /* Indicador visual cuando hay texto en búsqueda */
        #buscar_iniciador:not(:placeholder-shown) {
            background-color: #e7f3ff;
            border-color: #0d6efd;
            box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.25);
        }
        
        /* Estilo para las opciones de Select2 */
        .select2-results__option--highlighted {
            background-color: #0d6efd !important;
            color: white !important;
        }
        
        /* Mejorar la visualización de los grupos */
        .select2-results__group {
            background-color: #f8f9fa;
            font-weight: bold;
            padding: 12px 16px;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
            font-size: 0.9rem;
        }
        
        /* Separador personalizado */
        hr.border-primary {
            border-width: 2px;
            opacity: 0.3;
        }
        
        /* Alert mejorado */
        .alert-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .alert-info {
            background-color: #e7f3ff;
            border-color: #b3d7ff;
            color: #0c5460;
        }
        
        /* Labels mejorados */
        .form-label.fw-semibold {
            color: #495057;
            font-size: 0.95rem;
            letter-spacing: 0.025em;
        }
        
        /* Efectos hover para elementos interactivos */
        .input-group:hover .input-group-text {
            background-color: #e9ecef;
            transition: background-color 0.3s ease;
        }
        
        /* Estilo para kbd */
        kbd {
            background-color: #495057;
            color: white;
            padding: 0.1rem 0.3rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        /* Responsividad mejorada */
        @media (max-width: 768px) {
            .input-group-lg .form-control,
            .input-group-lg .input-group-text {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 en el campo iniciador con búsqueda mejorada
        $('#iniciador').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccione o busque un iniciador...',
            allowClear: true,
            language: 'es',
            matcher: function(params, data) {
                // Si no hay término de búsqueda, retornar todos los datos
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Convertir el término de búsqueda a minúsculas para búsqueda case-insensitive
                var term = params.term.toLowerCase();
                
                // Obtener el texto del option
                var text = data.text.toLowerCase();
                
                // Buscar en el texto completo
                if (text.indexOf(term) > -1) {
                    return data;
                }

                // Si hay un grupo (optgroup), también buscar en el texto del grupo
                if (data.children) {
                    var filteredChildren = [];
                    $.each(data.children, function(idx, child) {
                        if (child.text.toLowerCase().indexOf(term) > -1) {
                            filteredChildren.push(child);
                        }
                    });

                    if (filteredChildren.length > 0) {
                        var modifiedData = $.extend({}, data, true);
                        modifiedData.children = filteredChildren;
                        return modifiedData;
                    }
                }

                // Retornar null si no hay coincidencias
                return null;
            },
            minimumInputLength: 0, // Permitir búsqueda desde el primer carácter
            escapeMarkup: function(markup) {
                return markup; // Permitir HTML en los resultados
            },
            templateResult: function(data) {
                // Personalizar cómo se muestran los resultados
                if (data.loading) {
                    return 'Buscando...';
                }

                if (!data.id) {
                    return data.text;
                }

                // Resaltar el término de búsqueda
                var searchTerm = $('.select2-search__field').val();
                if (searchTerm) {
                    var regex = new RegExp('(' + searchTerm + ')', 'gi');
                    var highlightedText = data.text.replace(regex, '<mark>$1</mark>');
                    return $('<span>' + highlightedText + '</span>');
                }

                return data.text;
            }
        });

        // Funcionalidad del campo de búsqueda rápida
        const buscarIniciador = document.getElementById('buscar_iniciador');
        const limpiarBusqueda = document.getElementById('limpiar_busqueda');
        const selectIniciador = $('#iniciador');

        // Búsqueda en tiempo real
        buscarIniciador.addEventListener('input', function() {
            const termino = this.value.toLowerCase();
            
            if (termino.length === 0) {
                // Si no hay término, mostrar todas las opciones
                selectIniciador.find('option').show();
                selectIniciador.trigger('change.select2');
                return;
            }

            // Filtrar opciones basadas en el término de búsqueda
            selectIniciador.find('option').each(function() {
                const opcion = $(this);
                const texto = opcion.text().toLowerCase();
                
                if (texto.includes(termino) || opcion.val() === '') {
                    opcion.show();
                } else {
                    opcion.hide();
                }
            });

            // Abrir Select2 automáticamente si hay texto
            if (termino.length >= 2) {
                selectIniciador.select2('open');
                // Aplicar el término de búsqueda al Select2
                setTimeout(function() {
                    $('.select2-search__field').val(termino).trigger('input');
                }, 100);
            }

            selectIniciador.trigger('change.select2');
        });

        // Limpiar búsqueda
        limpiarBusqueda.addEventListener('click', function() {
            buscarIniciador.value = '';
            selectIniciador.find('option').show();
            selectIniciador.val('').trigger('change');
            buscarIniciador.focus();
        });

        // Sincronizar búsqueda rápida con Select2
        buscarIniciador.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Buscar la primera opción visible y seleccionarla
                const primeraOpcionVisible = selectIniciador.find('option:visible:not([value=""])').first();
                if (primeraOpcionVisible.length > 0) {
                    selectIniciador.val(primeraOpcionVisible.val()).trigger('change');
                    this.value = '';
                    selectIniciador.find('option').show();
                }
            }
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectIniciador.select2('open');
            }
        });

        // Agregar funcionalidad de búsqueda rápida con teclas
        $('#iniciador').on('select2:open', function() {
            // Enfocar automáticamente el campo de búsqueda
            setTimeout(function() {
                $('.select2-search__field').focus();
            }, 100);
        });

        // Cuando se selecciona una opción, limpiar el campo de búsqueda rápida
        $('#iniciador').on('select2:select', function() {
            buscarIniciador.value = '';
            selectIniciador.find('option').show();
        });
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