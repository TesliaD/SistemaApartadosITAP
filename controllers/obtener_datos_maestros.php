<?php
session_start();
include("../includes/conexion.php");

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$idUsuario = $_SESSION['id'];

// Verificar que el usuario sea maestro
$sqlUsuario = "SELECT IDUsuarios, nombre, apellidos, num_control, rol FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode(["error" => "Usuario no encontrado"]);
    exit;
}

$usuario = $result->fetch_assoc();

if($usuario['rol'] != 'maestro'){
    echo json_encode(["error" => "El usuario no tiene rol de maestro"]);
    exit;
}

// Obtener grupos asociados DIRECTAMENTE a este usuario
$sqlGrupos = "
SELECT 
    g.IDGrupo,
    c.Nombre AS Carrera,
    g.Semestre,
    g.cantidadAlumnos,
    g.Nombre AS NombreGrupo
FROM grupos g
INNER JOIN carreras c ON g.IDCarrera = c.IDCarrera
WHERE g.IDUsuario = ?
";

$stmt2 = $conn->prepare($sqlGrupos);
$stmt2->bind_param("i", $usuario['IDUsuarios']);
$stmt2->execute();
$resGrupos = $stmt2->get_result();

$grupos = [];
while($row = $resGrupos->fetch_assoc()){
    $grupos[] = $row;
}

echo json_encode([
    "docente" => [
        "IDUsuarios" => $usuario['IDUsuarios'],
        "Nombre" => $usuario['nombre'] . " " . ($usuario['apellidos'] ?? ''),
        "num_control" => $usuario['num_control']
    ],
    "grupos" => $grupos
]);
?>