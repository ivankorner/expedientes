<?php
session_start();
require 'header.php';
require 'head.php';

// Función para obtener el código completo basado en el código truncado
function obtenerCodigoCompleto($codigoTruncado) {
    $mapeo = [
        'CO' => ['COOPR', 'COM'],  // Podría ser Consorcio de Copropietarios o Comisión Municipal
        'CC' => ['CCOM', 'CCO', 'CCU'],  // Centro Comunitario, Comedor Comunitario, Centro Cultural
        'CR' => ['CREH'],  // Centro de Rehabilitación
        'CA' => ['CAP'],   // Capilla
        'HE' => ['HER'],   // Hermandad
        'TE' => ['TEM', 'TEI'],  // Templo, Teatro Independiente
        'AV' => ['AVE'],   // Asociación Vecinal
        'RG' => ['RGA'],   // Residencia Geriátrica
        'IT' => ['ITS'],   // Instituto Terciario/Superior
        'CE' => ['CEI', 'CES'],  // Centro de Investigación, Consejo Escolar
        'AC' => ['ACA', 'ACD'],  // Academia, Asociación de Clubes
        'AD' => ['ADE'],   // Asociación Deportiva
        'FD' => ['FDE'],   // Federación Deportiva
        'LD' => ['LDE'],   // Liga Deportiva
        'CL' => ['CLN'],   // Clínica
    ];
    
    return $mapeo[$codigoTruncado] ?? [];
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
                    <h1>Reparar Tipos de Entidad</h1>
                    <a href="listar_persona_juri_entidad.php" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>

                <?php
                try {
                    // Conectar a la base de datos
                    $db = new PDO(
                        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
                        "c2810161_iniciad",
                        "li62veMAdu",
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    echo "<div class='alert alert-info'>";
                    echo "<h5><i class='bi bi-info-circle'></i> Análisis de Datos</h5>";
                    echo "<p>Este script identifica entidades que pueden tener códigos de tipo truncados y sugiere correcciones.</p>";
                    echo "</div>";

                    // Obtener entidades con códigos posiblemente truncados
                    $sql = "SELECT id, razon_social, tipo_entidad, otro_tipo 
                            FROM persona_juri_entidad 
                            WHERE LENGTH(tipo_entidad) = 2 
                            AND tipo_entidad IN ('CO', 'CC', 'CR', 'CA', 'HE', 'TE', 'AV', 'RG', 'IT', 'CE', 'AC', 'AD', 'FD', 'LD', 'CL')
                            ORDER BY razon_social";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $entidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($entidades)) {
                        echo "<div class='alert alert-success'>";
                        echo "<h5><i class='bi bi-check-circle'></i> No hay problemas detectados</h5>";
                        echo "<p>No se encontraron entidades con códigos de tipo posiblemente truncados.</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-warning'>";
                        echo "<h5><i class='bi bi-exclamation-triangle'></i> Entidades con posibles problemas</h5>";
                        echo "<p>Se encontraron " . count($entidades) . " entidades que podrían tener códigos truncados:</p>";
                        echo "</div>";

                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped'>";
                        echo "<thead class='table-dark'>";
                        echo "<tr>";
                        echo "<th>ID</th>";
                        echo "<th>Razón Social</th>";
                        echo "<th>Código Actual</th>";
                        echo "<th>Otro Tipo</th>";
                        echo "<th>Posibles Correcciones</th>";
                        echo "<th>Acción</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";

                        foreach ($entidades as $entidad) {
                            $posiblesCodigos = obtenerCodigoCompleto($entidad['tipo_entidad']);
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($entidad['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($entidad['razon_social']) . "</td>";
                            echo "<td><span class='badge bg-warning'>" . htmlspecialchars($entidad['tipo_entidad']) . "</span></td>";
                            echo "<td>" . htmlspecialchars($entidad['otro_tipo'] ?: 'N/A') . "</td>";
                            echo "<td>";
                            
                            if (!empty($posiblesCodigos)) {
                                foreach ($posiblesCodigos as $codigo) {
                                    echo "<span class='badge bg-info me-1'>" . $codigo . "</span>";
                                }
                            } else {
                                echo "<span class='text-muted'>No hay sugerencias</span>";
                            }
                            
                            echo "</td>";
                            echo "<td>";
                            echo "<a href='editar_persona_juri_entidad.php?id=" . $entidad['id'] . "' class='btn btn-sm btn-primary'>";
                            echo "<i class='bi bi-pencil'></i> Editar";
                            echo "</a>";
                            echo "</td>";
                            echo "</tr>";
                        }

                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";

                        echo "<div class='alert alert-info mt-4'>";
                        echo "<h6><i class='bi bi-lightbulb'></i> Instrucciones para Reparar</h6>";
                        echo "<ol>";
                        echo "<li>Haga clic en 'Editar' para cada entidad que necesite corrección</li>";
                        echo "<li>En el formulario de edición, seleccione el tipo correcto de la lista desplegable</li>";
                        echo "<li>Guarde los cambios</li>";
                        echo "<li>El sistema ahora guardará el código completo sin truncar</li>";
                        echo "</ol>";
                        echo "</div>";
                    }

                    // Mostrar estadísticas generales
                    echo "<div class='row mt-4'>";
                    echo "<div class='col-md-4'>";
                    echo "<div class='card'>";
                    echo "<div class='card-body text-center'>";
                    echo "<h5 class='card-title'>Total Entidades</h5>";
                    
                    $sql_total = "SELECT COUNT(*) as total FROM persona_juri_entidad";
                    $stmt_total = $db->prepare($sql_total);
                    $stmt_total->execute();
                    $total = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    echo "<h3 class='text-primary'>" . $total . "</h3>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "<div class='col-md-4'>";
                    echo "<div class='card'>";
                    echo "<div class='card-body text-center'>";
                    echo "<h5 class='card-title'>Códigos de 2 Caracteres</h5>";
                    
                    $sql_cortos = "SELECT COUNT(*) as total FROM persona_juri_entidad WHERE LENGTH(tipo_entidad) = 2";
                    $stmt_cortos = $db->prepare($sql_cortos);
                    $stmt_cortos->execute();
                    $cortos = $stmt_cortos->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    echo "<h3 class='text-warning'>" . $cortos . "</h3>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "<div class='col-md-4'>";
                    echo "<div class='card'>";
                    echo "<div class='card-body text-center'>";
                    echo "<h5 class='card-title'>Códigos Largos (3+ chars)</h5>";
                    
                    $sql_largos = "SELECT COUNT(*) as total FROM persona_juri_entidad WHERE LENGTH(tipo_entidad) > 2";
                    $stmt_largos = $db->prepare($sql_largos);
                    $stmt_largos->execute();
                    $largos = $stmt_largos->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    echo "<h3 class='text-success'>" . $largos . "</h3>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>";
                    echo "<h5><i class='bi bi-exclamation-triangle'></i> Error</h5>";
                    echo "<p>Error al analizar los datos: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                }
                ?>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
