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

require_once __DIR__ . "/../includes/conexion.php";

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
// Panel de gestión: el manager solo aparta laboratorios de su departamento
require_once __DIR__ . "/../includes/permisos.php";
if(!puedeGestionar() || !puedeGestionarLab($conn, $idLab)){
    echo json_encode(["error" => "Solo puedes apartar laboratorios de tu departamento"]);
    exit;
}

$software = $data['software'] ?? '';
$practica = $data['Practica'] ?? '';

require_once __DIR__ . "/../includes/reglas_reservacion.php";

// Las horas llegan como bloques "19:00 - 20:00": se convierten en un rango inicio-fin
$rango = rangoDesdeHoras($horas);
if(isset($rango['error'])){
    echo json_encode(["error" => $rango['error']]);
    exit;
}
$horaInicio = $rango['inicio'];
$horaFin = $rango['fin'];

// Regla general: de hoy a 2 días; hoy solo horas que no han comenzado
$errorFecha = validarFechaReservacion($fecha, $horaInicio);
if($errorFecha){
    echo json_encode(["error" => $errorFecha]);
    exit;
}

// Verificar que el laboratorio no esté ocupado en ese horario
if(hayEmpalme($conn, $fecha, $idLab, $horaInicio, $horaFin)){
    echo json_encode(["error" => "El laboratorio ya está reservado en ese horario"]);
    exit;
}

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