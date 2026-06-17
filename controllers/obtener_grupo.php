<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$id = (int)$_GET['id'];
$idUsuario = $_SESSION['id'];

$sql = "SELECT IDGrupo, IDCarrera, Semestre, cantidadAlumnos, Nombre, tipoGrupo 
        FROM grupos 
        WHERE IDGrupo = ? AND IDUsuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Grupo no encontrado"]);
}
?>