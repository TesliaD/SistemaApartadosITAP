<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$idGrupo = $data['id'];
$idUsuario = $_SESSION['id'];

// Verificar que el grupo pertenece al maestro
$check = $conn->prepare("SELECT IDGrupo FROM grupos WHERE IDGrupo = ? AND IDUsuario = ?");
$check->bind_param("ii", $idGrupo, $idUsuario);
$check->execute();
if($check->get_result()->num_rows == 0){
    echo json_encode(["error" => "No tienes permiso para eliminar este grupo"]);
    exit;
}
$check->close();

// Eliminar el grupo (los alumnos se eliminan automáticamente por CASCADE)
$sql = "DELETE FROM grupos WHERE IDGrupo = ? AND IDUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idGrupo, $idUsuario);

if($stmt->execute()) {
    echo json_encode(["mensaje" => "Grupo y sus alumnos eliminados correctamente"]);
} else {
    echo json_encode(["error" => "Error al eliminar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>