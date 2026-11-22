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
$software = $data['Software'] ?? $data['software'] ?? '';
$practica = $data['Practica'] ?? '';

$rangoHora = trim((string)$horas[0]);

if (strpos($rangoHora, '-') !== false) {
    [$horaInicio, $horaFin] = array_map('trim', explode('-', $rangoHora, 2));
} else {
    // Compatibilidad con solicitudes antiguas que enviaban solamente el inicio.
    $horaInicio = $rangoHora;
    $horaFin = date('H:i', strtotime($horaInicio) + 3600);
}

if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $horaInicio) ||
    !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $horaFin)) {
    echo json_encode(["error" => "El rango de horario no es válido"]);
    exit;
}

// Regla general: de hoy a 2 días; hoy solo horas que no han comenzado
require_once __DIR__ . "/../includes/reglas_reservacion.php";
$errorFecha = validarFechaReservacion($fecha, $horaInicio);
if($errorFecha) {
    echo json_encode(["error" => $errorFecha]);
    exit;
}


// Cualquier reservación no cancelada ocupa el horario ('activa', 'Activo', etc.)
if(hayEmpalme($conn, $fecha, $idLab, $horaInicio, $horaFin)) {
    echo json_encode(["error" => "Ya existe una reservación en este horario"]);
    exit;
}


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
