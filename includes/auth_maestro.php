<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.html");
    exit();
}

if ($_SESSION['rol'] !== 'maestro') {
    echo "Acceso denegado";
    exit();
}
?>