<?php
include("../includes/conexion.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$fecha = $data['fecha'];
$horas = $data['horas'];
$lab = $data['IDLab'];
$evento = $data['IDEvento'] ?? null;
$software = $data['Software'] ?? '';
$alumnos = $data['Alumnos'] ?? 0;
$docente = $data['IDDocentes'] ?? null;
$grupo = $data['IDGrupo'] ?? null;

foreach($horas as $hora){

    $inicio = $hora . ":00";
    $fin = date("H:i:s", strtotime($inicio . " +1 hour"));

    // VALIDAR CRUCE
    $check = $conn->prepare("
        SELECT 1 FROM reservaciones 
        WHERE IDLab=? AND fecha=? 
        AND (horaInicio < ? AND horaFin > ?)
    ");

    $check->bind_param("isss", $lab, $fecha, $fin, $inicio);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        continue;
    }

    $stmt = $conn->prepare("
    INSERT INTO reservaciones 
    (fecha, horaInicio, horaFin, IDLab, IDDocentes, IDGrupo, IDEvento, Software, Estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')
    ");

    $stmt->bind_param("sssiiiis", $fecha, $inicio, $fin, $lab, $docente, $grupo, $evento, $software);

    $stmt->execute();
}

echo json_encode(["mensaje" => "OK"]);