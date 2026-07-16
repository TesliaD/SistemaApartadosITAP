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

$sql = "SELECT IDUsuarios, CONCAT(nombre, ' ', apellidos) AS Nombre 
        FROM usuarios 
        WHERE rol = 'maestro' AND activo = 1 
        ORDER BY nombre";
$result = $conn->query($sql);

$docentes = [];
while($row = $result->fetch_assoc()){
    $docentes[] = $row;
}

echo json_encode($docentes);
?>