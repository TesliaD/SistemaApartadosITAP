<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

$idUsuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($idUsuario == 0){
    echo json_encode(["error" => "ID de usuario requerido"]);
    exit;
}

// Verificar que el usuario existe y es maestro
$checkUser = "SELECT IDUsuarios FROM usuarios WHERE IDUsuarios = $idUsuario AND rol = 'maestro'";
$resultUser = $conn->query($checkUser);
if($resultUser->num_rows == 0){
    echo json_encode(["error" => "El usuario no es un maestro válido"]);
    exit;
}

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
        ORDER BY g.Semestre";

$result = $conn->query($sql);

if(!$result) {
    echo json_encode(["error" => $conn->error]);
    exit;
}

$grupos = [];
while($row = $result->fetch_assoc()) {
    if(empty($row['Nombre'])) {
        $row['Nombre'] = $row['Semestre'] . '° Semestre';
    }
    if(empty($row['tipoGrupo'])) {
        $row['tipoGrupo'] = 'regular';
    }
    $grupos[] = $row;
}

echo json_encode($grupos);
?>