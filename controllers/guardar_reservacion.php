<?php
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

if(!$conn){
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit;
}

$input = file_get_contents('php://input');
if(!$input){
    echo json_encode(["error" => "No se recibieron datos"]);
    exit;
}

$data = json_decode($input, true);
if(!$data){
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

if(empty($data['fecha']) || empty($data['horas']) || empty($data['IDLab']) || empty($data['IDUsuario']) || empty($data['IDGrupo'])){
    echo json_encode(["error" => "Faltan datos requeridos"]);
    exit;
}

$fecha = $data['fecha'];
$horas = $data['horas'];
$idLab = (int)$data['IDLab'];
$idUsuario = (int)$data['IDUsuario'];
$idGrupo = (int)$data['IDGrupo'];
$software = $data['software'] ?? '';
$practica = $data['Practica'] ?? '';

sort($horas);
$horaInicio = $horas[0];
$horaFin = end($horas);

// Verificar disponibilidad
$sqlCheck = "SELECT COUNT(*) as total FROM reservaciones 
             WHERE fecha = ? AND IDLab = ? AND Estado != 'cancelada'
             AND ((horaInicio <= ? AND horaFin > ?) OR (horaInicio < ? AND horaFin >= ?))";

$stmtCheck = $conn->prepare($sqlCheck);
if(!$stmtCheck){
    echo json_encode(["error" => "Error prepare check: " . $conn->error]);
    exit;
}

$stmtCheck->bind_param("sissii", $fecha, $idLab, $horaInicio, $horaInicio, $horaFin, $horaFin);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$rowCheck = $resultCheck->fetch_assoc();

if($rowCheck['total'] > 0){
    echo json_encode(["error" => "El laboratorio no está disponible en ese horario"]);
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();

// Guardar reservación - SIN la columna Alumnos
$sql = "INSERT INTO reservaciones (fecha, horaInicio, horaFin, IDLab, IDUsuario, IDGrupo, Software, Practica, Estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activa')";

$stmt = $conn->prepare($sql);
if(!$stmt){
    echo json_encode(["error" => "Error prepare insert: " . $conn->error]);
    exit;
}

$stmt->bind_param("sssiiiss", $fecha, $horaInicio, $horaFin, $idLab, $idUsuario, $idGrupo, $software, $practica);

if($stmt->execute()){
    echo json_encode(["mensaje" => "Reservación guardada correctamente"]);
} else {
    echo json_encode(["error" => "Error al guardar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>