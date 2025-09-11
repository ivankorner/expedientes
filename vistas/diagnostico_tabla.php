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

    echo "<div class='container mt-4'>";
    echo "<h2>Diagnóstico de la tabla persona_juri_entidad</h2>";
    
    // Verificar estructura de la tabla
    echo "<h3>Estructura de la tabla:</h3>";
    $sql = "DESCRIBE persona_juri_entidad";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
    echo "<tbody>";
    foreach ($columns as $column) {
        $highlight = ($column['Field'] == 'tipo_entidad' || $column['Field'] == 'rep_cargo') ? 'table-warning' : '';
        echo "<tr class='{$highlight}'>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($column['Type']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // Verificar datos existentes en tipo_entidad
    echo "<h3>Datos actuales en tipo_entidad:</h3>";
    $sql = "SELECT tipo_entidad, COUNT(*) as cantidad, LENGTH(tipo_entidad) as longitud 
            FROM persona_juri_entidad 
            WHERE tipo_entidad IS NOT NULL 
            GROUP BY tipo_entidad 
            ORDER BY longitud DESC, tipo_entidad";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($tipos) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Tipo de Entidad</th><th>Cantidad</th><th>Longitud</th></tr></thead>";
        echo "<tbody>";
        foreach ($tipos as $tipo) {
            $highlight = (strlen($tipo['tipo_entidad']) > 2) ? 'table-danger' : '';
            echo "<tr class='{$highlight}'>";
            echo "<td>" . htmlspecialchars($tipo['tipo_entidad']) . "</td>";
            echo "<td>" . htmlspecialchars($tipo['cantidad']) . "</td>";
            echo "<td>" . htmlspecialchars($tipo['longitud']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No hay datos en la tabla.</p>";
    }
    
    // Verificar datos existentes en rep_cargo
    echo "<h3>Datos actuales en rep_cargo:</h3>";
    $sql = "SELECT rep_cargo, COUNT(*) as cantidad, LENGTH(rep_cargo) as longitud 
            FROM persona_juri_entidad 
            WHERE rep_cargo IS NOT NULL 
            GROUP BY rep_cargo 
            ORDER BY longitud DESC, rep_cargo";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($cargos) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Cargo</th><th>Cantidad</th><th>Longitud</th></tr></thead>";
        echo "<tbody>";
        foreach ($cargos as $cargo) {
            $highlight = (strlen($cargo['rep_cargo']) > 2) ? 'table-danger' : '';
            echo "<tr class='{$highlight}'>";
            echo "<td>" . htmlspecialchars($cargo['rep_cargo']) . "</td>";
            echo "<td>" . htmlspecialchars($cargo['cantidad']) . "</td>";
            echo "<td>" . htmlspecialchars($cargo['longitud']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No hay datos de cargos en la tabla.</p>";
    }
    
    // Verificar datos existentes en otro_tipo
    echo "<h3>Datos actuales en otro_tipo:</h3>";
    $sql = "SELECT otro_tipo, COUNT(*) as cantidad, LENGTH(otro_tipo) as longitud 
            FROM persona_juri_entidad 
            WHERE otro_tipo IS NOT NULL AND otro_tipo != ''
            GROUP BY otro_tipo 
            ORDER BY longitud DESC, otro_tipo";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $otros_tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($otros_tipos) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Otro Tipo</th><th>Cantidad</th><th>Longitud</th></tr></thead>";
        echo "<tbody>";
        foreach ($otros_tipos as $otro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($otro['otro_tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($otro['cantidad']) . "</td>";
            echo "<td>" . htmlspecialchars($otro['longitud']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No hay datos de tipos personalizados en la tabla.</p>";
    }
    
    // Mostrar información adicional
    echo "<h3>Información adicional:</h3>";
    echo "<div class='alert alert-info'>";
    echo "<p><strong>Campos problemáticos destacados en amarillo</strong></p>";
    echo "<p><strong>Datos que exceden el límite destacados en rojo</strong></p>";
    echo "<p>Si tipo_entidad o rep_cargo tienen longitud menor a 2-3 caracteres, necesitamos ampliar la columna.</p>";
    echo "<p><strong>Campo otro_tipo:</strong> Se usa para especificar tipos de entidad personalizados cuando se selecciona 'Otro'.</p>";
    echo "</div>";
    
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='container mt-4'>";
    echo "<div class='alert alert-danger'>";
    echo "Error de base de datos: " . $e->getMessage();
    echo "</div>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='container mt-4'>";
    echo "<div class='alert alert-danger'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Tabla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="listar_persona_juri_entidad.php" class="btn btn-primary">Volver al Listado</a>
    </div>
</body>
</html>
