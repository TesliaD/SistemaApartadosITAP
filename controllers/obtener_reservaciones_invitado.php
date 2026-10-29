<?php

ob_clean();

session_start();

require_once __DIR__ . "/../includes/conexion.php";

header('Content-Type: application/json');


// ============================================================
// VALIDAR SESIÓN
// ============================================================

if (!isset($_SESSION['id'])) {

    echo json_encode([
        "error" => "No autorizado"
    ]);

    exit;
}


// ============================================================
// PAGINACIÓN
// ============================================================

$page = (int)($_GET['page'] ?? 1);

if ($page < 1) {
    $page = 1;
}

$limit = 10;

$offset = ($page - 1) * $limit;


// ============================================================
// WHERE DINÁMICO
// ============================================================

$where = [];

$params = [];

$types = "";


// ============================================================
// FILTRO DE FECHA INICIO
// ============================================================

if (!empty($_GET['inicio'])) {

    $where[] = "r.fecha >= ?";

    $params[] = $_GET['inicio'];

    $types .= "s";
}


// ============================================================
// FILTRO DE FECHA FIN
// ============================================================

if (!empty($_GET['fin'])) {

    $where[] = "r.fecha <= ?";

    $params[] = $_GET['fin'];

    $types .= "s";
}


// ============================================================
// FILTRO DE BÚSQUEDA
// ============================================================

if (!empty($_GET['buscar'])) {

    $buscar = "%" . $_GET['buscar'] . "%";

    $where[] = "(
        u.nombre LIKE ?
        OR u.apellidos LIKE ?
        OR l.Nombre LIKE ?
        OR g.Nombre LIKE ?
        OR r.Practica LIKE ?
        OR r.Software LIKE ?
    )";

    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;
    $params[] = $buscar;

    $types .= "ssssss";
}


// ============================================================
// FILTRO DE ESTADO
// ============================================================

if (!empty($_GET['estado'])) {

    $where[] = "r.Estado = ?";

    $params[] = $_GET['estado'];

    $types .= "s";
}


// ============================================================
// FILTRO DE DEPARTAMENTO
// ============================================================

if (!empty($_GET['departamento'])) {

    $where[] = "u.IDDepartamento = ?";

    $params[] = (int)$_GET['departamento'];

    $types .= "i";
}


// ============================================================
// FILTRO DE LABORATORIO
// ============================================================

if (!empty($_GET['laboratorio'])) {

    $where[] = "r.IDLab = ?";

    $params[] = (int)$_GET['laboratorio'];

    $types .= "i";
}


// ============================================================
// CONSTRUIR WHERE
// ============================================================

$whereClause = "";

if (!empty($where)) {

    $whereClause = "WHERE " .
        implode(" AND ", $where);
}


// ============================================================
// CONSULTA PRINCIPAL
// ============================================================

$sql = "

    SELECT

        r.IDReservacion,

        r.fecha,

        r.horaInicio,

        r.horaFin,

        r.Practica,

        r.Software,

        r.Estado,

        l.Nombre AS laboratorio,

        CONCAT(
            u.nombre,
            ' ',
            u.apellidos
        ) AS docente,

        g.Nombre AS grupoNombre,

        g.Semestre,

        c.Nombre AS carrera,

        d.nombre AS departamento

    FROM reservaciones r

    LEFT JOIN laboratorios l
        ON r.IDLab = l.IDLab

    LEFT JOIN usuarios u
        ON r.IDUsuario = u.IDUsuarios

    LEFT JOIN grupos g
        ON r.IDGrupo = g.IDGrupo

    LEFT JOIN carreras c
        ON g.IDCarrera = c.IDCarrera

    LEFT JOIN departamentos d
        ON u.IDDepartamento = d.IDDepartamentos

    $whereClause

    ORDER BY
        r.fecha DESC,
        r.horaInicio ASC

    LIMIT ? OFFSET ?

";


// ============================================================
// PREPARAR CONSULTA
// ============================================================

$paramsConsulta = $params;

$typesConsulta = $types;

$paramsConsulta[] = $limit;

$paramsConsulta[] = $offset;

$typesConsulta .= "ii";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    echo json_encode([
        "error" => "Error preparando consulta: " .
                   $conn->error
    ]);

    exit;
}


$stmt->bind_param(
    $typesConsulta,
    ...$paramsConsulta
);

$stmt->execute();

$result = $stmt->get_result();


// ============================================================
// OBTENER RESERVACIONES
// ============================================================

$reservaciones = [];


while ($row = $result->fetch_assoc()) {

    $reservaciones[] = $row;
}


// ============================================================
// CONTAR TOTAL
// ============================================================

$sqlCount = "

    SELECT COUNT(*) AS total

    FROM reservaciones r

    LEFT JOIN laboratorios l
        ON r.IDLab = l.IDLab

    LEFT JOIN usuarios u
        ON r.IDUsuario = u.IDUsuarios

    LEFT JOIN grupos g
        ON r.IDGrupo = g.IDGrupo

    LEFT JOIN carreras c
        ON g.IDCarrera = c.IDCarrera

    LEFT JOIN departamentos d
        ON u.IDDepartamento = d.IDDepartamentos

    $whereClause

";


$stmtCount = $conn->prepare($sqlCount);


if (!$stmtCount) {

    echo json_encode([
        "error" => "Error preparando conteo: " .
                   $conn->error
    ]);

    exit;
}


// Los parámetros del COUNT son solamente
// los parámetros de los filtros.
// NO incluye LIMIT ni OFFSET.

if (!empty($params)) {

    $stmtCount->bind_param(
        $types,
        ...$params
    );
}


$stmtCount->execute();

$resultCount = $stmtCount->get_result();

$rowCount = $resultCount->fetch_assoc();

$total = $rowCount['total'] ?? 0;


// ============================================================
// RESPUESTA JSON
// ============================================================

echo json_encode([

    "data" => $reservaciones,

    "total" => (int)$total,

    "page" => $page,

    "totalPages" => ceil(
        $total / $limit
    )

]);

?>