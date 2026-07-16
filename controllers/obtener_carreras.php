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

$sql = "SELECT IDCarrera, Nombre FROM carreras ORDER BY Nombre";
$result = $conn->query($sql);

$carreras = [];
while($row = $result->fetch_assoc()){
    $carreras[] = $row;
}

echo json_encode($carreras);
?>