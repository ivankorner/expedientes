<?php
session_start();
require 'header.php';
require 'head.php';

// Verificar que se recibió el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de entidad no válido";
    $_SESSION['tipo_mensaje'] = "danger";
    header('Location: listar_persona_juri_entidad.php');
    exit;
}

$id = intval($_GET['id']);
$entidad = null;

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener los datos de la entidad
    $sql = "SELECT * FROM persona_juri_entidad WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $entidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entidad) {
        $_SESSION['mensaje'] = "Entidad no encontrada";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar la entidad: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header('Location: listar_persona_juri_entidad.php');
    exit;
}

// Recuperar datos del formulario si hubo error
$form_data = $_SESSION['form_data'] ?? $entidad;
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="es">

<body>
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Editar Entidad</h1>
                    <div>
                        <a href="listar_persona_juri_entidad.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="listar_persona_juri_entidad.php" class="btn btn-primary px-4">
                            <i class="bi bi-journal-text"></i> Ver Listado
                        </a>
                    </div>
                </div>

                <!-- Formulario de edición -->
                <form action="procesar_editar_entidad.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?= $entidad['id'] ?>">
                    
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
                                            <option value="SA" <?= $entidad['tipo_entidad'] === 'SA' ? 'selected' : '' ?>>Sociedad Anónima</option>
                                            <option value="SR" <?= $entidad['tipo_entidad'] === 'SR' ? 'selected' : '' ?>>Sociedad de Responsabilidad Limitada</option>
                                            <option value="AS" <?= $entidad['tipo_entidad'] === 'AS' ? 'selected' : '' ?>>Sociedad por Acciones Simplificada</option>
                                            <option value="SC" <?= $entidad['tipo_entidad'] === 'SC' ? 'selected' : '' ?>>Sociedad Colectiva</option>
                                            <option value="CS" <?= $entidad['tipo_entidad'] === 'CS' ? 'selected' : '' ?>>Sociedad en Comandita Simple</option>
                                            <option value="CP" <?= $entidad['tipo_entidad'] === 'CP' ? 'selected' : '' ?>>Sociedad en Comandita por Acciones</option>
                                            <option value="AC" <?= $entidad['tipo_entidad'] === 'AC' ? 'selected' : '' ?>>Asociación Civil</option>
                                            <option value="FU" <?= $entidad['tipo_entidad'] === 'FU' ? 'selected' : '' ?>>Fundación</option>
                                            <option value="CO" <?= $entidad['tipo_entidad'] === 'CO' ? 'selected' : '' ?>>Cooperativa</option>
                                            <option value="MU" <?= $entidad['tipo_entidad'] === 'MU' ? 'selected' : '' ?>>Mutual</option>
                                            <option value="SI" <?= $entidad['tipo_entidad'] === 'SI' ? 'selected' : '' ?>>Sindicato</option>
                                            <option value="FE" <?= $entidad['tipo_entidad'] === 'FE' ? 'selected' : '' ?>>Federación</option>
                                            <option value="CF" <?= $entidad['tipo_entidad'] === 'CF' ? 'selected' : '' ?>>Confederación</option>
                                            <option value="UT" <?= $entidad['tipo_entidad'] === 'UT' ? 'selected' : '' ?>>Unión Transitoria de Empresas</option>
                                            <option value="AI" <?= $entidad['tipo_entidad'] === 'AI' ? 'selected' : '' ?>>Agrupación de Interés Económico</option>
                                            <option value="EN" <?= $entidad['tipo_entidad'] === 'EN' ? 'selected' : '' ?>>Entidad sin Fines de Lucro</option>
                                            <option value="ON" <?= $entidad['tipo_entidad'] === 'ON' ? 'selected' : '' ?>>Organización No Gubernamental</option>
                                            <option value="CL" <?= $entidad['tipo_entidad'] === 'CL' ? 'selected' : '' ?>>Club Deportivo</option>
                                            <option value="CC" <?= $entidad['tipo_entidad'] === 'CC' ? 'selected' : '' ?>>Cámara de Comercio</option>
                                            <option value="CI" <?= $entidad['tipo_entidad'] === 'CI' ? 'selected' : '' ?>>Colegio de Ingenieros</option>
                                            <option value="CM" <?= $entidad['tipo_entidad'] === 'CM' ? 'selected' : '' ?>>Colegio de Médicos</option>
                                            <option value="CA" <?= $entidad['tipo_entidad'] === 'CA' ? 'selected' : '' ?>>Colegio de Abogados</option>
                                            <option value="IN" <?= $entidad['tipo_entidad'] === 'IN' ? 'selected' : '' ?>>Instituto</option>
                                            <option value="UN" <?= $entidad['tipo_entidad'] === 'UN' ? 'selected' : '' ?>>Universidad</option>
                                            <option value="ES" <?= $entidad['tipo_entidad'] === 'ES' ? 'selected' : '' ?>>Escuela</option>
                                            <option value="CE" <?= $entidad['tipo_entidad'] === 'CE' ? 'selected' : '' ?>>Centro Educativo</option>
                                            <option value="HO" <?= $entidad['tipo_entidad'] === 'HO' ? 'selected' : '' ?>>Hospital</option>
                                            <option value="SN" <?= $entidad['tipo_entidad'] === 'SN' ? 'selected' : '' ?>>Sanatorio</option>
                                            <option value="CX" <?= $entidad['tipo_entidad'] === 'CX' ? 'selected' : '' ?>>Centro de Salud</option>
                                            <option value="IG" <?= $entidad['tipo_entidad'] === 'IG' ? 'selected' : '' ?>>Iglesia</option>
                                            <option value="PA" <?= $entidad['tipo_entidad'] === 'PA' ? 'selected' : '' ?>>Parroquia</option>
                                            <option value="OT" <?= $entidad['tipo_entidad'] === 'OT' ? 'selected' : '' ?>>Otro</option>
                                        </select>
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
                                            <option value="PR" <?= ($entidad['rep_cargo'] ?? '') === 'PR' ? 'selected' : '' ?>>Presidente</option>
                                            <option value="VP" <?= ($entidad['rep_cargo'] ?? '') === 'VP' ? 'selected' : '' ?>>Vicepresidente</option>
                                            <option value="SE" <?= ($entidad['rep_cargo'] ?? '') === 'SE' ? 'selected' : '' ?>>Secretario</option>
                                            <option value="TE" <?= ($entidad['rep_cargo'] ?? '') === 'TE' ? 'selected' : '' ?>>Tesorero</option>
                                            <option value="DI" <?= ($entidad['rep_cargo'] ?? '') === 'DI' ? 'selected' : '' ?>>Director</option>
                                            <option value="GE" <?= ($entidad['rep_cargo'] ?? '') === 'GE' ? 'selected' : '' ?>>Gerente</option>
                                            <option value="AP" <?= ($entidad['rep_cargo'] ?? '') === 'AP' ? 'selected' : '' ?>>Apoderado</option>
                                            <option value="AD" <?= ($entidad['rep_cargo'] ?? '') === 'AD' ? 'selected' : '' ?>>Administrador</option>
                                            <option value="SY" <?= ($entidad['rep_cargo'] ?? '') === 'SY' ? 'selected' : '' ?>>Síndico</option>
                                            <option value="RE" <?= ($entidad['rep_cargo'] ?? '') === 'RE' ? 'selected' : '' ?>>Rector</option>
                                            <option value="DE" <?= ($entidad['rep_cargo'] ?? '') === 'DE' ? 'selected' : '' ?>>Decano</option>
                                            <option value="CO" <?= ($entidad['rep_cargo'] ?? '') === 'CO' ? 'selected' : '' ?>>Coordinador</option>
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
                            <a href="listar_persona_juri_entidad.php" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Actualizar Entidad
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
                cancelButtonText: 'Seguir Editando',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'listar_persona_juri_entidad.php';
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
