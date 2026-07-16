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

$sql = "SELECT 
            g.IDGrupo,
            g.Nombre,
            g.Semestre,
            c.Nombre AS Carrera
        FROM grupos g
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        ORDER BY c.Nombre, g.Semestre";
$result = $conn->query($sql);

$grupos = [];
while($row = $result->fetch_assoc()){
    $grupos[] = $row;
}

echo json_encode($grupos);
?>