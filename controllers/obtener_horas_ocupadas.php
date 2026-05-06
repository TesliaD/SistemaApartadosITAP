<?php
include("../includes/conexion.php");

header('Content-Type: application/json');

$fecha = $_GET['fecha'];
$lab   = $_GET['lab'];

$stmt = $conn->prepare("
    SELECT horaInicio FROM reservaciones 
    WHERE fecha=? AND IDLab=?
");

$stmt->bind_param("si", $fecha, $lab);
$stmt->execute();

$res = $stmt->get_result();

$horas = [];

while($row = $res->fetch_assoc()){
    $horas[] = substr($row['horaInicio'], 0, 5);
}

echo json_encode($horas);
