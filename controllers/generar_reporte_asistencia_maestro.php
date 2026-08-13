<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'maestro'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$grupoId = (int)$data['grupoId'];
$fecha = $data['fecha'];
$hora = $data['hora'];
$practica = $data['practica'] ?? '';
$software = $data['software'] ?? '';
$idUsuario = $_SESSION['id'];

// Obtener datos del grupo
$sqlGrupo = "SELECT 
                g.Nombre AS grupoNombre,
                g.Semestre,
                c.Nombre AS carrera,
                l.Nombre AS laboratorio,
                l.Descripcion AS labDescripcion,
                d.nombre AS departamento
            FROM grupos g
            LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
            LEFT JOIN laboratorios l ON l.IDLab = (
                SELECT IDLab FROM reservaciones 
                WHERE IDGrupo = g.IDGrupo AND fecha = ? 
                ORDER BY IDReservacion DESC LIMIT 1
            )
            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
            WHERE g.IDGrupo = ?";

$stmtGrupo = $conn->prepare($sqlGrupo);
$stmtGrupo->bind_param("si", $fecha, $grupoId);
$stmtGrupo->execute();
$resultGrupo = $stmtGrupo->get_result();
$grupo = $resultGrupo->fetch_assoc();

if(!$grupo){
    echo json_encode(["error" => "Grupo no encontrado"]);
    exit;
}

// Obtener alumnos del grupo
$sqlAlumnos = "SELECT NoControl, nombre FROM alumnos WHERE IDGrupo = ? ORDER BY nombre";
$stmtAlumnos = $conn->prepare($sqlAlumnos);
$stmtAlumnos->bind_param("i", $grupoId);
$stmtAlumnos->execute();
$resultAlumnos = $stmtAlumnos->get_result();

$alumnos = [];
while($row = $resultAlumnos->fetch_assoc()){
    $alumnos[] = $row;
}

// Obtener datos del maestro
$sqlMaestro = "SELECT nombre, apellidos FROM usuarios WHERE IDUsuarios = ?";
$stmtMaestro = $conn->prepare($sqlMaestro);
$stmtMaestro->bind_param("i", $idUsuario);
$stmtMaestro->execute();
$resultMaestro = $stmtMaestro->get_result();
$maestro = $resultMaestro->fetch_assoc();

$response = [
    "laboratorio" => $grupo['laboratorio'] ?? 'N/A',
    "carrera" => $grupo['carrera'] ?? 'N/A',
    "docente" => trim($maestro['nombre'] . ' ' . ($maestro['apellidos'] ?? '')),
    "software" => $software,
    "practica" => $practica,
    "grupo" => $grupo['grupoNombre'] ?? $grupo['Semestre'] . '° Semestre',
    "fecha" => $fecha,
    "hora" => $hora,
    "departamento" => $grupo['departamento'] ?? 'SISTEMAS',
    "alumnos" => $alumnos
];

echo json_encode($response);
?>