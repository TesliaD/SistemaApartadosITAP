<?php
// ============================================================
// ENTRADA DE LAS VISTAS DEL MANAGER (Views/Manager/*.php)
//
// Cada archivo de Views/Manager define $vistaAdmin y carga este archivo.
// Se muestra la misma pantalla del administrador, que ya limita los
// datos al departamento del manager (ver includes/permisos.php).
// Así no hay código duplicado: un cambio en la pantalla aplica a ambos.
// ============================================================

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/permisos.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: " . BASE_URL . "/index.html");
    exit();
}

// Solo managers. El administrador usa su propia carpeta Views/Admin.
if (!esManager()) {
    if (esAdmin()) {
        header("Location: " . BASE_URL . "/Views/Admin/" . $vistaAdmin);
        exit();
    }

    echo "Acceso denegado";
    exit();
}

require __DIR__ . "/../Views/Admin/" . $vistaAdmin;
