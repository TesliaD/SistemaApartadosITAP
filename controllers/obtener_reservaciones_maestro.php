<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

include("../includes/conexion.php");

$idUsuario = $_SESSION['id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE dinámico
$where = "WHERE r.IDUsuario = $idUsuario";
$params = [];
$types = "";

// Filtro por fecha inicio
if(isset($_GET['inicio']) && !empty($_GET['inicio'])){
    $where .= " AND r.fecha >= ?";
    $params[] = $_GET['inicio'];
    $types .= "s";
}

// Filtro por fecha fin
if(isset($_GET['fin']) && !empty($_GET['fin'])){
    $where .= " AND r.fecha <= ?";
    $params[] = $_GET['fin'];
    $types .= "s";
}

// Filtro por estado
if(isset($_GET['estado']) && !empty($_GET['estado'])){
    $where .= " AND r.Estado = ?";
    $params[] = $_GET['estado'];
    $types .= "s";
}

// Filtro por búsqueda
if(isset($_GET['buscar']) && !empty($_GET['buscar'])){
    $buscar = "%" . $_GET['buscar'] . "%";
    $where .= " AND (u.nombre LIKE ? OR l.Nombre LIKE ? OR g.Nombre LIKE ?)";
    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;
    $types .= "sss";
}

// Consulta para contar total
$sqlCount = "SELECT COUNT(*) as total FROM reservaciones r 
             LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
             LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
             LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
             $where";

// Consulta para datos
$sql = "SELECT 
            r.IDReservacion,
            r.fecha,
            r.horaInicio,
            r.horaFin,
            CONCAT(r.horaInicio, ' - ', r.horaFin) AS horario,
            l.Nombre AS laboratorio,
            CONCAT(u.nombre, ' ', IFNULL(u.apellidos, '')) AS docente,
            CONCAT(g.Nombre, ' (', c.Nombre, ')') AS grupo,
            r.Practica,
            r.Software,
            r.Estado
        FROM reservaciones r
        LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
        $where
        ORDER BY r.fecha DESC, r.horaInicio ASC
        LIMIT $limit OFFSET $offset";

// Preparar y ejecutar consulta count
$stmtCount = $conn->prepare($sqlCount);
if(!empty($params)){
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$totalResult = $stmtCount->get_result();
$total = $totalResult->fetch_assoc()['total'] ?? 0;

// Preparar y ejecutar consulta datos
$stmt = $conn->prepare($sql);
if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "data" => $data,
    "total" => (int)$total,
    "page" => $page,
    "limit" => $limit
]);
?>¬