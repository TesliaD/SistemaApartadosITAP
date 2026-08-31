<?php

ob_clean();
session_start();

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
// CONEXIÓN
// ============================================================

require_once $_SERVER['DOCUMENT_ROOT'] .
    "/SistemaApartadosITAP/includes/conexion.php";


if (!$conn) {

    echo json_encode([
        "error" => "Error de conexión a BD"
    ]);

    exit;
}


// ============================================================
// OBTENER DEPARTAMENTOS
// ============================================================

$sql = "
    SELECT
        IDDepartamentos,
        nombre
    FROM departamentos
    WHERE activo = 1
    ORDER BY nombre
";


$result = $conn->query($sql);


if (!$result) {

    echo json_encode([
        "error" => "Error al consultar departamentos"
    ]);

    exit;
}


$departamentos = [];


while ($row = $result->fetch_assoc()) {

    $departamentos[] = $row;
}


echo json_encode($departamentos);

?>