<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    require_once __DIR__ . "/config.php";
    header("Location: " . BASE_URL . "/index.html");
    exit();
}

// solo admins
if ($_SESSION['rol'] !== 'invitado') {
    echo "Acceso denegado";
    exit();
}


?>