<?php
ob_clean();
session_start();

header('Content-Type: application/json');

require_once __DIR__ . "/../includes/permisos.php";
if (!puedeGestionar()){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once __DIR__ . "/../includes/conexion.php";

if(!$conn){
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit;
}

// Manager: solo su departamento
$sql = "SELECT IDDepartamentos, nombre FROM departamentos
        WHERE activo = 1 AND " . filtroDeptoSQL($conn, "IDDepartamentos") . "
        ORDER BY nombre";
$result = $conn->query($sql);

$departamentos = [];
while($row = $result->fetch_assoc()){
    $departamentos[] = $row;
}

echo json_encode($departamentos);
?>