<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$idUsuario = $_SESSION['id'];

$sql = "SELECT 
            g.IDGrupo,
            g.IDCarrera,
            g.Semestre,
            g.cantidadAlumnos,
            g.Nombre,
            g.tipoGrupo,
            c.Nombre AS Carrera
        FROM grupos g
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        WHERE g.IDUsuario = $idUsuario
        ORDER BY g.Semestre, g.Nombre";

$result = $conn->query($sql);

if(!$result){
    echo json_encode(["error" => $conn->error]);
    exit;
}

$grupos = [];
while($row = $result->fetch_assoc()){
    $nombreGrupo = $row['Nombre'] ?: $row['Semestre'] . '° Semestre';
    $row['NombreCompleto'] = $row['Carrera'] . ' - ' . $nombreGrupo;
    $grupos[] = $row;
}

echo json_encode($grupos);
?>