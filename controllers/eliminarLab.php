<?php
session_start();
include("../includes/conexion.php");
require_once __DIR__ . "/../includes/permisos.php";

if (!puedeGestionar()) {
    echo "No autorizado";
    exit;
}

$id = (int)($_GET['IDLab'] ?? 0);

// Manager: solo laboratorios de su departamento
if (!$id || !puedeGestionarLab($conn, $id)) {
    echo "Solo puedes eliminar laboratorios de tu departamento";
    exit;
}

$stmt = $conn->prepare("DELETE FROM laboratorios WHERE IDLab = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../Views/Admin/VerLaboratorios_Editar.php");
} else {
    echo "Error al eliminar";
}
?>
