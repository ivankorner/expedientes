<?php
session_start();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Método no permitido']);
    exit;
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode(['success'=>false,'message'=>'ID inválido']);
    exit;
}
try {
    $db = new PDO("mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4", "c2810161_iniciad", "li62veMAdu", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $db->prepare('DELETE FROM historial_lugares WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Pase eliminado']);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}



 // Conectar a la base de datos
    