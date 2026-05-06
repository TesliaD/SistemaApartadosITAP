<?php
include("../includes/conexion.php");

$id = $_GET['id'];

$sql = "SELECT cantidadAlumnos FROM grupos WHERE IDGrupo = $id";
$res = $conn->query($sql);

$row = $res->fetch_assoc();

echo json_encode([
    "cantidad" => $row['cantidadAlumnos']
]);