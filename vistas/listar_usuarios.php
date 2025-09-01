<?php
session_start();
try {
    $db = new PDO("mysql:host=localhost;dbname=Iniciadores;charset=utf8mb4","root","",[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $db->query("SELECT id, username, nombre, apellido, email, role, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error: '.$e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/expedientes/publico/css/estilos.css">
</head>
<body>
    <?php require 'header.php'; ?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Administración de Usuarios</h1>
            <a href="crear_usuario.php" class="btn btn-primary">Nuevo Usuario</a>
        </div>

        <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?? 'info' ?>"><?php echo htmlspecialchars($_SESSION['mensaje']); ?></div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Creado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['apellido'].' '.$u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td><?= htmlspecialchars($u['fecha_creacion']) ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="crear_usuario.php?id=<?= $u['id'] ?>">Editar</a>
                            <a class="btn btn-sm btn-outline-danger" href="eliminar_usuario.php?id=<?= $u['id'] ?>" onclick="return confirm('Eliminar usuario?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
