<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$idGrupo = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($idGrupo == 0){
    echo json_encode(["error" => "ID de grupo no válido"]);
    exit;
}

$idUsuario = $_SESSION['id'];

// Verificar que el grupo pertenece al maestro
$verificar = $conn->query("SELECT IDGrupo FROM grupos WHERE IDGrupo = $idGrupo AND IDUsuario = $idUsuario");

if($verificar->num_rows == 0){
    echo json_encode(["error" => "No tienes permiso para ver este grupo"]);
    exit;
}

// Obtener alumnos del grupo
$sql = "SELECT IDAlumnos, NoControl, nombre, plan 
        FROM alumnos 
        WHERE IDGrupo = $idGrupo 
        ORDER BY nombre";

$result = $conn->query($sql);

if(!$result){
    echo json_encode(["error" => $conn->error]);
    exit;
}

$alumnos = [];
while($row = $result->fetch_assoc()){
    $alumnos[] = $row;
}

echo json_encode($alumnos);
?>