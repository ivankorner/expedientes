<?php
session_start();

try {
    // Conectar a la base de datos
    require_once '../db/connection.php';
    $db = $pdo;

    echo "<h3>Iniciando migración de bloques históricos...</h3>";

    // Verificar si la tabla de historial existe, si no, crearla
    $stmt = $db->prepare("SHOW TABLES LIKE 'concejal_bloques_historial'");
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "<p>Creando tabla concejal_bloques_historial...</p>";
        
        $create_table_sql = "
            CREATE TABLE concejal_bloques_historial (
                id INT AUTO_INCREMENT PRIMARY KEY,
                concejal_id INT NOT NULL,
                nombre_bloque VARCHAR(255) NOT NULL,
                fecha_inicio DATE,
                fecha_fin DATE,
                es_actual BOOLEAN DEFAULT FALSE,
                observacion TEXT,
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (concejal_id) REFERENCES concejales(id) ON DELETE CASCADE,
                INDEX idx_concejal_id (concejal_id),
                INDEX idx_es_actual (es_actual),
                INDEX idx_fecha_inicio (fecha_inicio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $db->exec($create_table_sql);
        echo "<p style='color: green;'>✓ Tabla creada exitosamente</p>";
    } else {
        echo "<p>La tabla concejal_bloques_historial ya existe</p>";
    }

    // Verificar si ya hay datos migrados
    $stmt = $db->prepare("SELECT COUNT(*) FROM concejal_bloques_historial");
    $stmt->execute();
    $registros_historial = $stmt->fetchColumn();

    if ($registros_historial > 0) {
        echo "<p style='color: orange;'>⚠️ Ya existen $registros_historial registros en el historial</p>";
        echo "<p>¿Desea continuar con la migración? Esto podría crear duplicados.</p>";
        
        // En un entorno real, aquí pedirías confirmación del usuario
        echo "<p style='color: red;'>Migración cancelada para evitar duplicados</p>";
        echo "<p><a href='listar_concejales.php'>Volver al listado</a></p>";
        exit;
    }

    // Obtener todos los concejales con bloque definido
    $stmt = $db->prepare("
        SELECT id, nombre, apellido, bloque 
        FROM concejales 
        WHERE bloque IS NOT NULL AND bloque != ''
        ORDER BY apellido, nombre
    ");
    $stmt->execute();
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Encontrados " . count($concejales) . " concejales con bloques definidos</p>";

    if (count($concejales) > 0) {
        echo "<h4>Migrando datos...</h4>";
        
        $migrados = 0;
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            foreach ($concejales as $concejal) {
                // Insertar el bloque actual como registro histórico
                $stmt = $db->prepare("
                    INSERT INTO concejal_bloques_historial 
                    (concejal_id, nombre_bloque, fecha_inicio, es_actual, observacion, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $concejal['id'],
                    $concejal['bloque'],
                    null, // No tenemos fecha de inicio histórica
                    1, // Marcar como actual
                    'Bloque migrado desde datos existentes'
                ]);
                
                $migrados++;
                echo "<p>✓ Migrado: {$concejal['apellido']}, {$concejal['nombre']} - Bloque: {$concejal['bloque']}</p>";
            }
            
            // Confirmar transacción
            $db->commit();
            
            echo "<h4 style='color: green;'>✅ Migración completada exitosamente</h4>";
            echo "<p>Se migraron <strong>$migrados</strong> registros al historial</p>";
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    echo "<hr>";
    echo "<h4>Resumen de la migración:</h4>";
    
    // Mostrar estadísticas
    $stmt = $db->prepare("SELECT COUNT(*) FROM concejal_bloques_historial");
    $stmt->execute();
    $total_historial = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM concejal_bloques_historial WHERE es_actual = 1");
    $stmt->execute();
    $actuales = $stmt->fetchColumn();
    
    echo "<ul>";
    echo "<li>Total de registros en historial: <strong>$total_historial</strong></li>";
    echo "<li>Bloques marcados como actuales: <strong>$actuales</strong></li>";
    echo "</ul>";
    
    echo "<p><a href='listar_concejales.php' class='btn btn-primary'>Volver al listado de concejales</a></p>";

} catch (Exception $e) {
    echo "<div style='color: red; border: 1px solid red; padding: 10px; margin: 10px 0;'>";
    echo "<h4>❌ Error en la migración:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Bloques Históricos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 20px; 
        }
        .btn { 
            display: inline-block; 
            padding: 8px 16px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
        }
        .btn:hover { 
            background: #0056b3; 
            color: white; 
            text-decoration: none; 
        }
    </style>
</head>
<body>
</body>
</html>