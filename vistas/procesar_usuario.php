<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: listar_usuarios.php'); exit; }

// CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['mensaje'] = 'Token CSRF inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: listar_usuarios.php'); exit;
}

$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
$username = trim($_POST['username']);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? 'viewer');
$password = $_POST['password'] ?? '';

try {
    if ($id) {
        // update
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET username=?, nombre=?, apellido=?, email=?, role=?, password_hash=?, fecha_actualizacion = NOW() WHERE id = ?');
            $stmt->execute([$username,$nombre,$apellido,$email,$role,$hash,$id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET username=?, nombre=?, apellido=?, email=?, role=?, fecha_actualizacion = NOW() WHERE id = ?');
            $stmt->execute([$username,$nombre,$apellido,$email,$role,$id]);
        }
        $_SESSION['mensaje'] = 'Usuario actualizado';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: listar_usuarios.php'); exit;
    } else {
        // create
        if (empty($password)) { throw new Exception('Contraseña requerida'); }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (username,nombre,apellido,email,role,password_hash,fecha_creacion) VALUES (?,?,?,?,?,?,NOW())');
        $stmt->execute([$username,$nombre,$apellido,$email,$role,$hash]);
        $_SESSION['mensaje'] = 'Usuario creado';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: listar_usuarios.php'); exit;
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: listar_usuarios.php'); exit;
}
