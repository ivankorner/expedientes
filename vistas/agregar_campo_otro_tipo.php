<?php
session_start();
header('Content-Type: application/json');

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar si la columna ya existe
    $sql_check = "SHOW COLUMNS FROM persona_juri_entidad LIKE 'otro_tipo'";
    $stmt = $db->prepare($sql_check);
    $stmt->execute();
    $exists = $stmt->fetch();

    if (!$exists) {
        // Agregar la columna otro_tipo
        $sql = "ALTER TABLE persona_juri_entidad ADD COLUMN otro_tipo VARCHAR(100) NULL AFTER tipo_entidad";
        $db->exec($sql);
        
        echo json_encode([
            'success' => true,
            'message' => 'Campo "otro_tipo" agregado correctamente a la tabla persona_juri_entidad'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'El campo "otro_tipo" ya existe en la tabla'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
