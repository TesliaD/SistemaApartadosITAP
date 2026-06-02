<?php 
include("../includes/conexion.php");

header('Content-Type: application/json');


// ==========================
// PARAMETROS
// ==========================
$fechaInicio = $_GET['inicio'] ?? null;
$fechaFin    = $_GET['fin'] ?? null;
$busqueda    = $_GET['buscar'] ?? null;

$page = isset($_GET['page']) 
    ? (int)$_GET['page'] 
    : 1;

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

    r.Practica,
    r.Software

FROM reservaciones r

LEFT JOIN laboratorios l 
    ON r.IDLab = l.IDLab

LEFT JOIN docentes d 
    ON r.IDDocentes = d.IDDocentes

LEFT JOIN grupos g 
    ON r.IDGrupo = g.IDGrupo

LEFT JOIN carreras c 
    ON g.IDCarrera = c.IDCarrera

LEFT JOIN departamentos dep 
    ON c.IDDepartamento = dep.IDDepartamentos

WHERE 1=1
";

$params = [];
$types = "";


// ==========================
// FILTRO FECHAS
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

    $sql .= "
    AND (
        l.Nombre LIKE ?
        OR d.Nombre LIKE ?
        OR r.Practica LIKE ?
    )
    ";

    $search = "%$busqueda%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "sss";
}


// ==========================
// ORDEN + PAGINACION
// ==========================
$sql .= "
ORDER BY r.fecha DESC, r.horaInicio ASC
LIMIT ? OFFSET ?
";

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
$totalQuery = "
SELECT COUNT(*) as total 
FROM reservaciones
";

$totalRes = $conn->query($totalQuery);

$total = $totalRes->fetch_assoc()['total'];


// ==========================
// RESPUESTA
// ==========================
echo json_encode([
    "data"  => $data,
    "total" => $total,
    "page"  => $page
]);
?>