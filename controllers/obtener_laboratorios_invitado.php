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
// DEPARTAMENTO OPCIONAL
// ============================================================

$departamento = isset($_GET['departamento'])
    ? (int)$_GET['departamento']
    : 0;


// ============================================================
// CONSULTA
// ============================================================

$sql = "
    SELECT
        IDLab,
        Nombre
    FROM laboratorios
    WHERE activo = 1
";


// ============================================================
// FILTRAR POR DEPARTAMENTO
// ============================================================

if ($departamento > 0) {

    $sql .= "
        AND IDDepartamento = ?
    ";
}


$sql .= "
    ORDER BY Nombre
";


// ============================================================
// EJECUTAR
// ============================================================

if ($departamento > 0) {

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $departamento
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query($sql);
}


// ============================================================
// VALIDAR RESULTADO
// ============================================================

if (!$result) {

    echo json_encode([
        "error" => "Error al consultar laboratorios"
    ]);

    exit;
}


// ============================================================
// CREAR JSON
// ============================================================

$laboratorios = [];


while ($row = $result->fetch_assoc()) {

    $laboratorios[] = $row;
}


echo json_encode($laboratorios);

?>