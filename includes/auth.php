<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /login.php");
    exit();
}

// Ejemplo: solo admins
if ($_SESSION['rol'] !== 'administrador') {
    echo "Acceso denegado";
    exit();
}
?>