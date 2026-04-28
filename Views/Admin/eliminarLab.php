<?php
include("../includes/conexion.php");

$id = $_GET['IDLab'];

$sql = "DELETE FROM laboratorios WHERE IDLab = $id";

if ($conn->query($sql)) {
    header("Location: VerLaboratorios_Editar.php");
} else {
    echo "Error al eliminar";
}
?>