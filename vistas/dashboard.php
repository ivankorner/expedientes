<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
    <?php require 'head.php'; ?>
 
<body>
  <?php require 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">

         <!-- Sidebar -->
           <?php require '../vistas/sidebar.php'; ?>
            <!-- Sidebar -->
            






            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <!-- Estadísticas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-files fs-2 text-primary"></i></div>
                                <h5 class="card-title">Expedientes Totales</h5>
                                <span class="display-6 fw-bold text-primary">1243</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-plus-circle fs-2 text-success"></i></div>
                                <h5 class="card-title">Expedientes Hoy</h5>
                                <span class="display-6 fw-bold text-success">6</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-exclamation-circle fs-2 text-warning"></i></div>
                                <h5 class="card-title">Pendientes</h5>
                                <span class="display-6 fw-bold text-warning">32</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Accesos rápidos -->
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <a href="listar_usuarios.php" class="btn btn-outline-dark btn-lg w-100">
                            <i class="bi bi-people"></i> Administrar usuarios
                        </a>
                    </div>
                </div>
                <!-- NO HAY FORMULARIO DE CONSULTA ACÁ -->
            </main>
        </div>
    </div>
</body>
</html>