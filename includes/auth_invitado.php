<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /login.php");
    exit();
}

// solo admins
if ($_SESSION['rol'] !== 'invitado') {
    echo "Acceso denegado";
    exit();
}


?>