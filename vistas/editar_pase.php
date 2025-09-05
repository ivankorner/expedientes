<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $fecha = $_POST['fecha'] ?? '';
    $lugar_nuevo = $_POST['lugar_nuevo'] ?? '';
    if (!$id || !$fecha || !$lugar_nuevo) {
        throw new Exception('Datos incompletos');
    }
   $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $db->prepare("UPDATE historial_lugares SET fecha_cambio = ?, lugar_nuevo = ? WHERE id = ?");
    $stmt->execute([$fecha, $lugar_nuevo, $id]);
    echo json_encode(['success' => true, 'message' => 'Pase actualizado']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
