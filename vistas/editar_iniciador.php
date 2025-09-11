<?php
session_start();
require 'header.php';
require 'head.php';

// Verificar que se proporcionó el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de iniciador no válido";
    $_SESSION['tipo_mensaje'] = "danger";
    header('Location: listar_iniciadores.php');
    exit;
}

$id = intval($_GET['id']);

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener los datos del iniciador
    $sql = "SELECT * FROM persona_fisica WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $iniciador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$iniciador) {
        $_SESSION['mensaje'] = "Iniciador no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_iniciadores.php');
        exit;
    }

    // Procesar formulario si se envió
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validaciones
        $errores = [];
        
        $apellido = trim($_POST['apellido'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        
        if (empty($apellido)) $errores[] = "El apellido es obligatorio";
        if (empty($nombre)) $errores[] = "El nombre es obligatorio";
        if (empty($dni)) $errores[] = "El DNI es obligatorio";
        
        // Verificar que el DNI no esté duplicado (excepto para este iniciador)
        if (!empty($dni)) {
            $stmt = $db->prepare("SELECT id FROM persona_fisica WHERE dni = :dni AND id != :id");
            $stmt->execute([':dni' => $dni, ':id' => $id]);
            if ($stmt->fetch()) {
                $errores[] = "Ya existe un iniciador con este DNI";
            }
        }
        
        if (empty($errores)) {
            // Actualizar el iniciador
            $sql = "UPDATE persona_fisica SET 
                    apellido = :apellido,
                    nombre = :nombre,
                    dni = :dni,
                    cuil = :cuil,
                    fecha_nacimiento = :fecha_nacimiento,
                    nacionalidad = :nacionalidad,
                    estado_civil = :estado_civil,
                    profesion = :profesion,
                    email = :email,
                    tel = :tel,
                    cel = :cel,
                    calle = :calle,
                    numero = :numero,
                    piso = :piso,
                    depto = :depto,
                    localidad = :localidad,
                    cp = :cp,
                    observaciones = :observaciones
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $resultado = $stmt->execute([
                ':apellido' => $apellido,
                ':nombre' => $nombre,
                ':dni' => $dni,
                ':cuil' => trim($_POST['cuil'] ?? ''),
                ':fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                ':nacionalidad' => trim($_POST['nacionalidad'] ?? ''),
                ':estado_civil' => trim($_POST['estado_civil'] ?? ''),
                ':profesion' => trim($_POST['profesion'] ?? ''),
                ':email' => trim($_POST['email'] ?? ''),
                ':tel' => trim($_POST['tel'] ?? ''),
                ':cel' => trim($_POST['cel'] ?? ''),
                ':calle' => trim($_POST['calle'] ?? ''),
                ':numero' => trim($_POST['numero'] ?? ''),
                ':piso' => trim($_POST['piso'] ?? ''),
                ':depto' => trim($_POST['depto'] ?? ''),
                ':localidad' => trim($_POST['localidad'] ?? ''),
                ':cp' => trim($_POST['cp'] ?? ''),
                ':observaciones' => trim($_POST['observaciones'] ?? ''),
                ':id' => $id
            ]);
            
            if ($resultado) {
                $actualizado_exitosamente = true;
            } else {
                $errores[] = "Error al actualizar el iniciador";
            }
        }
    }

} catch (PDOException $e) {
    $errores[] = "Error de base de datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<body>
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Editar Iniciador</h1>
                    <a href="listar_iniciadores.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>

                <!-- Mostrar errores -->
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulario de edición -->
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Datos personales -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-fill text-primary me-2"></i>
                                        Datos Personales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="apellido" class="form-label">Apellido *</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               value="<?= htmlspecialchars($iniciador['apellido'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= htmlspecialchars($iniciador['nombre'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="dni" class="form-label">DNI *</label>
                                                <input type="text" class="form-control" id="dni" name="dni" 
                                                       value="<?= htmlspecialchars($iniciador['dni'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cuil" class="form-label">CUIL</label>
                                                <input type="text" class="form-control" id="cuil" name="cuil" 
                                                       value="<?= htmlspecialchars($iniciador['cuil'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                               value="<?= htmlspecialchars($iniciador['fecha_nacimiento'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                                <input type="text" class="form-control" id="nacionalidad" name="nacionalidad" 
                                                       value="<?= htmlspecialchars($iniciador['nacionalidad'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                                <select class="form-select" id="estado_civil" name="estado_civil">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="Soltero/a" <?= ($iniciador['estado_civil'] ?? '') === 'Soltero/a' ? 'selected' : '' ?>>Soltero/a</option>
                                                    <option value="Casado/a" <?= ($iniciador['estado_civil'] ?? '') === 'Casado/a' ? 'selected' : '' ?>>Casado/a</option>
                                                    <option value="Divorciado/a" <?= ($iniciador['estado_civil'] ?? '') === 'Divorciado/a' ? 'selected' : '' ?>>Divorciado/a</option>
                                                    <option value="Viudo/a" <?= ($iniciador['estado_civil'] ?? '') === 'Viudo/a' ? 'selected' : '' ?>>Viudo/a</option>
                                                    <option value="Unión de hecho" <?= ($iniciador['estado_civil'] ?? '') === 'Unión de hecho' ? 'selected' : '' ?>>Unión de hecho</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="profesion" class="form-label">Profesión</label>
                                        <input type="text" class="form-control" id="profesion" name="profesion" 
                                               value="<?= htmlspecialchars($iniciador['profesion'] ?? '') ?>">
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
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($iniciador['email'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="tel" name="tel" 
                                                       value="<?= htmlspecialchars($iniciador['tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cel" class="form-label">Celular</label>
                                                <input type="text" class="form-control" id="cel" name="cel" 
                                                       value="<?= htmlspecialchars($iniciador['cel'] ?? '') ?>">
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
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="calle" class="form-label">Calle</label>
                                                <input type="text" class="form-control" id="calle" name="calle" 
                                                       value="<?= htmlspecialchars($iniciador['calle'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="numero" class="form-label">Número</label>
                                                <input type="text" class="form-control" id="numero" name="numero" 
                                                       value="<?= htmlspecialchars($iniciador['numero'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="piso" class="form-label">Piso</label>
                                                <input type="text" class="form-control" id="piso" name="piso" 
                                                       value="<?= htmlspecialchars($iniciador['piso'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="depto" class="form-label">Departamento</label>
                                                <input type="text" class="form-control" id="depto" name="depto" 
                                                       value="<?= htmlspecialchars($iniciador['depto'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad</label>
                                                <input type="text" class="form-control" id="localidad" name="localidad" 
                                                       value="<?= htmlspecialchars($iniciador['localidad'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="cp" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="cp" name="cp" 
                                                       value="<?= htmlspecialchars($iniciador['cp'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-text-fill text-warning me-2"></i>
                                Observaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones adicionales</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars($iniciador['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mb-4">
                        <a href="listar_iniciadores.php" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Actualizar Iniciador
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

        <?php if (isset($actualizado_exitosamente) && $actualizado_exitosamente): ?>
        // Mostrar SweetAlert de éxito
        Swal.fire({
            icon: 'success',
            title: '¡Actualización exitosa!',
            text: 'Los datos del iniciador han sido actualizados correctamente.',
            showCancelButton: true,
            confirmButtonText: 'Ir al Listado',
            cancelButtonText: 'Quedarse Aquí',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'listar_iniciadores.php';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
