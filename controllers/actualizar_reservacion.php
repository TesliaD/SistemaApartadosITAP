<?php
include("../includes/conexion.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$inicio = $data['inicio'];
$fin = $data['fin'];
$fecha = $data['fecha'];

// VALIDAR CRUCE
$check = $conn->prepare("
SELECT * FROM reservaciones 
WHERE IDReservacion != ?
AND fecha = ?
AND (horaInicio < ? AND horaFin > ?)
");

$check->bind_param("isss", $id, $fecha, $fin, $inicio);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    echo json_encode(["status"=>"error","error"=>"Horario ocupado"]);
    exit;
}

// UPDATE
$stmt = $conn->prepare("
UPDATE reservaciones 
SET horaInicio=?, horaFin=? 
WHERE IDReservacion=?
");

$stmt->bind_param("ssi", $inicio, $fin, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}