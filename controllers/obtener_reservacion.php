<?php
include("../includes/conexion.php");

$sql = "SELECT r.*, l.Nombre AS laboratorio 
        FROM reservaciones r
        JOIN laboratorios l ON r.IDLab = l.IDLab";

$result = $conn->query($sql);

$eventos = [];

while($row = $result->fetch_assoc()){
    $eventos[] = [
        "title" => $row['laboratorio'],
        "start" => $row['fecha']."T".$row['horaInicio'],
        "end" => $row['fecha']."T".$row['horaFin']
    ];
}

echo json_encode($eventos);