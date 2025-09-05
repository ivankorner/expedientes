
<?php
session_start();

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
        'direccion' => filter_var(trim($_POST['direccion'] ?? ''), FILTER_SANITIZE_STRING),
        'tel' => filter_var(trim($_POST['tel'] ?? ''), FILTER_SANITIZE_STRING),
        'cel' => filter_var(trim($_POST['cel'] ?? ''), FILTER_SANITIZE_STRING),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : null,
        'observacion' => filter_var(trim($_POST['observacion'] ?? ''), FILTER_SANITIZE_STRING)
    ];

    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    

    // Verificar si el DNI ya existe
    $stmt = $db->prepare("SELECT id FROM persona_fisica WHERE dni = ?");
    $stmt->execute([$datos['dni']]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe una persona registrada con ese DNI");
    }

    // Preparar la consulta SQL
    $sql = "INSERT INTO persona_fisica (
                apellido, nombre, dni, direccion, tel, cel, email, observacion
            ) VALUES (
                :apellido, :nombre, :dni, :direccion, :tel, :cel, :email, :observacion
            )";

    // Ejecutar la consulta
    $stmt = $db->prepare($sql);
    $stmt->execute($datos);

    // Guardar mensaje de éxito
    $_SESSION['mensaje'] = "Iniciador registrado correctamente";
    $_SESSION['tipo_mensaje'] = "success";

    // Redireccionar
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