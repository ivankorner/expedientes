<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

$isEdit = false;
$user = ['id'=>'','username'=>'','nombre'=>'','apellido'=>'','email'=>'','role'=>'user'];
if (!empty($_GET['id'])) {
    $isEdit = true;
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT id, username, nombre, apellido, email, role FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $_SESSION['mensaje'] = 'Usuario no encontrado';
        $_SESSION['tipo_mensaje'] = 'warning';
        header('Location: listar_usuarios.php'); exit;
    }
}

// CSRF token
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= $isEdit ? 'Editar' : 'Crear' ?> Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require 'header.php'; ?>
    <div class="container py-4">
        <h1><?= $isEdit ? 'Editar' : 'Crear' ?> Usuario</h1>
        <form method="post" action="procesar_usuario.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <div class="mb-3">
                <label class="form-label">Usuario (login)</label>
                <input class="form-control" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellido</label>
                    <input class="form-control" name="apellido" value="<?= htmlspecialchars($user['apellido']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input class="form-control" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="role" class="form-select">
                    <option value="admin" <?= ($user['role']==='admin')? 'selected':'' ?>>Administrador</option>
                    <option value="editor" <?= ($user['role']==='editor')? 'selected':'' ?>>Editor</option>
                    <option value="viewer" <?= ($user['role']==='viewer')? 'selected':'' ?>>Solo lectura</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña <?= $isEdit ? '(dejar vacía para no cambiar)' : '' ?></label>
                <input class="form-control" type="password" name="password" <?= $isEdit ? '' : 'required' ?>>
            </div>
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-secondary" href="listar_usuarios.php">Cancelar</a>
        </form>
    </div>
</body>
</html>
