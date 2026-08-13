<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$idUsuario = $_SESSION['id'];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtros adicionales desde el JS
$inicio = isset($_GET['inicio']) ? $_GET['inicio'] : '';
$fin = isset($_GET['fin']) ? $_GET['fin'] : '';
$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta SQL con filtros
// NOTA: Se eliminó 'r.Alumnos' porque no existe en tu tabla reservaciones
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
            g.Nombre AS grupo
        FROM reservaciones r
        INNER JOIN laboratorios l ON r.IDLab = l.IDLab
        INNER JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
        LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
        WHERE r.IDUsuario = ?";

$params = [$idUsuario];
$tipos = "i";

if (!empty($inicio) && !empty($fin)) {
    $sql .= " AND r.fecha BETWEEN ? AND ?";
    $params[] = $inicio;
    $params[] = $fin;
    $tipos .= "ss";
}

if (!empty($buscar)) {
    $sql .= " AND (l.Nombre LIKE ? OR g.Nombre LIKE ? OR r.Practica LIKE ?)";
    $like = "%$buscar%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $tipos .= "sss";
}

if (!empty($estado)) {
    $sql .= " AND r.Estado = ?";
    $params[] = $estado;
    $tipos .= "s";
}

$sql .= " ORDER BY r.fecha DESC, r.horaInicio DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$tipos .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Contar total para paginación (mismos filtros sin LIMIT ni OFFSET)
$sqlTotal = str_replace("LIMIT ? OFFSET ?", "", $sql);
$stmtTotal = $conn->prepare($sqlTotal);
// Quitamos los dos últimos parámetros (limit y offset) para el count
$paramsTotal = array_slice($params, 0, count($params) - 2);
$tiposTotal = substr($tipos, 0, -2);

$stmtTotal->bind_param($tiposTotal, ...$paramsTotal);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$total = $resultTotal->fetch_assoc()['total'] ?? 0;

echo json_encode(["data" => $data, "total" => $total]);
?>