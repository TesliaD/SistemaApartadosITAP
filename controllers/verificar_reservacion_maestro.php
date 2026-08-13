<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'maestro'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$grupoId = (int)$_GET['grupo'];
$fecha = $_GET['fecha'];
$hora = $_GET['hora'];

if(!$grupoId || !$fecha || !$hora){
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

// Buscar reservación
$sql = "SELECT 
            r.IDReservacion,
            r.Practica,
            r.Software,
            l.Nombre AS laboratorio,
            l.Descripcion AS labDescripcion
        FROM reservaciones r
        LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
        WHERE r.IDGrupo = ? 
        AND r.fecha = ? 
        AND r.horaInicio = ?
        AND r.Estado != 'cancelada'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $grupoId, $fecha, $hora);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "existe" => true,
        "IDReservacion" => $row['IDReservacion'],
        "laboratorio" => $row['laboratorio'] ?? 'N/A',
        "practica" => $row['Practica'] ?? '',
        "software" => $row['Software'] ?? ''
    ]);
} else {
    // Obtener nombre del grupo para el mensaje
    $sqlGrupo = "SELECT Nombre FROM grupos WHERE IDGrupo = ?";
    $stmtGrupo = $conn->prepare($sqlGrupo);
    $stmtGrupo->bind_param("i", $grupoId);
    $stmtGrupo->execute();
    $resultGrupo = $stmtGrupo->get_result();
    $grupo = $resultGrupo->fetch_assoc();
    
    echo json_encode([
        "existe" => false,
        "grupo" => $grupo['Nombre'] ?? 'el grupo'
    ]);
}
?>