<?php
session_start();
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
                    <h1>Nuevo Concejal (Modo Debug)</h1>
                    <div>
                        <a href="diagnostico_concejales.php" class="btn btn-info px-4 me-2">
                            <i class="bi bi-search"></i> Diagnóstico
                        </a>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="listar_concejales.php" class="btn btn-primary px-4">
                            <i class="bi bi-journal-text"></i> Ver Listado
                        </a>
                    </div>
                </div>

                <?php
                // Recuperar datos del formulario si hubo error
                $form_data = $_SESSION['form_data'] ?? [];
                unset($_SESSION['form_data']);
                ?>

                <!-- Formulario de creación -->
                <form action="procesar_carga_concejal_debug.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Datos personales -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person text-primary me-2"></i>
                                        Datos Personales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="apellido" class="form-label">Apellido *</label>
                                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                                       value="<?= htmlspecialchars($form_data['apellido'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre *</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                                       value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="dni" class="form-label">DNI *</label>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                               placeholder="Ingrese DNI"
                                               value="<?= htmlspecialchars($form_data['dni'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" 
                                               value="<?= htmlspecialchars($form_data['direccion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-telephone-fill text-success me-2"></i>
                                        Contacto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel" class="form-label">Teléfono Fijo</label>
                                                <input type="tel" class="form-control" id="tel" name="tel" 
                                                       value="<?= htmlspecialchars($form_data['tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cel" class="form-label">Teléfono Celular</label>
                                                <input type="tel" class="form-control" id="cel" name="cel" 
                                                       value="<?= htmlspecialchars($form_data['cel'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información política -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-building text-warning me-2"></i>
                                Información Política
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bloque" class="form-label">Bloque</label>
                                        <input type="text" class="form-control" id="bloque" name="bloque" 
                                               placeholder="Ingrese el nombre del bloque"
                                               value="<?= htmlspecialchars($form_data['bloque'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="observacion" class="form-label">Observaciones</label>
                                        <input type="text" class="form-control" id="observacion" name="observacion" 
                                               placeholder="Observaciones adicionales"
                                               value="<?= htmlspecialchars($form_data['observacion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <button type="reset" class="btn btn-outline-secondary px-4 me-2">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                            <a href="carga_concejal.php" class="btn btn-outline-info px-4 me-2">
                                <i class="bi bi-arrow-right"></i> Modo Normal
                            </a>
                            <a href="acciones_iniciadores.php" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Guardar Concejal (Debug)
                        </button>
                    </div>
                </form>

                <!-- Panel de información de debug -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-bug text-danger me-2"></i>
                            Información de Debug
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Archivos de diagnóstico:</strong></p>
                        <ul>
                            <li><a href="diagnostico_concejales.php" target="_blank">Diagnóstico del Sistema</a></li>
                            <li><strong>Log de Debug:</strong> debug_concejales.log (se crea automáticamente)</li>
                        </ul>
                        
                        <p><strong>Qué hace este modo:</strong></p>
                        <ul>
                            <li>Registra todos los pasos del procesamiento</li>
                            <li>Verifica la estructura de la base de datos</li>
                            <li>Crea la tabla automáticamente si no existe</li>
                            <li>Muestra errores detallados</li>
                            <li>Guarda logs en archivo para revisión</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Validación de formulario
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Mostrar mensaje de error con SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Formulario Incompleto',
                            text: 'Por favor, complete todos los campos obligatorios marcados con (*)',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                    form.classList.add('was-validated');
                });
            });
        })();

        // Validación de email en tiempo real
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                this.setCustomValidity('Por favor, ingrese un email válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Verificar si hay mensaje en la sesión para mostrar con SweetAlert
        <?php if (isset($_SESSION['mensaje'])): ?>
            <?php 
            $mensaje = $_SESSION['mensaje'];
            $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
            
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
            ?>
            
            <?php if ($tipo === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= addslashes($mensaje) ?>',
                showCancelButton: true,
                confirmButtonText: 'Ir al Listado',
                cancelButtonText: 'Crear Otro',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                footer: '<small>Modo Debug - Revise el log para más detalles</small>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'listar_concejales.php';
                } else {
                    // Limpiar el formulario para crear otro
                    document.querySelector('form').reset();
                    document.querySelector('form').classList.remove('was-validated');
                }
            });
            <?php elseif ($tipo === 'danger' || $tipo === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error al Guardar',
                text: '<?= addslashes($mensaje) ?>',
                confirmButtonColor: '#dc3545',
                footer: '<small>Revise el archivo debug_concejales.log para más detalles</small>'
            }).then(() => {
                // Mostrar opción de ver diagnóstico
                Swal.fire({
                    title: '¿Desea ver el diagnóstico del sistema?',
                    text: 'Esto puede ayudar a identificar el problema',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ver diagnóstico',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('diagnostico_concejales.php', '_blank');
                    }
                });
            });
            <?php else: ?>
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: '<?= addslashes($mensaje) ?>',
                confirmButtonColor: '#0d6efd'
            });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
