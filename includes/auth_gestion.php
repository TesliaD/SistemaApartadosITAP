<?php
// Vistas compartidas entre administrador y manager
// (laboratorios, horarios, reservaciones y reportes).
// El manager solo ve y modifica datos de su departamento.
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/permisos.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: /SistemaApartadosITAP/index.html");
    exit();
}

if (!puedeGestionar()) {
    echo "Acceso denegado";
    exit();
}
?>
