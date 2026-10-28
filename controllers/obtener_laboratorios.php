<?php
ob_clean();
session_start();

header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/permisos.php";
if (!puedeGestionar()){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

if(!$conn){
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit;
}

// Manager: solo laboratorios de su departamento
$sql = "SELECT IDLab, Nombre FROM laboratorios
        WHERE activo = 1 AND " . filtroDeptoSQL($conn, "IDDepartamento") . "
        ORDER BY Nombre";
$result = $conn->query($sql);

$laboratorios = [];
while($row = $result->fetch_assoc()){
    $laboratorios[] = $row;
}

echo json_encode($laboratorios);
?>