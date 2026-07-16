<?php
ob_clean();
session_start();

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

if(!$conn){
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit;
}

$sql = "SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre";
$result = $conn->query($sql);

$departamentos = [];
while($row = $result->fetch_assoc()){
    $departamentos[] = $row;
}

echo json_encode($departamentos);
?>