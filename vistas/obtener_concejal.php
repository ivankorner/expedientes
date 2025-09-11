<?php
session_start();
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de concejal no válido.'
    ]);
    exit;
}

$id = intval($_GET['id']);

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener datos del concejal
    $stmt = $db->prepare("SELECT * FROM concejales WHERE id = ?");
    $stmt->execute([$id]);
    $concejal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$concejal) {
        echo json_encode([
            'success' => false,
            'message' => 'El concejal no existe.'
        ]);
        exit;
    }

    // Limpiar valores nulos y escapar HTML
    $concejal_limpio = [];
    foreach ($concejal as $key => $value) {
        $concejal_limpio[$key] = $value ? htmlspecialchars($value) : '';
    }

    echo json_encode([
        'success' => true,
        'concejal' => $concejal_limpio
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar los datos: ' . $e->getMessage()
    ]);
}
?>
