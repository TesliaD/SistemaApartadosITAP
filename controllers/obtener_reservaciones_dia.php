<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

// ============================================================
// FECHA (YYYY-MM-DD)
// ============================================================

$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(["error" => "Fecha inválida"]);
    exit;
}

// ============================================================
// RESERVACIONES DEL DÍA (una fila por reservación)
// ============================================================

$sql = "SELECT
            r.IDReservacion,
            r.horaInicio,
            r.horaFin,
            r.Practica,
            r.Estado,
            l.IDLab,
            l.Nombre AS laboratorio,
            l.numLab,
            d.IDDepartamentos AS IDDepartamento,
            d.nombre AS departamento,
            CONCAT(u.nombre, ' ', u.apellidos) AS docente,
            g.Nombre AS grupo
        FROM reservaciones r
        INNER JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
        LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
        LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
        WHERE r.fecha = ?
          AND LOWER(COALESCE(r.Estado, '')) NOT IN ('cancelada', 'cancelado')
          AND l.activo = 1
        ORDER BY r.horaInicio, l.numLab";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Error al consultar reservaciones"]);
    exit;
}

$stmt->bind_param("s", $fecha);
$stmt->execute();
$result = $stmt->get_result();

$reservaciones = [];
while ($row = $result->fetch_assoc()) {
    $reservaciones[] = $row;
}

// ============================================================
// DEPARTAMENTOS (para el filtro)
// ============================================================

$departamentos = [];
$resultDeptos = $conn->query(
    "SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre"
);

if ($resultDeptos) {
    while ($row = $resultDeptos->fetch_assoc()) {
        $departamentos[] = $row;
    }
}

echo json_encode([
    "fecha" => $fecha,
    "departamentos" => $departamentos,
    "reservaciones" => $reservaciones
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
?>
