
<?php
session_start();
header('Content-Type: application/json');

try {
    // Obtener y validar datos
    $data = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$id) {
        throw new Exception('ID de expediente inválido');
    }

    // Conectar a la base de datos
   $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Iniciar transacción
    $db->beginTransaction();

    // Eliminar primero registros relacionados en historial_lugares
    $sql = "DELETE FROM historial_lugares WHERE expediente_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    // Eliminar el expediente
    $sql = "DELETE FROM expedientes WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    // Confirmar transacción
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Expediente eliminado correctamente'
    ]);

} catch (Exception $e) {
    // Revertir cambios si hay error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar: ' . $e->getMessage()
    ]);
}