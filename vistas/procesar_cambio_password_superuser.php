<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Verificar que el usuario esté logueado y sea superuser
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superuser') {
    $_SESSION['mensaje'] = 'Acceso denegado. Solo el Super Usuario puede realizar esta acción.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cambiar_password_superuser.php');
    exit;
}

// CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['mensaje'] = 'Token CSRF inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cambiar_password_superuser.php');
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validaciones
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['mensaje'] = 'Todos los campos son obligatorios';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cambiar_password_superuser.php');
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['mensaje'] = 'Las contraseñas nuevas no coinciden';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cambiar_password_superuser.php');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['mensaje'] = 'La nueva contraseña debe tener al menos 8 caracteres';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cambiar_password_superuser.php');
    exit;
}

// Validar que la nueva contraseña sea diferente a la actual
if ($current_password === $new_password) {
    $_SESSION['mensaje'] = 'La nueva contraseña debe ser diferente a la actual';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cambiar_password_superuser.php');
    exit;
}

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener el hash de la contraseña actual del super usuario
    $stmt = $db->prepare('SELECT password_hash FROM usuarios WHERE id = ? AND is_superuser = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['mensaje'] = 'Error: Usuario no encontrado o no es super usuario';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: cambiar_password_superuser.php');
        exit;
    }
    
    // Verificar la contraseña actual
    if (!password_verify($current_password, $user['password_hash'])) {
        $_SESSION['mensaje'] = 'La contraseña actual es incorrecta';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: cambiar_password_superuser.php');
        exit;
    }
    
    // Generar el hash de la nueva contraseña
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña en la base de datos
    $stmt = $db->prepare('UPDATE usuarios SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ? AND is_superuser = 1');
    $result = $stmt->execute([$new_password_hash, $_SESSION['user_id']]);
    
    if ($result) {
        // Registrar el cambio en un log de seguridad (opcional)
        $log_stmt = $db->prepare('INSERT INTO logs_seguridad (user_id, accion, descripcion, ip_address, fecha) VALUES (?, ?, ?, ?, NOW())');
        $log_stmt->execute([
            $_SESSION['user_id'],
            'CAMBIO_PASSWORD_SUPERUSER',
            'El super usuario cambió su contraseña',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        $_SESSION['mensaje'] = '✅ Contraseña del Super Usuario actualizada exitosamente';
        $_SESSION['tipo_mensaje'] = 'success';
        
        // Opcional: Regenerar la sesión por seguridad
        session_regenerate_id(true);
        
    } else {
        $_SESSION['mensaje'] = 'Error al actualizar la contraseña';
        $_SESSION['tipo_mensaje'] = 'danger';
    }
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error del sistema: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    
    // En producción, registrar el error en un log
    error_log("Error cambio password superuser: " . $e->getMessage());
}

header('Location: cambiar_password_superuser.php');
exit;
?>
