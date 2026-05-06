<?php
include("../includes/conexion.php");

header('Content-Type: application/json');

// ==========================
// PARAMETROS
// ==========================
$fechaInicio = $_GET['inicio'] ?? null;
$fechaFin    = $_GET['fin'] ?? null;
$busqueda    = $_GET['buscar'] ?? null;
$page        = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$limit = 10;
$offset = ($page - 1) * $limit;

// ==========================
// QUERY BASE
// ==========================
$sql = "
SELECT 
    r.IDReservacion,
    r.fecha,
    r.horaInicio,
    r.horaFin,
    r.Estado,

    l.Nombre AS laboratorio,
    l.numLab,

    d.Nombre AS docente,
    g.IDGrupo,
    g.Semestre,

    c.Nombre AS carrera,
    dep.nombre AS departamento,

    e.Practica,
    r.Software

FROM Reservaciones r
LEFT JOIN Laboratorios l ON r.IDLab = l.IDLab
LEFT JOIN Docentes d ON r.IDDocentes = d.IDDocentes
LEFT JOIN Grupos g ON r.IDGrupo = g.IDGrupo
LEFT JOIN Carreras c ON g.IDCarrera = c.IDCarrera
LEFT JOIN Departamentos dep ON c.IDDepartamento = dep.IDDepartamentos
LEFT JOIN Eventos e ON r.IDEvento = e.IDEvento
WHERE 1=1
";

$params = [];
$types = "";

// ==========================
// FILTRO RANGO FECHAS
// ==========================
if($fechaInicio && $fechaFin){
    $sql .= " AND r.fecha BETWEEN ? AND ?";
    $params[] = $fechaInicio;
    $params[] = $fechaFin;
    $types .= "ss";
}

// ==========================
// BUSQUEDA
// ==========================
if($busqueda){
    $sql .= " AND (
        l.Nombre LIKE ? OR 
        d.Nombre LIKE ?
    )";
    $search = "%$busqueda%";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

// ==========================
// PAGINACION
// ==========================
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// ==========================
// EJECUTAR
// ==========================
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$res = $stmt->get_result();

$data = [];
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

// ==========================
// TOTAL REGISTROS
// ==========================
$totalQuery = "SELECT COUNT(*) as total FROM Reservaciones";
$totalRes = $conn->query($totalQuery);
$total = $totalRes->fetch_assoc()['total'];

echo json_encode([
    "data" => $data,
    "total" => $total,
    "page" => $page
]);