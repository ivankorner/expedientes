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
    $registros_por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $registros_por_pagina;

    // Búsqueda
    $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $where = '';
    $params = [];
    
    if ($busqueda !== '') {
        $where = "WHERE apellido LIKE :busqueda 
                  OR nombre LIKE :busqueda 
                  OR dni LIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }

    // Obtener total de registros
    $sql_total = "SELECT COUNT(*) FROM persona_fisica $where";
    $stmt = $db->prepare($sql_total);
    if ($busqueda !== '') {
        $stmt->bindParam(':busqueda', $params[':busqueda']);
    }
    $stmt->execute();
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    // Obtener registros de la página actual con información de expedientes asociados
    $sql = "SELECT pf.*, 
            (SELECT COUNT(*) 
             FROM expedientes e 
             WHERE e.iniciador LIKE CONCAT('%', pf.apellido, ', ', pf.nombre, '%')
            ) as expedientes_asociados
            FROM persona_fisica pf 
            $where 
            ORDER BY pf.apellido, pf.nombre 
            LIMIT :offset, :limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
    if ($busqueda !== '') {
        $stmt->bindParam(':busqueda', $params[':busqueda']);
    }
    $stmt->execute();
    $iniciadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar los iniciadores: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
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
                    <h1>Listado de Iniciadores</h1>
                    <div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-tools"></i> Herramientas
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="verificar_proteccion_eliminacion.php">
                                    <i class="bi bi-shield-check"></i> Verificar Protecciones
                                </a></li>
                                <li><a class="dropdown-item" href="diagnostico_tabla.php">
                                    <i class="bi bi-bug"></i> Diagnóstico de Base de Datos
                                </a></li>
                            </ul>
                        </div>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="carga_iniciador.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> Nuevo Iniciador
                        </a>
                    </div>
                </div>

                <!-- Mensaje de éxito o error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                endif; ?>

                <!-- Buscador -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" 
                               name="buscar" 
                               class="form-control" 
                               placeholder="Buscar por apellido, nombre o DNI..."
                               value="<?= htmlspecialchars($busqueda) ?>">
                        <button class="btn btn-outline-secondary px-4" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>

                <!-- Tabla de iniciadores -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Apellido y Nombre</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Expedientes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($iniciadores as $iniciador): ?>
                            <tr>
                                <td><?= htmlspecialchars($iniciador['apellido'] . ', ' . $iniciador['nombre']) ?></td>
                                <td><?= htmlspecialchars($iniciador['dni']) ?></td>
                                <td>
                                    <?php if ($iniciador['cel']): ?>
                                        <i class="bi bi-phone"></i> <?= htmlspecialchars($iniciador['cel']) ?>
                                    <?php endif; ?>
                                    <?php if ($iniciador['tel']): ?>
                                        <br><i class="bi bi-telephone"></i> <?= htmlspecialchars($iniciador['tel']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($iniciador['email'] ?? '-') ?></td>
                                <td>
                                    <?php if ($iniciador['expedientes_asociados'] > 0): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-folder"></i> <?= $iniciador['expedientes_asociados'] ?>
                                        </span>
                                        <small class="text-muted d-block">expediente(s)</small>
                                    <?php else: ?>
                                        <span class="text-muted">Sin expedientes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalles(<?= $iniciador['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="editar_iniciador.php?id=<?= $iniciador['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($iniciador['expedientes_asociados'] > 0): ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    disabled
                                                    title="No se puede eliminar: tiene <?= $iniciador['expedientes_asociados'] ?> expediente(s) asociado(s)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmarEliminar(<?= $iniciador['id'] ?>, '<?= htmlspecialchars($iniciador['apellido'] . ', ' . $iniciador['nombre']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
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
                            <a class="page-link" href="?pagina=<?= $pagina-1 ?>&buscar=<?= urlencode($busqueda) ?>">Anterior</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina+1 ?>&buscar=<?= urlencode($busqueda) ?>">Siguiente</a>
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
    function verDetalles(id) {
        // Hacer petición AJAX para obtener los detalles del iniciador
        fetch(`obtener_iniciador_detalles.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const iniciador = data.iniciador;
                    
                    // Construir el HTML con los detalles
                    const detallesHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Datos Personales</h6>
                                <p><strong>Nombre completo:</strong><br>${iniciador.apellido}, ${iniciador.nombre}</p>
                                <p><strong>DNI:</strong> ${iniciador.dni}</p>
                                <p><strong>CUIL:</strong> ${iniciador.cuil || 'No especificado'}</p>
                                <p><strong>Fecha de nacimiento:</strong> ${iniciador.fecha_nacimiento || 'No especificada'}</p>
                                <p><strong>Nacionalidad:</strong> ${iniciador.nacionalidad || 'No especificada'}</p>
                                <p><strong>Estado civil:</strong> ${iniciador.estado_civil || 'No especificado'}</p>
                                <p><strong>Profesión:</strong> ${iniciador.profesion || 'No especificada'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Contacto</h6>
                                <p><strong>Email:</strong> ${iniciador.email || 'No especificado'}</p>
                                <p><strong>Teléfono:</strong> ${iniciador.tel || 'No especificado'}</p>
                                <p><strong>Celular:</strong> ${iniciador.cel || 'No especificado'}</p>
                                
                                <h6 class="text-primary mt-3">Domicilio</h6>
                                <p><strong>Calle:</strong> ${iniciador.calle || 'No especificada'}</p>
                                <p><strong>Número:</strong> ${iniciador.numero || 'No especificado'}</p>
                                <p><strong>Piso:</strong> ${iniciador.piso || 'No especificado'}</p>
                                <p><strong>Departamento:</strong> ${iniciador.depto || 'No especificado'}</p>
                                <p><strong>Localidad:</strong> ${iniciador.localidad || 'No especificada'}</p>
                                <p><strong>Código Postal:</strong> ${iniciador.cp || 'No especificado'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Información Adicional</h6>
                                <p><strong>Observaciones:</strong> ${iniciador.observaciones || 'Sin observaciones'}</p>
                                <p><strong>Fecha de registro:</strong> ${iniciador.fecha_creacion || 'No disponible'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Mostrar modal con SweetAlert2
                    Swal.fire({
                        title: `<strong>Detalles del Iniciador</strong>`,
                        html: detallesHtml,
                        width: '800px',
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                            htmlContainer: 'text-start'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudieron cargar los detalles',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión al cargar los detalles',
                    icon: 'error'
                });
            });
    }

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar iniciador?',
            html: `¿Está seguro que desea eliminar a <br><strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_iniciador.php?id=${id}`;
            }
        });
    }
    </script>
</body>
</html>