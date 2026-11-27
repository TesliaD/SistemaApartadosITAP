<?php
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/permisos.php";

$esInvitado = ($_SESSION['rol'] ?? '') === 'invitado';
if (!$esInvitado && !puedeGestionar()) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!is_array($data)) {
    echo json_encode(["error" => "Los datos de la reservación no son válidos"]);
    exit;
}

$fecha = $data['fecha'] ?? '';
$horas = $data['horas'] ?? [];
$idLab = (int)($data['IDLab'] ?? 0);
$software = trim((string)($data['software'] ?? ''));
$practica = trim((string)($data['Practica'] ?? ''));

if (empty($fecha) || empty($horas) || $idLab <= 0) {
    echo json_encode(["error" => "Selecciona fecha, laboratorio y horario"]);
    exit;
}

if ($esInvitado) {
    // El invitado reserva solo a nombre de su propia cuenta.
    $idUsuario = (int)$_SESSION['id'];
    $idGrupo = null;
} else {
    if (empty($data['IDUsuario']) || empty($data['IDGrupo'])) {
        echo json_encode(["error" => "Faltan el docente o el grupo"]);
        exit;
    }
    $idUsuario = (int)$data['IDUsuario'];
    $idGrupo = (int)$data['IDGrupo'];
    if (!puedeGestionarLab($conn, $idLab)) {
        echo json_encode(["error" => "Solo puedes apartar laboratorios de tu departamento"]);
        exit;
    }
}

$stmtLab = $conn->prepare("SELECT IDLab FROM laboratorios WHERE IDLab = ? AND activo = 1");
$stmtLab->bind_param("i", $idLab);
$stmtLab->execute();
$labValido = $stmtLab->get_result()->num_rows > 0;
$stmtLab->close();
if (!$labValido) {
    echo json_encode(["error" => "El laboratorio no está disponible"]);
    exit;
}

require_once __DIR__ . "/../includes/reglas_reservacion.php";
$rango = rangoDesdeHoras($horas);
if (isset($rango['error'])) {
    echo json_encode(["error" => $rango['error']]);
    exit;
}
$horaInicio = $rango['inicio'];
$horaFin = $rango['fin'];

$errorFecha = validarFechaReservacion($fecha, $horaInicio);
if ($errorFecha) {
    echo json_encode(["error" => $errorFecha]);
    exit;
}

if (hayEmpalme($conn, $fecha, $idLab, $horaInicio, $horaFin)) {
    echo json_encode(["error" => "El laboratorio ya está reservado en ese horario"]);
    exit;
}

if ($esInvitado) {
    $sql = "INSERT INTO reservaciones (fecha, horaInicio, horaFin, IDLab, IDGrupo, Software, Practica, Estado, IDUsuario)
            VALUES (?, ?, ?, ?, NULL, ?, ?, 'activa', ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "No se pudo preparar la reservación"]);
        exit;
    }
    $stmt->bind_param("sssissi", $fecha, $horaInicio, $horaFin, $idLab, $software, $practica, $idUsuario);
} else {
    $sql = "INSERT INTO reservaciones (fecha, horaInicio, horaFin, IDLab, IDUsuario, IDGrupo, Software, Practica, Estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activa')";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "No se pudo preparar la reservación"]);
        exit;
    }
    $stmt->bind_param("sssiiiss", $fecha, $horaInicio, $horaFin, $idLab, $idUsuario, $idGrupo, $software, $practica);
}

if ($stmt->execute()) {
    echo json_encode(["mensaje" => "Reservación guardada correctamente"]);
} else {
    echo json_encode(["error" => "No se pudo guardar la reservación"]);
}
$stmt->close();
$conn->close();
?>