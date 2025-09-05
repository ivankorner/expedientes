
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

    // Configuración de paginación
    $por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $por_pagina;

    // Búsqueda
    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $where = '';
    $params = [];

    if ($buscar !== '') {
        $where = "WHERE apellido LIKE :buscar 
                  OR nombre LIKE :buscar 
                  OR dni LIKE :buscar
                  OR bloque LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    // Obtener total de registros
    $sql_total = "SELECT COUNT(*) FROM concejales $where";
    $stmt = $db->prepare($sql_total);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_paginas = ceil($total / $por_pagina);

    // Obtener registros
    $sql = "SELECT * FROM concejales $where 
            ORDER BY apellido, nombre 
            LIMIT :offset, :limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}
?>

<!DOCTYPE html>
<html lang="es">

<body>
    
    
    <div class="container-fluid">
        <div class="row">
            <?php require '../vistas/sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Listado de Concejales</h1>
                    <a href="carga_concejal.php" class="btn btn-primary px-4">
                        <i class="bi bi-plus-circle"></i> Nuevo Concejal
                    </a>
                </div>

                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensaje'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <!-- Buscador -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" name="buscar" class="form-control" 
                               placeholder="Buscar por apellido, nombre, DNI o bloque..."
                               value="<?= htmlspecialchars($buscar) ?>">
                        <button class="btn btn-outline-secondary px-4" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Apellido y Nombre</th>
                                <th>DNI</th>
                                <th>Bloque</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($concejales as $concejal): ?>
                            <tr>
                                <td><?= htmlspecialchars($concejal['apellido'] . ', ' . $concejal['nombre']) ?></td>
                                <td><?= htmlspecialchars($concejal['dni']) ?></td>
                                <td><?= htmlspecialchars($concejal['bloque'] ?? '-') ?></td>
                                <td>
                                    <?php if ($concejal['email']): ?>
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($concejal['email']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($concejal['cel']): ?>
                                        <i class="bi bi-phone"></i> <?= htmlspecialchars($concejal['cel']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar_concejal.php?id=<?= $concejal['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminar(<?= $concejal['id'] ?>, '<?= htmlspecialchars($concejal['apellido'] . ', ' . $concejal['nombre']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina-1 ?>&buscar=<?= urlencode($buscar) ?>">Anterior</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina+1 ?>&buscar=<?= urlencode($buscar) ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar concejal?',
            html: `¿Está seguro que desea eliminar a <br><strong>${nombre}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_concejal.php?id=${id}`;
            }
        });
    }
    </script>
</body>
</html>