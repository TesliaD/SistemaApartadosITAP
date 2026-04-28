<?php
include("../includes/conexion.php");

$id = intval($_GET['IDUsuarios']);

$stmt = $conn->prepare("DELETE FROM usuarios WHERE IDUsuarios = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../Views/Admin/VerUsuarios_Editar.php");
    exit;
} else {
    echo "Error al eliminar";
}
?>