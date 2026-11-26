<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/permisos.php';
header('Content-Type: application/json; charset=utf-8');

$esMaestro = ($_SESSION['rol'] ?? '') === 'maestro';
if (!$esMaestro && !puedeGestionar()) {
    http_response_code(403); echo json_encode(['error' => 'No autorizado']); exit;
}
$idUsuario = (int)($_SESSION['id'] ?? 0);
$accion = $_GET['accion'] ?? 'grupos';
$filtroDepto = $esMaestro ? '1=1' : filtroDeptoSQL($conn, 'l.IDDepartamento');
$filtroGrupos = $esMaestro ? 'g.IDUsuario = ' . $idUsuario : (esManager() ? "EXISTS (SELECT 1 FROM reservaciones r INNER JOIN laboratorios l ON l.IDLab = r.IDLab WHERE r.IDGrupo = g.IDGrupo AND $filtroDepto)" : '1=1');

if ($accion === 'grupos') {
    $sql = "SELECT g.IDGrupo, g.Nombre AS grupo, g.Semestre, g.Periodo, g.Anio, c.Nombre AS carrera, CONCAT(u.nombre, ' ', u.apellidos) AS maestro, COUNT(DISTINCT a.IDAlumnos) AS totalAlumnos FROM grupos g INNER JOIN usuarios u ON u.IDUsuarios = g.IDUsuario LEFT JOIN carreras c ON c.IDCarrera = g.IDCarrera LEFT JOIN alumnos a ON a.IDGrupo = g.IDGrupo WHERE $filtroGrupos GROUP BY g.IDGrupo, g.Nombre, g.Semestre, g.Periodo, g.Anio, c.Nombre, u.nombre, u.apellidos ORDER BY c.Nombre, g.Semestre, g.Nombre";
    $resultado = $conn->query($sql); $grupos = [];
    while ($row = $resultado->fetch_assoc()) $grupos[] = $row;
    echo json_encode(['grupos' => $grupos], JSON_UNESCAPED_UNICODE); exit;
}
if ($accion !== 'detalle') { http_response_code(400); echo json_encode(['error' => 'Acción no válida']); exit; }

$idGrupo = (int)($_GET['grupo'] ?? 0); $idReservacion = (int)($_GET['reservacion'] ?? 0);
if ($idGrupo <= 0) { http_response_code(400); echo json_encode(['error' => 'Selecciona un grupo válido']); exit; }

$sqlGrupo = "SELECT g.IDGrupo, g.IDUsuario, g.Nombre AS grupo, g.Semestre, g.Periodo, g.Anio, c.Nombre AS carrera, CONCAT(u.nombre, ' ', u.apellidos) AS maestro, COUNT(DISTINCT a.IDAlumnos) AS totalAlumnos FROM grupos g INNER JOIN usuarios u ON u.IDUsuarios = g.IDUsuario LEFT JOIN carreras c ON c.IDCarrera = g.IDCarrera LEFT JOIN alumnos a ON a.IDGrupo = g.IDGrupo WHERE g.IDGrupo = ? GROUP BY g.IDGrupo, g.IDUsuario, g.Nombre, g.Semestre, g.Periodo, g.Anio, c.Nombre, u.nombre, u.apellidos";
$stmtGrupo = $conn->prepare($sqlGrupo); $stmtGrupo->bind_param('i', $idGrupo); $stmtGrupo->execute(); $grupo = $stmtGrupo->get_result()->fetch_assoc(); $stmtGrupo->close();
if (!$grupo || ($esMaestro && (int)$grupo['IDUsuario'] !== $idUsuario)) { http_response_code(403); echo json_encode(['error' => 'No tienes permiso para consultar este grupo']); exit; }

$filtroPropia = $esMaestro ? 'AND r.IDUsuario = ' . $idUsuario : '';
$sqlSesiones = "SELECT r.IDReservacion, r.fecha, TIME_FORMAT(r.horaInicio, '%H:%i') AS horaInicio, TIME_FORMAT(r.horaFin, '%H:%i') AS horaFin, r.Practica, l.Nombre AS laboratorio FROM reservaciones r INNER JOIN laboratorios l ON l.IDLab = r.IDLab WHERE r.IDGrupo = ? AND LOWER(COALESCE(r.Estado, '')) NOT IN ('cancelada', 'cancelado') AND $filtroDepto $filtroPropia ORDER BY r.fecha DESC, r.horaInicio DESC";
$stmtSesiones = $conn->prepare($sqlSesiones); $stmtSesiones->bind_param('i', $idGrupo); $stmtSesiones->execute(); $resultadoSesiones = $stmtSesiones->get_result(); $sesiones = [];
while ($row = $resultadoSesiones->fetch_assoc()) $sesiones[] = $row;
$stmtSesiones->close();

if ($idReservacion > 0) {
    $sesionValida = false; foreach ($sesiones as $sesion) if ((int)$sesion['IDReservacion'] === $idReservacion) $sesionValida = true;
    if (!$sesionValida) { http_response_code(403); echo json_encode(['error' => 'No tienes permiso para consultar esta práctica']); exit; }
}

$porAlumno = [];
if ($idReservacion > 0) {
    $stmt = $conn->prepare("SELECT ap.IDAlumnos, ap.asistio, ap.observacion FROM asistencias_practicas ap INNER JOIN reservaciones r ON r.IDReservacion = ap.IDReservacion WHERE ap.IDReservacion = ? AND r.IDGrupo = ?");
    $stmt->bind_param('ii', $idReservacion, $idGrupo); $stmt->execute(); $rs = $stmt->get_result(); while ($row = $rs->fetch_assoc()) $porAlumno[(int)$row['IDAlumnos']] = $row; $stmt->close();
}
$stmtAlumnos = $conn->prepare('SELECT IDAlumnos, NoControl, nombre, plan FROM alumnos WHERE IDGrupo = ? ORDER BY nombre'); $stmtAlumnos->bind_param('i', $idGrupo); $stmtAlumnos->execute(); $rs = $stmtAlumnos->get_result(); $alumnos = [];
while ($row = $rs->fetch_assoc()) { $registro = $porAlumno[(int)$row['IDAlumnos']] ?? null; $row['asistio'] = $registro === null ? null : (int)$registro['asistio']; $row['observacion'] = $registro['observacion'] ?? ''; $alumnos[] = $row; } $stmtAlumnos->close();

$sqlHistorial = "SELECT r.IDReservacion, r.fecha, TIME_FORMAT(r.horaInicio, '%H:%i') AS horaInicio, TIME_FORMAT(r.horaFin, '%H:%i') AS horaFin, r.Practica, l.Nombre AS laboratorio, COUNT(ap.IDAsistencia) AS registrados, COALESCE(SUM(ap.asistio = 1), 0) AS presentes, COALESCE(SUM(ap.asistio = 0), 0) AS faltas FROM reservaciones r INNER JOIN laboratorios l ON l.IDLab = r.IDLab LEFT JOIN asistencias_practicas ap ON ap.IDReservacion = r.IDReservacion WHERE r.IDGrupo = ? AND $filtroDepto $filtroPropia GROUP BY r.IDReservacion, r.fecha, r.horaInicio, r.horaFin, r.Practica, l.Nombre ORDER BY r.fecha DESC, r.horaInicio DESC LIMIT 20";
$stmtHistorial = $conn->prepare($sqlHistorial); $stmtHistorial->bind_param('i', $idGrupo); $stmtHistorial->execute(); $rs = $stmtHistorial->get_result(); $historial = []; while ($row = $rs->fetch_assoc()) $historial[] = $row; $stmtHistorial->close();

echo json_encode(['grupo' => $grupo, 'sesiones' => $sesiones, 'alumnos' => $alumnos, 'historial' => $historial], JSON_UNESCAPED_UNICODE);