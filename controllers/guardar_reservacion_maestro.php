<?php
session_start();
include("../includes/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['id'])) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Validar datos requeridos
if(!isset($data['fecha']) || !isset($data['horas']) || !isset($data['IDLab']) || !isset($data['IDGrupo'])) {
    echo json_encode(["error" => "Faltan datos requeridos"]);
    exit;
}

$idUsuario = $_SESSION['id'];
$fecha = $data['fecha'];
$horas = $data['horas'];
$idLab = $data['IDLab'];
$idGrupo = $data['IDGrupo'];
$software = $data['Software'] ?? '';
$practica = $data['Practica'] ?? '';


sort($horas);
$horaInicio = $horas[0];
$horaFin = $horas[count($horas) - 1];

$horaFin = date('H:i', strtotime($horaFin) + 3600);


$sqlCheck = "SELECT COUNT(*) as total FROM reservaciones 
             WHERE fecha = ? AND IDLab = ? AND Estado = 'activa'
             AND (
                (horaInicio <= ? AND horaFin > ?) OR
                (horaInicio < ? AND horaFin >= ?) OR
                (horaInicio >= ? AND horaFin <= ?)
             )";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("sissssss", $fecha, $idLab, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$rowCheck = $resultCheck->fetch_assoc();

if($rowCheck['total'] > 0) {
    echo json_encode(["error" => "Ya existe una reservación en este horario"]);
    exit;
}
$stmtCheck->close();


$sql = "INSERT INTO reservaciones (fecha, horaInicio, horaFin, IDLab, IDUsuario, IDGrupo, Software, Practica, Estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activa')";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiiiss", $fecha, $horaInicio, $horaFin, $idLab, $idUsuario, $idGrupo, $software, $practica);

if($stmt->execute()) {
    echo json_encode(["mensaje" => "Reservación guardada correctamente"]);
} else {
    echo json_encode(["error" => "Error al guardar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>