<?php
$base_url = dirname($_SERVER['SCRIPT_NAME']);
?>
<nav class="sidebar-dashboard col-12 col-md-2 d-md-block sidebar px-0 py-4">
    <ul class="nav flex-column gap-1 menu-dashboard">
        <li class="nav-item">
            <a class="nav-link active" href="<?= $base_url ?>/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $base_url ?>/acciones_expedientes.php"><i class="bi bi-file-earmark-plus me-2"></i>Expedientes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $base_url ?>/acciones_iniciadores.php"><i class="bi bi-person-plus me-2"></i>Iniciadores</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= $base_url ?>/consulta.php"><i class="bi bi-search me-2"></i>Consulta de Expedientes</a>
        </li>
    </ul>
</nav>