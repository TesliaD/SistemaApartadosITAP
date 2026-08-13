<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$idLab = (int)$data['idLab'];
$dia = $data['dia'];
$horas = $data['horas'];

if(empty($idLab) || empty($horas)){
    echo json_encode(["error" => "Faltan datos requeridos"]);
    exit;
}

// Primero, eliminar horarios existentes para este laboratorio y día (si no es 'todos')
if($dia !== 'todos') {
    $sqlDelete = "DELETE FROM horarios_laboratorio WHERE IDLab = ? AND dia = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("is", $idLab, $dia);
    $stmtDelete->execute();
} else {
    // Si es 'todos', eliminar todos los horarios de este laboratorio
    $sqlDelete = "DELETE FROM horarios_laboratorio WHERE IDLab = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $idLab);
    $stmtDelete->execute();
}

// Insertar nuevos horarios
$sqlInsert = "INSERT INTO horarios_laboratorio (IDLab, dia, hora, habilitado) VALUES (?, ?, ?, 1)";
$stmtInsert = $conn->prepare($sqlInsert);

$errores = 0;
foreach($horas as $hora) {
    $diaInsert = ($dia === 'todos') ? 'todos' : $dia;
    $stmtInsert->bind_param("iss", $idLab, $diaInsert, $hora);
    if(!$stmtInsert->execute()) {
        $errores++;
    }
}

if($errores === 0) {
    echo json_encode(["mensaje" => "Horarios guardados correctamente"]);
} else {
    echo json_encode(["error" => "Algunos horarios no se pudieron guardar"]);
}

$stmtInsert->close();
$conn->close();
?>