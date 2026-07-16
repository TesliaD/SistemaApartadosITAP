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

$sql = "SELECT IDLab, Nombre FROM laboratorios WHERE activo = 1 ORDER BY Nombre";
$result = $conn->query($sql);

$laboratorios = [];
while($row = $result->fetch_assoc()){
    $laboratorios[] = $row;
}

echo json_encode($laboratorios);
?>