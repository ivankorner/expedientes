
<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Aceptar tanto GET como POST
    $id = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    } else {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    }
    
    if (!$id) {
        throw new Exception("ID inválido");
    }

    // Conectar a la base de datos (usar base local)
    require_once '../db/connection.php';
    $db = $pdo;

    // Obtener datos del expediente
    $stmt = $db->prepare("SELECT *, DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y %H:%i') as fecha_ingreso FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    // Obtener historial de movimientos
    $stmt = $db->prepare("
        SELECT 
            fecha_cambio,
            DATE_FORMAT(fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            lugar_anterior,
            lugar_nuevo,
            tipo_movimiento
        FROM historial_lugares 
        WHERE expediente_id = ?
        ORDER BY fecha_cambio ASC
    ");
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'expediente' => $expediente,
        'historial' => $historial
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}