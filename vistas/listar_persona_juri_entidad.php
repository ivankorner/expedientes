
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
        $where = "WHERE razon_social LIKE :buscar 
                  OR cuit LIKE :buscar 
                  OR rep_nombre LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    // Obtener total de registros
    $sql_total = "SELECT COUNT(*) FROM persona_juri_entidad $where";
    $stmt = $db->prepare($sql_total);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_paginas = ceil($total / $por_pagina);

    // Obtener registros
    $sql = "SELECT * FROM persona_juri_entidad $where 
            ORDER BY razon_social 
            LIMIT :offset, :limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $entidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <h1>Listado de Entidades</h1>
                    <div>
                        <a href="carga_persona_juri_entidad.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="carga_persona_juri_entidad.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> Nueva Entidad
                        </a>
                    </div>
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
                               placeholder="Buscar por razón social, CUIT o representante..."
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
                                <th>Razón Social</th>
                                <th>CUIT</th>
                                <th>Tipo</th>
                                <th>Representante</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entidades as $entidad): ?>
                            <tr>
                                <td><?= htmlspecialchars($entidad['razon_social'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($entidad['cuit'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $tipos = [
                                        'SA' => 'Sociedad Anónima',
                                        'SR' => 'Sociedad de Responsabilidad Limitada',
                                        'AS' => 'Sociedad por Acciones Simplificada',
                                        'SC' => 'Sociedad Colectiva',
                                        'CS' => 'Sociedad en Comandita Simple',
                                        'CP' => 'Sociedad en Comandita por Acciones',
                                        'AC' => 'Asociación Civil',
                                        'FU' => 'Fundación',
                                        'CO' => 'Cooperativa',
                                        'MU' => 'Mutual',
                                        'SI' => 'Sindicato',
                                        'FE' => 'Federación',
                                        'CF' => 'Confederación',
                                        'UT' => 'Unión Transitoria de Empresas',
                                        'AI' => 'Agrupación de Interés Económico',
                                        'EN' => 'Entidad sin Fines de Lucro',
                                        'ON' => 'Organización No Gubernamental',
                                        'CL' => 'Club Deportivo',
                                        'CC' => 'Cámara de Comercio',
                                        'CI' => 'Colegio de Ingenieros',
                                        'CM' => 'Colegio de Médicos',
                                        'CA' => 'Colegio de Abogados',
                                        'IN' => 'Instituto',
                                        'UN' => 'Universidad',
                                        'ES' => 'Escuela',
                                        'CE' => 'Centro Educativo',
                                        'HO' => 'Hospital',
                                        'SN' => 'Sanatorio',
                                        'CX' => 'Centro de Salud',
                                        'IG' => 'Iglesia',
                                        'PA' => 'Parroquia',
                                        'OT' => 'Otro'
                                    ];
                                    echo htmlspecialchars($tipos[$entidad['tipo_entidad']] ?? $entidad['tipo_entidad'] ?? '-');
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($entidad['rep_nombre'] ?? '-') ?></td>
                                <td>
                                    <?php if ($entidad['email']): ?>
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($entidad['email']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($entidad['tel_celular']): ?>
                                        <i class="bi bi-phone"></i> <?= htmlspecialchars($entidad['tel_celular']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalles(<?= $entidad['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="editar_persona_juri_entidad.php?id=<?= $entidad['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminar(<?= $entidad['id'] ?>, '<?= htmlspecialchars($entidad['razon_social']) ?>')">
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
    function verDetalles(id) {
        // Hacer petición AJAX para obtener los detalles de la entidad
        fetch(`obtener_entidad_detalles.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const entidad = data.entidad;
                    
                    // Mapear tipos de entidad
                    const tiposEntidad = {
                        'SA': 'Sociedad Anónima',
                        'SR': 'Sociedad de Responsabilidad Limitada',
                        'AS': 'Sociedad por Acciones Simplificada',
                        'SC': 'Sociedad Colectiva',
                        'CS': 'Sociedad en Comandita Simple',
                        'CP': 'Sociedad en Comandita por Acciones',
                        'AC': 'Asociación Civil',
                        'FU': 'Fundación',
                        'CO': 'Cooperativa',
                        'MU': 'Mutual',
                        'SI': 'Sindicato',
                        'FE': 'Federación',
                        'CF': 'Confederación',
                        'UT': 'Unión Transitoria de Empresas',
                        'AI': 'Agrupación de Interés Económico',
                        'EN': 'Entidad sin Fines de Lucro',
                        'ON': 'Organización No Gubernamental',
                        'CL': 'Club Deportivo',
                        'CC': 'Cámara de Comercio',
                        'CI': 'Colegio de Ingenieros',
                        'CM': 'Colegio de Médicos',
                        'CA': 'Colegio de Abogados',
                        'IN': 'Instituto',
                        'UN': 'Universidad',
                        'ES': 'Escuela',
                        'CE': 'Centro Educativo',
                        'HO': 'Hospital',
                        'SN': 'Sanatorio',
                        'CX': 'Centro de Salud',
                        'IG': 'Iglesia',
                        'PA': 'Parroquia',
                        'OT': 'Otro'
                    };
                    
                    // Mapear cargos de representante
                    const cargos = {
                        'PR': 'Presidente',
                        'VP': 'Vicepresidente',
                        'SE': 'Secretario',
                        'TE': 'Tesorero',
                        'DI': 'Director',
                        'GE': 'Gerente',
                        'AP': 'Apoderado',
                        'AD': 'Administrador',
                        'SY': 'Síndico',
                        'RE': 'Rector',
                        'DE': 'Decano',
                        'CO': 'Coordinador'
                    };
                    
                    // Construir el HTML con los detalles
                    const detallesHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Datos de la Entidad</h6>
                                <p><strong>Razón Social:</strong><br>${entidad.razon_social || 'No especificada'}</p>
                                <p><strong>CUIT:</strong> ${entidad.cuit || 'No especificado'}</p>
                                <p><strong>Personería Jurídica:</strong> ${entidad.personeria || 'No especificada'}</p>
                                <p><strong>Tipo de Entidad:</strong> ${tiposEntidad[entidad.tipo_entidad] || entidad.tipo_entidad || 'No especificado'}</p>
                                <p><strong>Página Web:</strong> ${entidad.web ? `<a href="${entidad.web}" target="_blank">${entidad.web}</a>` : 'No especificada'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Contacto</h6>
                                <p><strong>Email:</strong> ${entidad.email || 'No especificado'}</p>
                                <p><strong>Teléfono Fijo:</strong> ${entidad.tel_fijo || 'No especificado'}</p>
                                <p><strong>Teléfono Celular:</strong> ${entidad.tel_celular || 'No especificado'}</p>
                                
                                <h6 class="text-primary mt-3">Domicilio</h6>
                                <p><strong>Dirección:</strong> ${entidad.domicilio || 'No especificada'}</p>
                                <p><strong>Localidad:</strong> ${entidad.localidad || 'No especificada'}</p>
                                <p><strong>Provincia:</strong> ${entidad.provincia || 'No especificada'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Representante Legal</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre y Apellido:</strong> ${entidad.rep_nombre || 'No especificado'}</p>
                                        <p><strong>Documento:</strong> ${entidad.rep_documento || 'No especificado'}</p>
                                        <p><strong>Cargo:</strong> ${cargos[entidad.rep_cargo] || entidad.rep_cargo || 'No especificado'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> ${entidad.rep_email || 'No especificado'}</p>
                                        <p><strong>Teléfono Fijo:</strong> ${entidad.rep_tel_fijo || 'No especificado'}</p>
                                        <p><strong>Teléfono Celular:</strong> ${entidad.rep_tel_celular || 'No especificado'}</p>
                                        <p><strong>Domicilio:</strong> ${entidad.rep_domicilio || 'No especificado'}</p>
                                    </div>
                                </div>
                                <p><strong>Fecha de registro:</strong> ${entidad.fecha_creacion || 'No disponible'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Mostrar modal con SweetAlert2
                    Swal.fire({
                        title: `<strong>Detalles de la Entidad</strong>`,
                        html: detallesHtml,
                        width: '900px',
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
            title: '¿Eliminar entidad?',
            html: `¿Está seguro que desea eliminar a <br><strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_persona_juri_entidad.php?id=${id}`;
            }
        });
    }
    </script>
</body>
</html>