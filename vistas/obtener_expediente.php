
<?php
header('Content-Type: application/json');

try {
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception("ID inválido");
    }

    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    echo json_encode($expediente);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}