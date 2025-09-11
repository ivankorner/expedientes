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
                    <h1>Nueva Persona Jurídica / Entidad</h1>
                    <div>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="listar_persona_juri_entidad.php" class="btn btn-primary px-4">
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
                <form action="procesar_carga_entidad.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Datos de la entidad -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-building text-primary me-2"></i>
                                        Datos de la Entidad
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="razon_social" class="form-label">Razón Social (Nombre)</label>
                                        <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                               value="<?= htmlspecialchars($form_data['razon_social'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cuit" class="form-label">CUIT</label>
                                                <input type="text" class="form-control" id="cuit" name="cuit" 
                                                       placeholder="Ingrese CUIT"
                                                       value="<?= htmlspecialchars($form_data['cuit'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="personeria" class="form-label">Nº Personería Jurídica</label>
                                                <input type="text" class="form-control" id="personeria" name="personeria" 
                                                       value="<?= htmlspecialchars($form_data['personeria'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tipo_entidad" class="form-label">Tipo de Entidad *</label>
                                        <select class="form-select" id="tipo_entidad" name="tipo_entidad" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="SA" <?= ($form_data['tipo_entidad'] ?? '') === 'SA' ? 'selected' : '' ?>>Sociedad Anónima</option>
                                            <option value="SR" <?= ($form_data['tipo_entidad'] ?? '') === 'SR' ? 'selected' : '' ?>>Sociedad de Responsabilidad Limitada</option>
                                            <option value="AS" <?= ($form_data['tipo_entidad'] ?? '') === 'AS' ? 'selected' : '' ?>>Sociedad por Acciones Simplificada</option>
                                            <option value="SC" <?= ($form_data['tipo_entidad'] ?? '') === 'SC' ? 'selected' : '' ?>>Sociedad Colectiva</option>
                                            <option value="CS" <?= ($form_data['tipo_entidad'] ?? '') === 'CS' ? 'selected' : '' ?>>Sociedad en Comandita Simple</option>
                                            <option value="CP" <?= ($form_data['tipo_entidad'] ?? '') === 'CP' ? 'selected' : '' ?>>Sociedad en Comandita por Acciones</option>
                                            <option value="SE" <?= ($form_data['tipo_entidad'] ?? '') === 'SE' ? 'selected' : '' ?>>Sociedad del Estado</option>
                                            <option value="SP" <?= ($form_data['tipo_entidad'] ?? '') === 'SP' ? 'selected' : '' ?>>Sociedad Anónima con Participación Estatal Mayoritaria (SAPEM)</option>
                                            <option value="EU" <?= ($form_data['tipo_entidad'] ?? '') === 'EU' ? 'selected' : '' ?>>Empresa Unipersonal</option>
                                            <option value="MO" <?= ($form_data['tipo_entidad'] ?? '') === 'MO' ? 'selected' : '' ?>>Monotributista / Autónomo</option>
                                            
                                            <option value="AC" <?= ($form_data['tipo_entidad'] ?? '') === 'AC' ? 'selected' : '' ?>>Asociación Civil</option>
                                            <option value="FU" <?= ($form_data['tipo_entidad'] ?? '') === 'FU' ? 'selected' : '' ?>>Fundación</option>
                                            <option value="CO" <?= ($form_data['tipo_entidad'] ?? '') === 'CO' ? 'selected' : '' ?>>Cooperativa</option>
                                            <option value="MU" <?= ($form_data['tipo_entidad'] ?? '') === 'MU' ? 'selected' : '' ?>>Mutual</option>
                                            <option value="SI" <?= ($form_data['tipo_entidad'] ?? '') === 'SI' ? 'selected' : '' ?>>Sindicato</option>
                                            <option value="FE" <?= ($form_data['tipo_entidad'] ?? '') === 'FE' ? 'selected' : '' ?>>Federación</option>
                                            <option value="CF" <?= ($form_data['tipo_entidad'] ?? '') === 'CF' ? 'selected' : '' ?>>Confederación</option>
                                            <option value="UT" <?= ($form_data['tipo_entidad'] ?? '') === 'UT' ? 'selected' : '' ?>>Unión Transitoria de Empresas</option>
                                            <option value="AI" <?= ($form_data['tipo_entidad'] ?? '') === 'AI' ? 'selected' : '' ?>>Agrupación de Interés Económico</option>
                                            <option value="EN" <?= ($form_data['tipo_entidad'] ?? '') === 'EN' ? 'selected' : '' ?>>Entidad sin Fines de Lucro</option>
                                            <option value="ON" <?= ($form_data['tipo_entidad'] ?? '') === 'ON' ? 'selected' : '' ?>>Organización No Gubernamental</option>
                                            <option value="COOPR" <?= ($form_data['tipo_entidad'] ?? '') === 'COOPR' ? 'selected' : '' ?>>Consorcio de Copropietarios</option>
                                            
                                            <option value="MIN" <?= ($form_data['tipo_entidad'] ?? '') === 'MIN' ? 'selected' : '' ?>>Ministerio</option>
                                            <option value="SEC" <?= ($form_data['tipo_entidad'] ?? '') === 'SEC' ? 'selected' : '' ?>>Secretaría</option>
                                            <option value="MUN" <?= ($form_data['tipo_entidad'] ?? '') === 'MUN' ? 'selected' : '' ?>>Municipalidad</option>
                                            <option value="COM" <?= ($form_data['tipo_entidad'] ?? '') === 'COM' ? 'selected' : '' ?>>Comisión Municipal</option>
                                            <option value="CD" <?= ($form_data['tipo_entidad'] ?? '') === 'CD' ? 'selected' : '' ?>>Concejo Deliberante</option>
                                            <option value="OD" <?= ($form_data['tipo_entidad'] ?? '') === 'OD' ? 'selected' : '' ?>>Organismo Descentralizado / Ente Autárquico</option>
                                            <option value="EP" <?= ($form_data['tipo_entidad'] ?? '') === 'EP' ? 'selected' : '' ?>>Empresa Pública</option>
                                            
                                            <option value="CL" <?= ($form_data['tipo_entidad'] ?? '') === 'CL' ? 'selected' : '' ?>>Club Deportivo</option>
                                            <option value="ADE" <?= ($form_data['tipo_entidad'] ?? '') === 'ADE' ? 'selected' : '' ?>>Asociación Deportiva</option>
                                            <option value="FDE" <?= ($form_data['tipo_entidad'] ?? '') === 'FDE' ? 'selected' : '' ?>>Federación Deportiva</option>
                                            <option value="LDE" <?= ($form_data['tipo_entidad'] ?? '') === 'LDE' ? 'selected' : '' ?>>Liga Deportiva / Liga Barrial</option>
                                            <option value="ACD" <?= ($form_data['tipo_entidad'] ?? '') === 'ACD' ? 'selected' : '' ?>>Asociación de Clubes</option>
                                            
                                            <option value="CC" <?= ($form_data['tipo_entidad'] ?? '') === 'CC' ? 'selected' : '' ?>>Cámara de Comercio</option>
                                            <option value="CI" <?= ($form_data['tipo_entidad'] ?? '') === 'CI' ? 'selected' : '' ?>>Colegio de Ingenieros</option>
                                            <option value="CM" <?= ($form_data['tipo_entidad'] ?? '') === 'CM' ? 'selected' : '' ?>>Colegio de Médicos</option>
                                            <option value="CA" <?= ($form_data['tipo_entidad'] ?? '') === 'CA' ? 'selected' : '' ?>>Colegio de Abogados</option>
                                            
                                            <option value="IN" <?= ($form_data['tipo_entidad'] ?? '') === 'IN' ? 'selected' : '' ?>>Instituto</option>
                                            <option value="UN" <?= ($form_data['tipo_entidad'] ?? '') === 'UN' ? 'selected' : '' ?>>Universidad</option>
                                            <option value="ES" <?= ($form_data['tipo_entidad'] ?? '') === 'ES' ? 'selected' : '' ?>>Escuela</option>
                                            <option value="JI" <?= ($form_data['tipo_entidad'] ?? '') === 'JI' ? 'selected' : '' ?>>Jardín de Infantes</option>
                                            <option value="ET" <?= ($form_data['tipo_entidad'] ?? '') === 'ET' ? 'selected' : '' ?>>Escuela Técnica</option>
                                            <option value="CE" <?= ($form_data['tipo_entidad'] ?? '') === 'CE' ? 'selected' : '' ?>>Centro Educativo</option>
                                            <option value="ITS" <?= ($form_data['tipo_entidad'] ?? '') === 'ITS' ? 'selected' : '' ?>>Instituto Terciario / Superior</option>
                                            <option value="CEI" <?= ($form_data['tipo_entidad'] ?? '') === 'CEI' ? 'selected' : '' ?>>Centro de Investigación</option>
                                            <option value="ACA" <?= ($form_data['tipo_entidad'] ?? '') === 'ACA' ? 'selected' : '' ?>>Academia</option>
                                            <option value="CES" <?= ($form_data['tipo_entidad'] ?? '') === 'CES' ? 'selected' : '' ?>>Consejo Escolar</option>
                                            
                                            <option value="HO" <?= ($form_data['tipo_entidad'] ?? '') === 'HO' ? 'selected' : '' ?>>Hospital</option>
                                            <option value="SN" <?= ($form_data['tipo_entidad'] ?? '') === 'SN' ? 'selected' : '' ?>>Sanatorio</option>
                                            <option value="CLN" <?= ($form_data['tipo_entidad'] ?? '') === 'CLN' ? 'selected' : '' ?>>Clínica</option>
                                            <option value="CX" <?= ($form_data['tipo_entidad'] ?? '') === 'CX' ? 'selected' : '' ?>>Centro de Salud</option>
                                            <option value="CCOM" <?= ($form_data['tipo_entidad'] ?? '') === 'CCOM' ? 'selected' : '' ?>>Centro Comunitario</option>
                                            <option value="CREH" <?= ($form_data['tipo_entidad'] ?? '') === 'CREH' ? 'selected' : '' ?>>Centro de Rehabilitación</option>
                                            <option value="RGA" <?= ($form_data['tipo_entidad'] ?? '') === 'RGA' ? 'selected' : '' ?>>Residencia Geriátrica / Hogar de Ancianos</option>
                                            <option value="CCO" <?= ($form_data['tipo_entidad'] ?? '') === 'CCO' ? 'selected' : '' ?>>Comedor Comunitario</option>
                                            
                                            <option value="IG" <?= ($form_data['tipo_entidad'] ?? '') === 'IG' ? 'selected' : '' ?>>Iglesia</option>
                                            <option value="PA" <?= ($form_data['tipo_entidad'] ?? '') === 'PA' ? 'selected' : '' ?>>Parroquia</option>
                                            <option value="TEM" <?= ($form_data['tipo_entidad'] ?? '') === 'TEM' ? 'selected' : '' ?>>Templo</option>
                                            <option value="CAP" <?= ($form_data['tipo_entidad'] ?? '') === 'CAP' ? 'selected' : '' ?>>Capilla</option>
                                            <option value="HER" <?= ($form_data['tipo_entidad'] ?? '') === 'HER' ? 'selected' : '' ?>>Hermandad / Cofradía</option>
                                            
                                            <option value="BP" <?= ($form_data['tipo_entidad'] ?? '') === 'BP' ? 'selected' : '' ?>>Biblioteca Popular</option>
                                            <option value="CCU" <?= ($form_data['tipo_entidad'] ?? '') === 'CCU' ? 'selected' : '' ?>>Centro Cultural</option>
                                            <option value="TEI" <?= ($form_data['tipo_entidad'] ?? '') === 'TEI' ? 'selected' : '' ?>>Teatro Independiente</option>
                                            <option value="AVE" <?= ($form_data['tipo_entidad'] ?? '') === 'AVE' ? 'selected' : '' ?>>Asociación Vecinal / Centro Vecinal</option>
                                            <option value="SF" <?= ($form_data['tipo_entidad'] ?? '') === 'SF' ? 'selected' : '' ?>>Sociedad de Fomento</option>
                                            
                                            <option value="OT" <?= ($form_data['tipo_entidad'] ?? '') === 'OT' ? 'selected' : '' ?>>Otro</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Campo adicional para especificar "Otro" -->
                                    <div class="mb-3" id="otro-tipo-container" style="display: none;">
                                        <label for="otro_tipo" class="form-label">Especificar otro tipo</label>
                                        <input type="text" class="form-control" id="otro_tipo" name="otro_tipo" 
                                               placeholder="Ingrese el tipo de entidad"
                                               value="<?= htmlspecialchars($form_data['otro_tipo'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="web" class="form-label">Página Web</label>
                                        <input type="url" class="form-control" id="web" name="web" 
                                               placeholder="https://"
                                               value="<?= htmlspecialchars($form_data['web'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto y domicilio -->
                        <div class="col-md-6">
                            <!-- Contacto -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-telephone-fill text-success me-2"></i>
                                        Contacto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel_fijo" class="form-label">Teléfono Fijo</label>
                                                <input type="tel" class="form-control" id="tel_fijo" name="tel_fijo" 
                                                       value="<?= htmlspecialchars($form_data['tel_fijo'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel_celular" class="form-label">Teléfono Celular</label>
                                                <input type="tel" class="form-control" id="tel_celular" name="tel_celular" 
                                                       value="<?= htmlspecialchars($form_data['tel_celular'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Domicilio -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-house-fill text-info me-2"></i>
                                        Domicilio
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="domicilio" class="form-label">Domicilio *</label>
                                        <input type="text" class="form-control" id="domicilio" name="domicilio" 
                                               value="<?= htmlspecialchars($form_data['domicilio'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad *</label>
                                                <input type="text" class="form-control" id="localidad" name="localidad" 
                                                       value="<?= htmlspecialchars($form_data['localidad'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="provincia" class="form-label">Provincia *</label>
                                                <input type="text" class="form-control" id="provincia" name="provincia" 
                                                       value="<?= htmlspecialchars($form_data['provincia'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Representante Legal -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-badge text-warning me-2"></i>
                                Representante Legal
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_nombre" class="form-label">Nombre y Apellido</label>
                                        <input type="text" class="form-control" id="rep_nombre" name="rep_nombre" 
                                               value="<?= htmlspecialchars($form_data['rep_nombre'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_documento" class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" id="rep_documento" name="rep_documento" 
                                               value="<?= htmlspecialchars($form_data['rep_documento'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_cargo" class="form-label">Cargo</label>
                                        <select class="form-select" id="rep_cargo" name="rep_cargo">
                                            <option value="">Seleccionar...</option>
                                            <option value="PR" <?= ($form_data['rep_cargo'] ?? '') === 'PR' ? 'selected' : '' ?>>Presidente</option>
                                            <option value="VP" <?= ($form_data['rep_cargo'] ?? '') === 'VP' ? 'selected' : '' ?>>Vicepresidente</option>
                                            <option value="SE" <?= ($form_data['rep_cargo'] ?? '') === 'SE' ? 'selected' : '' ?>>Secretario</option>
                                            <option value="TE" <?= ($form_data['rep_cargo'] ?? '') === 'TE' ? 'selected' : '' ?>>Tesorero</option>
                                            <option value="DI" <?= ($form_data['rep_cargo'] ?? '') === 'DI' ? 'selected' : '' ?>>Director</option>
                                            <option value="GE" <?= ($form_data['rep_cargo'] ?? '') === 'GE' ? 'selected' : '' ?>>Gerente</option>
                                            <option value="AP" <?= ($form_data['rep_cargo'] ?? '') === 'AP' ? 'selected' : '' ?>>Apoderado</option>
                                            <option value="AD" <?= ($form_data['rep_cargo'] ?? '') === 'AD' ? 'selected' : '' ?>>Administrador</option>
                                            <option value="SY" <?= ($form_data['rep_cargo'] ?? '') === 'SY' ? 'selected' : '' ?>>Síndico</option>
                                            <option value="RE" <?= ($form_data['rep_cargo'] ?? '') === 'RE' ? 'selected' : '' ?>>Rector</option>
                                            <option value="DE" <?= ($form_data['rep_cargo'] ?? '') === 'DE' ? 'selected' : '' ?>>Decano</option>
                                            <option value="CO" <?= ($form_data['rep_cargo'] ?? '') === 'CO' ? 'selected' : '' ?>>Coordinador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_tel_fijo" class="form-label">Teléfono Fijo</label>
                                        <input type="tel" class="form-control" id="rep_tel_fijo" name="rep_tel_fijo" 
                                               value="<?= htmlspecialchars($form_data['rep_tel_fijo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_tel_celular" class="form-label">Teléfono Celular</label>
                                        <input type="tel" class="form-control" id="rep_tel_celular" name="rep_tel_celular" 
                                               value="<?= htmlspecialchars($form_data['rep_tel_celular'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_domicilio" class="form-label">Domicilio</label>
                                        <input type="text" class="form-control" id="rep_domicilio" name="rep_domicilio" 
                                               value="<?= htmlspecialchars($form_data['rep_domicilio'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="rep_email" name="rep_email" 
                                               value="<?= htmlspecialchars($form_data['rep_email'] ?? '') ?>">
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
                            <a href="acciones_iniciadores.php" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Guardar Entidad
                        </button>
                    </div>
                </form>
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
                    }
                    form.classList.add('was-validated');
                });
            });
        })();

        // Mostrar/ocultar campo "otro tipo" cuando se selecciona "Otro"
        document.getElementById('tipo_entidad').addEventListener('change', function() {
            const otroContainer = document.getElementById('otro-tipo-container');
            const otroInput = document.getElementById('otro_tipo');
            
            if (this.value === 'OT') {
                otroContainer.style.display = 'block';
                otroInput.required = true;
            } else {
                otroContainer.style.display = 'none';
                otroInput.required = false;
                otroInput.value = '';
            }
        });

        // Verificar al cargar la página si ya está seleccionado "Otro"
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSelect = document.getElementById('tipo_entidad');
            if (tipoSelect.value === 'OT') {
                document.getElementById('otro-tipo-container').style.display = 'block';
                document.getElementById('otro_tipo').required = true;
            }
        });

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
                cancelButtonText: 'Crear Otra',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'listar_persona_juri_entidad.php';
                } else {
                    // Limpiar el formulario para crear otra
                    document.querySelector('form').reset();
                }
            });
            <?php elseif ($tipo === 'danger' || $tipo === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= addslashes($mensaje) ?>',
                confirmButtonColor: '#dc3545'
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