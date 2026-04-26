<?php
include("../includes/conexion.php");
header('Content-Type: application/json');

$fecha = $_POST['fecha'];
$horaInicio = $_POST['horaInicio'];
$horaFin = $_POST['horaFin'];
$IDLab = $_POST['IDLab'];
$IDDocentes = $_POST['IDDocentes'];

// 🔥 VALIDACIÓN DE HORARIO
$check = $conn->prepare("
SELECT * FROM reservaciones 
WHERE IDLab = ? 
AND fecha = ? 
AND (horaInicio < ? AND horaFin > ?)
");

$check->bind_param("isss", $IDLab, $fecha, $horaFin, $horaInicio);
$check->execute();
$result = $check->get_result();

if($result->num_rows > 0){
    echo json_encode([
        "status"=>"error",
        "error"=>"Horario ocupado"
    ]);
    exit;
}

// INSERTAR
$stmt = $conn->prepare("
INSERT INTO reservaciones 
(fecha, horaInicio, horaFin, IDLab, IDDocentes)
VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("sssii", $fecha, $horaInicio, $horaFin, $IDLab, $IDDocentes);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}