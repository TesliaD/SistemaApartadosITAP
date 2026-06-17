<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Construir WHERE dinámico
$where = [];
$params = [];
$types = "";

// Filtro de fechas
if(!empty($_GET['inicio'])) {
    $where[] = "r.fecha >= ?";
    $params[] = $_GET['inicio'];
    $types .= "s";
}
if(!empty($_GET['fin'])) {
    $where[] = "r.fecha <= ?";
    $params[] = $_GET['fin'];
    $types .= "s";
}

// Filtro de búsqueda
if(!empty($_GET['buscar'])) {
    $buscar = "%" . $_GET['buscar'] . "%";
    $where[] = "(u.nombre LIKE ? OR u.apellidos LIKE ? OR l.Nombre LIKE ?)";
    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;
    $types .= "sss";
}

// Filtro de estado
if(!empty($_GET['estado'])) {
    $where[] = "r.Estado = ?";
    $params[] = $_GET['estado'];
    $types .= "s";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Consulta principal
$sql = "SELECT 
            r.IDReservacion,
            r.fecha,
            r.horaInicio,
            r.horaFin,
            r.Practica,
            r.Software,
            r.Estado,
            l.Nombre AS laboratorio,
            CONCAT(u.nombre, ' ', u.apellidos) AS docente,
            g.Nombre AS grupoNombre,
            g.Semestre,
            c.Nombre AS carrera
        FROM reservaciones r
        LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
        LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        $whereClause
        ORDER BY r.fecha DESC, r.horaInicio DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$reservaciones = [];
while($row = $result->fetch_assoc()) {
    $reservaciones[] = $row;
}

// Contar total para paginación
$sqlCount = "SELECT COUNT(*) as total FROM reservaciones r $whereClause";
$stmtCount = $conn->prepare($sqlCount);
if(!empty($params)) {
    // Quitar los parámetros de LIMIT para el COUNT
    $countParams = array_slice($params, 0, count($params) - 2);
    $countTypes = substr($types, 0, -2);
    if(!empty($countParams)) {
        $stmtCount->bind_param($countTypes, ...$countParams);
    }
}
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total = $resultCount->fetch_assoc()['total'] ?? 0;

echo json_encode([
    "data" => $reservaciones,
    "total" => $total,
    "page" => $page,
    "totalPages" => ceil($total / $limit)
]);
?>