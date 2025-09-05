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
           
             <!-- Sidebar -->
           <?php require '../vistas/sidebar.php'; ?>
            <!-- Sidebar -->



        
            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="main-box carga">
                    <h1 class="titulo-principal mb-4 text-center">Carga de Iniciador</h1>
                    
                    <?php
                    session_start();

                    // Mostrar mensaje si existe
                    if (isset($_SESSION['mensaje'])) {
                        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
                        echo "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                                {$_SESSION['mensaje']}
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                              </div>";
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    }

                    // Recuperar datos del formulario si hubo error
                    $form_data = $_SESSION['form_data'] ?? [];
                    unset($_SESSION['form_data']);
                    ?>

                    <form action="procesar_carga_iniciador.php" method="post" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" id="apellido" name="apellido" class="form-control" required value="<?= htmlspecialchars($form_data['apellido'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="dni" class="form-label">DNI</label>
                                <input type="text" id="dni" name="dni" class="form-control" required value="<?= htmlspecialchars($form_data['dni'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control" value="<?= htmlspecialchars($form_data['direccion'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="tel" class="form-label">Teléfono Fijo</label>
                                <input type="text" id="tel" name="tel" class="form-control" value="<?= htmlspecialchars($form_data['tel'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="cel" class="form-label">Teléfono Celular</label>
                                <input type="text" id="cel" name="cel" class="form-control" value="<?= htmlspecialchars($form_data['cel'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="observacion" class="form-label">Observación</label>
                                <input type="text" id="observacion" name="observacion" class="form-control" value="<?= htmlspecialchars($form_data['observacion'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="reset" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                                <a href="listar_iniciadores.php" class="btn btn-info text-white px-4">
                                    <i class="bi bi-list-ul"></i> Ver Listado
                                </a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save"></i> Guardar
                                </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>