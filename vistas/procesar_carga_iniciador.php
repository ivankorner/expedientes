
<?php
session_start();

// Debug temporal - verificar que el script se ejecuta
error_log("Procesando carga de iniciador - " . date('Y-m-d H:i:s'));

try {
    // Validar datos recibidos
    $requeridos = ['apellido', 'nombre', 'dni'];
    foreach ($requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es obligatorio");
        }
    }

    // Sanitizar datos
    $datos = [
        'apellido' => filter_var(trim($_POST['apellido']), FILTER_SANITIZE_STRING),
        'nombre' => filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING),
        'dni' => filter_var(trim($_POST['dni']), FILTER_SANITIZE_STRING),
        'cuil' => filter_var(trim($_POST['cuil'] ?? ''), FILTER_SANITIZE_STRING),
        'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
        'nacionalidad' => filter_var(trim($_POST['nacionalidad'] ?? ''), FILTER_SANITIZE_STRING),
        'estado_civil' => filter_var(trim($_POST['estado_civil'] ?? ''), FILTER_SANITIZE_STRING),
        'profesion' => filter_var(trim($_POST['profesion'] ?? ''), FILTER_SANITIZE_STRING),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '',
        'tel' => filter_var(trim($_POST['tel'] ?? ''), FILTER_SANITIZE_STRING),
        'cel' => filter_var(trim($_POST['cel'] ?? ''), FILTER_SANITIZE_STRING),
        'calle' => filter_var(trim($_POST['calle'] ?? ''), FILTER_SANITIZE_STRING),
        'numero' => filter_var(trim($_POST['numero'] ?? ''), FILTER_SANITIZE_STRING),
        'piso' => filter_var(trim($_POST['piso'] ?? ''), FILTER_SANITIZE_STRING),
        'depto' => filter_var(trim($_POST['depto'] ?? ''), FILTER_SANITIZE_STRING),
        'localidad' => filter_var(trim($_POST['localidad'] ?? ''), FILTER_SANITIZE_STRING),
        'cp' => filter_var(trim($_POST['cp'] ?? ''), FILTER_SANITIZE_STRING),
        'observaciones' => filter_var(trim($_POST['observaciones'] ?? ''), FILTER_SANITIZE_STRING)
    ];

    // Construir dirección completa para compatibilidad
    $direccion_partes = array_filter([
        $datos['calle'],
        $datos['numero'],
        $datos['piso'] ? "Piso: " . $datos['piso'] : null,
        $datos['depto'] ? "Depto: " . $datos['depto'] : null,
        $datos['localidad'],
        $datos['cp'] ? "CP: " . $datos['cp'] : null
    ]);
    $direccion_completa = implode(', ', $direccion_partes);

    // Validar fecha de nacimiento si se proporcionó
    if (!empty($datos['fecha_nacimiento'])) {
        $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_nacimiento']);
        if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_nacimiento']) {
            throw new Exception("La fecha de nacimiento no tiene un formato válido");
        }
    }

    // Validar email si se proporcionó
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del email no es válido");
    }

    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar si el DNI ya existe
    $stmt = $pdo->prepare("SELECT id FROM persona_fisica WHERE dni = ?");
    $stmt->execute([$datos['dni']]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe una persona registrada con ese DNI");
    }

    // Verificar si el CUIL ya existe (si se proporcionó)
    if (!empty($datos['cuil'])) {
        $stmt = $pdo->prepare("SELECT id FROM persona_fisica WHERE cuil = ? AND cuil != ''");
        $stmt->execute([$datos['cuil']]);
        if ($stmt->fetch()) {
            throw new Exception("Ya existe una persona registrada con ese CUIL");
        }
    }

    // Preparar la consulta SQL con campos básicos primero
    $sql = "INSERT INTO persona_fisica (
                apellido, nombre, dni, direccion, tel, cel, email, observacion
            ) VALUES (
                :apellido, :nombre, :dni, :direccion, :tel, :cel, :email, :observaciones
            )";

    // Preparar datos básicos para la inserción
    $datos_insercion = [
        'apellido' => $datos['apellido'],
        'nombre' => $datos['nombre'],
        'dni' => $datos['dni'],
        'direccion' => $direccion_completa ?: ($datos['calle'] . ' ' . $datos['numero']),
        'tel' => $datos['tel'],
        'cel' => $datos['cel'],
        'email' => $datos['email'],
        'observaciones' => $datos['observaciones']
    ];

    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos_insercion);

    // Guardar mensaje de éxito
    $_SESSION['mensaje'] = "Iniciador registrado correctamente con ID: " . $pdo->lastInsertId();
    $_SESSION['tipo_mensaje'] = "success";
    
    // Debug temporal
    error_log("Mensaje de sesión establecido: " . $_SESSION['mensaje']);

    // Redireccionar de vuelta al formulario para mostrar SweetAlert
    header("Location: carga_iniciador.php");
    exit;

} catch (Exception $e) {
    // Guardar mensaje de error
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
    // Guardar datos del formulario para recuperarlos
    $_SESSION['form_data'] = $_POST;
    
    // Redireccionar
    header("Location: carga_iniciador.php");
    exit;
}