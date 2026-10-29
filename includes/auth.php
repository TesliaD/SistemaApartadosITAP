<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    require_once __DIR__ . "/config.php";
    header("Location: " . BASE_URL . "/index.html");
    exit();
}

// Ejemplo: solo admins
if ($_SESSION['rol'] !== 'administrador') {
    echo "Acceso denegado";
    exit();
}

?>

