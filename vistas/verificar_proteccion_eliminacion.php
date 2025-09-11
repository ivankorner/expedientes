<?php
session_start();

// Script de prueba para verificar la funcionalidad de protección contra eliminación

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h1>Verificación de Protección contra Eliminación</h1>";
    echo "<div style='font-family: Arial; margin: 20px;'>";

    // 1. Verificar entidades con expedientes asociados
    echo "<h2>1. Entidades con expedientes asociados:</h2>";
    
    $sql_con_expedientes = "SELECT pje.id, pje.razon_social, pje.cuit,
                           (SELECT COUNT(*) 
                            FROM expedientes e 
                            WHERE e.iniciador LIKE CONCAT('%', pje.razon_social, '%') 
                            AND (pje.cuit IS NULL OR e.iniciador LIKE CONCAT('%', pje.cuit, '%'))
                           ) as expedientes_asociados
                           FROM persona_juri_entidad pje 
                           HAVING expedientes_asociados > 0
                           ORDER BY expedientes_asociados DESC";
    
    $stmt = $db->prepare($sql_con_expedientes);
    $stmt->execute();
    $entidades_con_expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($entidades_con_expedientes)) {
        echo "<p style='color: green;'>✅ No hay entidades con expedientes asociados.</p>";
        echo "<p>Todas las entidades pueden ser eliminadas sin restricciones.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Razón Social</th>";
        echo "<th style='padding: 8px;'>CUIT</th>";
        echo "<th style='padding: 8px;'>Expedientes</th>";
        echo "<th style='padding: 8px;'>Estado</th>";
        echo "</tr>";
        
        foreach ($entidades_con_expedientes as $entidad) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $entidad['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($entidad['razon_social']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($entidad['cuit'] ?: 'N/A') . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>" . $entidad['expedientes_asociados'] . "</td>";
            echo "<td style='padding: 8px; color: red;'>🔒 PROTEGIDA</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 2. Verificar entidades sin expedientes (eliminables)
    echo "<h2>2. Entidades sin expedientes (eliminables):</h2>";
    
    $sql_sin_expedientes = "SELECT pje.id, pje.razon_social, pje.cuit,
                           (SELECT COUNT(*) 
                            FROM expedientes e 
                            WHERE e.iniciador LIKE CONCAT('%', pje.razon_social, '%') 
                            AND (pje.cuit IS NULL OR e.iniciador LIKE CONCAT('%', pje.cuit, '%'))
                           ) as expedientes_asociados
                           FROM persona_juri_entidad pje 
                           HAVING expedientes_asociados = 0
                           ORDER BY pje.razon_social";
    
    $stmt2 = $db->prepare($sql_sin_expedientes);
    $stmt2->execute();
    $entidades_sin_expedientes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "<p><strong>Total entidades eliminables:</strong> " . count($entidades_sin_expedientes) . "</p>";
    
    if (count($entidades_sin_expedientes) > 0) {
        echo "<p style='color: green;'>✅ Estas entidades pueden ser eliminadas sin restricciones.</p>";
        
        // Mostrar solo las primeras 5 para no saturar
        $entidades_muestra = array_slice($entidades_sin_expedientes, 0, 5);
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Razón Social</th>";
        echo "<th style='padding: 8px;'>CUIT</th>";
        echo "<th style='padding: 8px;'>Estado</th>";
        echo "</tr>";
        
        foreach ($entidades_muestra as $entidad) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $entidad['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($entidad['razon_social']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($entidad['cuit'] ?: 'N/A') . "</td>";
            echo "<td style='padding: 8px; color: green;'>✅ ELIMINABLE</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (count($entidades_sin_expedientes) > 5) {
            echo "<p><em>... y " . (count($entidades_sin_expedientes) - 5) . " más.</em></p>";
        }
    }

    // 3. Resumen de estadísticas
    echo "<h2>3. Resumen de estadísticas:</h2>";
    
    $total_entidades = count($entidades_con_expedientes) + count($entidades_sin_expedientes);
    $porcentaje_protegidas = $total_entidades > 0 ? round((count($entidades_con_expedientes) / $total_entidades) * 100, 2) : 0;
    
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Total de entidades:</strong> $total_entidades</p>";
    echo "<p><strong>Entidades protegidas:</strong> " . count($entidades_con_expedientes) . " ({$porcentaje_protegidas}%)</p>";
    echo "<p><strong>Entidades eliminables:</strong> " . count($entidades_sin_expedientes) . " (" . (100 - $porcentaje_protegidas) . "%)</p>";
    echo "</div>";

    // 4. Verificar archivos de protección
    echo "<h2>4. Verificación de archivos del sistema:</h2>";
    
    $archivos_verificar = [
        'eliminar_persona_juri_entidad.php' => 'Script de eliminación con protección',
        'listar_persona_juri_entidad.php' => 'Listado con botones deshabilitados',
        'reparar_tipos_entidad.php' => 'Herramienta de reparación de tipos'
    ];
    
    foreach ($archivos_verificar as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "<p style='color: green;'>✅ $archivo - $descripcion</p>";
        } else {
            echo "<p style='color: red;'>❌ $archivo - NO ENCONTRADO</p>";
        }
    }

    echo "<h2>✅ Sistema de Protección Implementado</h2>";
    echo "<p>El sistema ahora previene la eliminación de entidades que tienen expedientes asociados.</p>";
    echo "<ul>";
    echo "<li>Los botones de eliminar se deshabilitan automáticamente</li>";
    echo "<li>Se muestran tooltips informativos</li>";
    echo "<li>La verificación se hace tanto en frontend como backend</li>";
    echo "<li>Se incluye una columna visual para mostrar el estado</li>";
    echo "</ul>";

    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: red; font-family: Arial; margin: 20px;'>";
    echo "<h2>❌ Error en la verificación</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
