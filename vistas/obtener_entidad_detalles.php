<?php
session_start();
header('Content-Type: application/json');

try {
    // Verificar que se recibió el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de entidad no válido'
        ]);
        exit;
    }

    $id = intval($_GET['id']);

    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener los detalles de la entidad
    $sql = "SELECT * FROM persona_juri_entidad WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $entidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entidad) {
        echo json_encode([
            'success' => true,
            'entidad' => $entidad
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Entidad no encontrada'
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
