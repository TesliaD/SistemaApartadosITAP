<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$where = [];
$params = [];
$types = "";

if(!empty($_GET['lab'])){
    $where[] = "r.IDLab = ?";
    $params[] = (int)$_GET['lab'];
    $types .= "i";
}

if(!empty($_GET['docente'])){
    $where[] = "r.IDUsuario = ?";
    $params[] = (int)$_GET['docente'];
    $types .= "i";
}

if(!empty($_GET['grupo'])){
    $where[] = "r.IDGrupo = ?";
    $params[] = (int)$_GET['grupo'];
    $types .= "i";
}

if(!empty($_GET['inicio'])){
    $where[] = "r.fecha >= ?";
    $params[] = $_GET['inicio'];
    $types .= "s";
}

if(!empty($_GET['fin'])){
    $where[] = "r.fecha <= ?";
    $params[] = $_GET['fin'];
    $types .= "s";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT 
            r.IDReservacion,
            r.fecha,
            r.horaInicio,
            r.horaFin,
            l.Nombre AS laboratorio
        FROM reservaciones r
        LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
        $whereClause
        ORDER BY r.fecha DESC, r.horaInicio DESC";

$stmt = $conn->prepare($sql);
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$reservaciones = [];
while($row = $result->fetch_assoc()){
    $reservaciones[] = $row;
}

echo json_encode($reservaciones);
?>