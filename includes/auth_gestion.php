<?php
// Vistas compartidas entre administrador y manager
// (laboratorios, horarios, reservaciones y reportes).
// El manager solo ve y modifica datos de su departamento.
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/permisos.php";

if (!isset($_SESSION['usuario'])) {
    require_once __DIR__ . "/config.php";
    header("Location: " . BASE_URL . "/index.html");
    exit();
}

if (!puedeGestionar()) {
    echo "Acceso denegado";
    exit();
}

// Un manager que entra por una ruta de Views/Admin (p. ej. después de una
// redirección de un controlador) se envía a la misma vista en Views/Manager.
if (esManager()) {
    $paginaActual = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $carpetaActual = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($carpetaActual === 'Admin') {
        $paginaManager = $paginaActual === 'admin.php' ? 'manager.php' : $paginaActual;

        if (is_file(__DIR__ . "/../Views/Manager/" . $paginaManager)) {
            require_once __DIR__ . "/config.php";
            $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
            header("Location: " . BASE_URL . "/Views/Manager/" . $paginaManager . $query);
            exit();
        }
    }
}

// Cuenta desactivada mientras la sesión estaba abierta: se cierra la sesión
require_once __DIR__ . "/verificar_activo.php";
?>
