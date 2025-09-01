<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

if (empty($_GET['id'])) { header('Location: listar_usuarios.php'); exit; }
$id = (int)$_GET['id'];

// Prevent deleting self
if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
    $_SESSION['mensaje'] = 'No puede eliminar su propia cuenta mientras esté autenticado.';
    $_SESSION['tipo_mensaje'] = 'warning';
    header('Location: listar_usuarios.php'); exit;
}

$stmt = $pdo->prepare('UPDATE usuarios SET is_active = 0 WHERE id = ?');
$stmt->execute([$id]);
$_SESSION['mensaje'] = 'Usuario desactivado';
$_SESSION['tipo_mensaje'] = 'info';
header('Location: listar_usuarios.php'); exit;
