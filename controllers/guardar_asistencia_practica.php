<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/permisos.php';
header('Content-Type: application/json; charset=utf-8');

$esMaestro = ($_SESSION['rol'] ?? '') === 'maestro';
if (!$esMaestro && !puedeGestionar()) { http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$idReservacion = (int)($data['reservacion'] ?? 0); $asistencias = $data['asistencias'] ?? []; $idUsuario = (int)$_SESSION['id'];
if ($idReservacion <= 0 || !is_array($asistencias) || count($asistencias) === 0) { http_response_code(400); echo json_encode(['error' => 'Selecciona una práctica y registra al menos un alumno']); exit; }

$stmtReserva = $conn->prepare('SELECT IDGrupo, IDUsuario FROM reservaciones WHERE IDReservacion = ?');
$stmtReserva->bind_param('i', $idReservacion); $stmtReserva->execute(); $reserva = $stmtReserva->get_result()->fetch_assoc(); $stmtReserva->close();
if (!$reserva) { http_response_code(404); echo json_encode(['error' => 'La práctica no existe']); exit; }
if (($esMaestro && (int)$reserva['IDUsuario'] !== $idUsuario) || (!$esMaestro && !puedeGestionarReservacion($conn, $idReservacion))) { http_response_code(403); echo json_encode(['error' => 'No tienes permiso para registrar esta asistencia']); exit; }

$idGrupo = (int)$reserva['IDGrupo'];
$stmtValida = $conn->prepare('SELECT IDAlumnos FROM alumnos WHERE IDAlumnos = ? AND IDGrupo = ?');
$stmtGuardar = $conn->prepare("INSERT INTO asistencias_practicas (IDReservacion, IDAlumnos, asistio, observacion, registradoPor) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE asistio = VALUES(asistio), observacion = VALUES(observacion), registradoPor = VALUES(registradoPor), fechaRegistro = CURRENT_TIMESTAMP");
if (!$stmtValida || !$stmtGuardar) { http_response_code(500); echo json_encode(['error' => 'No fue posible preparar el registro']); exit; }

$conn->begin_transaction();
try {
    foreach ($asistencias as $registro) {
        $idAlumno = (int)($registro['alumno'] ?? 0); $asistio = !empty($registro['asistio']) ? 1 : 0; $observacion = mb_substr(trim((string)($registro['observacion'] ?? '')), 0, 255);
        $stmtValida->bind_param('ii', $idAlumno, $idGrupo); $stmtValida->execute();
        if ($stmtValida->get_result()->num_rows === 0) throw new RuntimeException('Uno de los alumnos no pertenece al grupo seleccionado');
        $stmtGuardar->bind_param('iiisi', $idReservacion, $idAlumno, $asistio, $observacion, $idUsuario);
        if (!$stmtGuardar->execute()) throw new RuntimeException('No se pudo guardar la asistencia');
    }
    $conn->commit(); echo json_encode(['mensaje' => 'Asistencia guardada correctamente']);
} catch (Throwable $error) { $conn->rollback(); http_response_code(400); echo json_encode(['error' => $error->getMessage()]); }
$stmtValida->close(); $stmtGuardar->close();