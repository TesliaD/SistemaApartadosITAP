<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.html");
    exit();
}

if ($_SESSION['rol'] !== 'invitado') {
    echo "Acceso denegado";
    exit();
}

// Cuenta desactivada mientras la sesión estaba abierta: se cierra la sesión
require_once __DIR__ . "/verificar_activo.php";
?>
