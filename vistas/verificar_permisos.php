<?php
/**
 * Middleware de verificación de permisos
 * Controla el acceso a las diferentes vistas según el rol del usuario
 */

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Verifica si el usuario tiene permisos para acceder a una vista específica
 * @param string $vista Nombre del archivo de vista
 */
function verificarPermisoVista($vista) {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
        $_SESSION['mensaje'] = 'Debe iniciar sesión para acceder a esta página';
        $_SESSION['tipo_mensaje'] = 'warning';
        header('Location: login.php');
        exit;
    }

    $rol = $_SESSION['rol'];
    $usuario = $_SESSION['usuario'];

    // Definir permisos por vista
    $permisos = [
        // Páginas administrativas - Solo admin y superuser
        'crear_usuario.php' => ['admin', 'superuser'],
        'listar_usuarios.php' => ['admin', 'superuser'],
        'eliminar_usuario.php' => ['admin', 'superuser'],
        
        // Páginas exclusivas de superuser
        'gestionar_roles_usuarios.php' => ['superuser'],
        'procesar_cambio_rol.php' => ['superuser'],
        'configuracion_permisos.php' => ['admin', 'superuser'],
        'verificacion_permisos.php' => ['admin', 'superuser'],
        
        // Páginas de gestión de expedientes - Admin, superuser y usuario
        'carga_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'listar_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'actualizar_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'eliminar_expediente.php' => ['admin', 'superuser'],
        'pases_expediente.php' => ['admin', 'superuser', 'usuario'],
        'editar_pase.php' => ['admin', 'superuser', 'usuario'],
        'eliminar_pase.php' => ['admin', 'superuser', 'usuario'],
        'historial_expediente.php' => ['admin', 'superuser', 'usuario'],
        
        // Páginas de gestión de iniciadores - Admin, superuser y usuario
        'carga_iniciador.php' => ['admin', 'superuser', 'usuario'],
        'listar_iniciadores.php' => ['admin', 'superuser', 'usuario'],
        'carga_concejal.php' => ['admin', 'superuser', 'usuario'],
        'listar_concejales.php' => ['admin', 'superuser', 'usuario'],
        'carga_persona_juri_entidad.php' => ['admin', 'superuser', 'usuario'],
        'listar_persona_juri_entidad.php' => ['admin', 'superuser', 'usuario'],
        
        // Búsquedas - Todos los roles
        'buscar_expediente.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'busqueda_rapida.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'consulta.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'resultados.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        
        // Dashboard - Todos los usuarios logueados
        'dashboard.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        
        // Páginas de procesamiento - Admin, superuser y usuario
        'procesar_carga_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_iniciador.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_concejal.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_entidad.php' => ['admin', 'superuser', 'usuario'],
        'procesar_usuario.php' => ['admin', 'superuser'],
        'procesar_pase.php' => ['admin', 'superuser', 'usuario'],
        'procesar_actualizacion.php' => ['admin', 'superuser', 'usuario'],
        
        // PDFs y obtención de datos
        'pdf_auto_descarga.php' => ['admin', 'superuser', 'usuario'],
        'generar_pdf_expediente.php' => ['admin', 'superuser', 'usuario'],
        'obtener_expediente.php' => ['admin', 'superuser', 'usuario'],
        'obtener_historial.php' => ['admin', 'superuser', 'usuario'],
        'obtener_historial_pases.php' => ['admin', 'superuser', 'usuario']
    ];

    // Si la vista no está definida en permisos, permitir acceso a usuarios logueados
    if (!isset($permisos[$vista])) {
        return true;
    }

    // Verificar si el rol del usuario tiene permiso para esta vista
    if (!in_array($rol, $permisos[$vista])) {
        $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta página';
        $_SESSION['tipo_mensaje'] = 'danger';
        
        // Redirigir según el rol
        switch ($rol) {
            case 'superuser':
                header('Location: dashboard.php');
                break;
            case 'admin':
                header('Location: dashboard.php');
                break;
            case 'usuario':
                header('Location: dashboard.php');
                break;
            case 'consulta':
                header('Location: consulta.php');
                break;
            default:
                header('Location: login.php');
                break;
        }
        exit;
    }

    return true;
}

/**
 * Verifica si el usuario es administrador
 * @return bool
 */
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Verifica si el usuario puede gestionar expedientes
 * @return bool
 */
function puedeGestionarExpedientes() {
    return isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'usuario']);
}

/**
 * Verifica si el usuario solo puede consultar
 * @return bool
 */
function soloConsulta() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'consulta';
}
?>