<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$idReservacion = (int)$_GET['id'];

if(!$idReservacion){
    echo json_encode(["error" => "ID de reservación requerido"]);
    exit;
}

// Obtener datos de la reservación
$sql = "SELECT 
            r.fecha,
            r.horaInicio,
            r.horaFin,
            r.Practica,
            r.Software,
            r.Estado,
            l.Nombre AS laboratorio,
            l.IDLab,
            CONCAT(u.nombre, ' ', u.apellidos) AS docente,
            u.IDUsuarios AS idDocente,
            g.IDGrupo,
            g.Semestre,
            g.Nombre AS grupoNombre,
            g.cantidadAlumnos,
            c.Nombre AS carrera,
            c.IDCarrera
        FROM reservaciones r
        LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
        LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        WHERE r.IDReservacion = $idReservacion";

$result = $conn->query($sql);

if(!$result || $result->num_rows === 0){
    echo json_encode(["error" => "Reservación no encontrada"]);
    exit;
}

$reserva = $result->fetch_assoc();

// Obtener alumnos del grupo
$sqlAlumnos = "SELECT NoControl, nombre FROM alumnos WHERE IDGrupo = " . (int)$reserva['IDGrupo'] . " ORDER BY nombre";
$resultAlumnos = $conn->query($sqlAlumnos);

$alumnos = [];
while($row = $resultAlumnos->fetch_assoc()){
    $alumnos[] = $row;
}

// Construir respuesta
$response = [
    "laboratorio" => $reserva['laboratorio'] ?? 'N/A',
    "carrera" => $reserva['carrera'] ?? 'N/A',
    "docente" => $reserva['docente'] ?? 'N/A',
    "materia" => $reserva['Software'] ?? 'N/A',
    "practica" => $reserva['Practica'] ?? 'N/A',
    "grupo" => $reserva['grupoNombre'] ?? $reserva['Semestre'] . '° Semestre',
    "fecha" => $reserva['fecha'] ?? 'N/A',
    "hora" => ($reserva['horaInicio'] ?? '') . ' - ' . ($reserva['horaFin'] ?? ''),
    "departamento" => "SISTEMAS",
    "alumnos" => $alumnos
];

echo json_encode($response);
?>