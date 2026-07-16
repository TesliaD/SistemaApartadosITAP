<?php
session_start();
include("../includes/conexion.php");

header('Content-Type: application/json');

// Verificar sesión
if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

// Obtener datos
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$area = trim($_POST['area'] ?? '');
$email = trim($_POST['email'] ?? '');
$rol = $_POST['rol'] ?? '';
$IDDepartamento = isset($_POST['IDDepartamento']) && !empty($_POST['IDDepartamento']) ? (int)$_POST['IDDepartamento'] : null;

// Validaciones
if(!$id){
    echo json_encode(["status" => "error", "message" => "ID de usuario requerido"]);
    exit;
}

if(empty($nombre) || empty($apellidos) || empty($area) || empty($email) || empty($rol)){
    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios"]);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode(["status" => "error", "message" => "Correo electrónico inválido"]);
    exit;
}

// Si es maestro, debe tener departamento
if($rol === 'maestro' && empty($IDDepartamento)){
    echo json_encode(["status" => "error", "message" => "Los maestros deben tener un departamento asignado"]);
    exit;
}

// Verificar si el usuario existe
$sqlCheck = "SELECT IDUsuarios FROM usuarios WHERE IDUsuarios = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if($resultCheck->num_rows === 0){
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

// Actualizar usuario
$sql = "UPDATE usuarios SET 
            nombre = ?, 
            apellidos = ?, 
            area = ?, 
            email = ?, 
            rol = ?, 
            IDDepartamento = ? 
        WHERE IDUsuarios = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssii", $nombre, $apellidos, $area, $email, $rol, $IDDepartamento, $id);

if($stmt->execute()){
    echo json_encode([
        "status" => "success", 
        "message" => "Usuario actualizado correctamente"
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Error al actualizar: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>