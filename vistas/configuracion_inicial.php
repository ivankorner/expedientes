<?php
/**
 * Script para crear usuario administrador por defecto
 * Ejecutar una sola vez para crear el primer usuario admin
 */

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar si ya existe un usuario admin
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount > 0) {
        echo "Ya existe al menos un usuario administrador en el sistema.\n";
        echo "Usuarios administradores existentes:\n";
        
        $stmt = $db->prepare("SELECT username, nombre, apellido, email, is_active FROM usuarios WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins as $admin) {
            $status = $admin['is_active'] ? 'Activo' : 'Inactivo';
            echo "- {$admin['username']} ({$admin['nombre']} {$admin['apellido']}) - {$status}\n";
        }
    } else {
        echo "No se encontraron usuarios administradores. Creando usuario admin por defecto...\n";
        
        // Datos del usuario admin por defecto
        $adminData = [
            'username' => 'admin',
            'password' => 'admin123', // Cambiar después del primer login
            'nombre' => 'Administrador',
            'apellido' => 'Sistema',
            'email' => 'admin@expedientescde.online',
            'role' => 'admin',
            'is_active' => 1,
            'is_superuser' => 1
        ];
        
        // Hash de la contraseña
        $passwordHash = password_hash($adminData['password'], PASSWORD_DEFAULT);
        
        // Insertar usuario
        $stmt = $db->prepare("
            INSERT INTO usuarios (username, password_hash, nombre, apellido, email, role, is_active, is_superuser, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $adminData['username'],
            $passwordHash,
            $adminData['nombre'],
            $adminData['apellido'],
            $adminData['email'],
            $adminData['role'],
            $adminData['is_active'],
            $adminData['is_superuser']
        ]);
        
        echo "✅ Usuario administrador creado exitosamente!\n";
        echo "Credenciales:\n";
        echo "Usuario: {$adminData['username']}\n";
        echo "Contraseña: {$adminData['password']}\n";
        echo "\n⚠️ IMPORTANTE: Cambie la contraseña después del primer login por seguridad.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Si la tabla no existe, intentar crearla
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo "\nIntentando crear la tabla 'usuarios'...\n";
        
        try {
            $createTable = "
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    nombre VARCHAR(100) NOT NULL,
                    apellido VARCHAR(100) NOT NULL,
                    email VARCHAR(150) UNIQUE NOT NULL,
                    role ENUM('admin', 'usuario', 'consulta') DEFAULT 'usuario',
                    is_active TINYINT(1) DEFAULT 1,
                    is_superuser TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";
            
            $db->exec($createTable);
            echo "✅ Tabla 'usuarios' creada exitosamente.\n";
            echo "Ejecute este script nuevamente para crear el usuario administrador.\n";
            
        } catch (Exception $e2) {
            echo "❌ Error al crear la tabla: " . $e2->getMessage() . "\n";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - Sistema de Expedientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-gear"></i> Configuración Inicial del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Configuración completada.</strong> Revise los mensajes arriba para más detalles.
                        </div>
                        
                        <div class="mt-4">
                            <h6><i class="bi bi-list-check"></i> Siguientes Pasos:</h6>
                            <ol>
                                <li>Ir a la página de <a href="login.php" class="btn btn-sm btn-primary">Login</a></li>
                                <li>Usar las credenciales mostradas arriba</li>
                                <li>Cambiar la contraseña por defecto</li>
                                <li>Crear usuarios adicionales según sea necesario</li>
                            </ol>
                        </div>
                        
                        <div class="mt-4">
                            <h6><i class="bi bi-tools"></i> Herramientas de Diagnóstico:</h6>
                            <div class="btn-group" role="group">
                                <a href="diagnostico_sesion.php" class="btn btn-outline-info">
                                    <i class="bi bi-search"></i> Verificar Sesión
                                </a>
                                <a href="login.php" class="btn btn-success">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>