<?php
session_start();

try {
    // Verificar que se recibió el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['mensaje'] = "ID de entidad no válido";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    $id = intval($_GET['id']);

    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar que la entidad existe
    $sql_verificar = "SELECT razon_social FROM persona_juri_entidad WHERE id = :id";
    $stmt = $db->prepare($sql_verificar);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $entidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entidad) {
        $_SESSION['mensaje'] = "La entidad no existe";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    // TODO: Verificar si la entidad tiene expedientes asociados
    // $sql_expedientes = "SELECT COUNT(*) FROM expedientes WHERE iniciador_id = :id";
    // $stmt_exp = $db->prepare($sql_expedientes);
    // $stmt_exp->bindParam(':id', $id, PDO::PARAM_INT);
    // $stmt_exp->execute();
    // $tiene_expedientes = $stmt_exp->fetchColumn() > 0;
    
    // if ($tiene_expedientes) {
    //     $_SESSION['mensaje'] = "No se puede eliminar la entidad porque tiene expedientes asociados";
    //     $_SESSION['tipo_mensaje'] = "warning";
    //     header('Location: listar_persona_juri_entidad.php');
    //     exit;
    // }

    // Eliminar la entidad
    $sql_eliminar = "DELETE FROM persona_juri_entidad WHERE id = :id";
    $stmt_eliminar = $db->prepare($sql_eliminar);
    $stmt_eliminar->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt_eliminar->execute()) {
        $_SESSION['mensaje'] = "Entidad '" . htmlspecialchars($entidad['razon_social']) . "' eliminada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la entidad";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta al listado
header('Location: listar_persona_juri_entidad.php');
exit;
?>
