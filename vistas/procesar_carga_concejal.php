
<?php
session_start();

try {
  // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Validar DNI único si se proporciona
    if (!empty($_POST['dni'])) {
        $stmt = $db->prepare("SELECT id FROM concejales WHERE dni = ?");
        $stmt->execute([$_POST['dni']]);
        if ($stmt->fetch()) {
            throw new Exception("Ya existe un concejal registrado con ese DNI");
        }
    }

    // Validar email si se proporciona
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido");
    }

    // Preparar los datos para insertar
    $datos = [
        'apellido' => trim($_POST['apellido'] ?? ''),
        'nombre' => trim($_POST['nombre'] ?? ''),
        'dni' => trim($_POST['dni'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'tel' => trim($_POST['tel'] ?? ''),
        'cel' => trim($_POST['cel'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'bloque' => trim($_POST['bloque'] ?? '')
    ];

    // Filtrar campos vacíos
    $datos = array_filter($datos, function($valor) {
        return $valor !== '';
    });

    // Verificar si hay datos para insertar
    if (empty($datos)) {
        throw new Exception("Debe proporcionar al menos un dato");
    }

    // Preparar la consulta SQL
    $campos = implode(', ', array_keys($datos));
    $valores = ':' . implode(', :', array_keys($datos));
    
    $sql = "INSERT INTO concejales ($campos) VALUES ($valores)";
    
    // Ejecutar la consulta
    $stmt = $db->prepare($sql);
    $stmt->execute($datos);

    // Mensaje de éxito
    $_SESSION['mensaje'] = "Concejal registrado correctamente";
    $_SESSION['tipo_mensaje'] = "success";

    // Redireccionar
    header("Location: carga_concejal.php");
    exit;

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    $_SESSION['form_data'] = $_POST;
    header("Location: carga_concejal.php");
    exit;
}