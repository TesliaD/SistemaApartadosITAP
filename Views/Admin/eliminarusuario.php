<?php
include("../../includes/conexion.php");

$id = $_GET['IDUsuarios'];

$sql = "DELETE FROM usuarios WHERE IDUsuarios = $id";

if ($conn->query($sql)) {
    header("Location: VerUsuarios_Editar.php");
} else {
    echo "Error al eliminar";
}
?>