<?php
ob_clean();
include("../includes/conexion.php");

header('Content-Type: application/json');

$idGrupo = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($idGrupo == 0){
    echo json_encode(["error" => "ID de grupo requerido"]);
    exit;
}

// Obtener la cantidad de alumnos del grupo
$sql = "SELECT cantidadAlumnos FROM grupos WHERE IDGrupo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idGrupo);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode(["cantidad" => $row['cantidadAlumnos'] ?? 0]);
} else {
    echo json_encode(["cantidad" => 0]);
}
?>