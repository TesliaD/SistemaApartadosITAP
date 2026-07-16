<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

header('Content-Type: application/json');

// =========================
// VERIFICAR SESIÓN Y ROL
// =========================
if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador') {
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado"
    ]);
    exit;
}

// =========================
// VALIDAR MÉTODO
// =========================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ]);
    exit;
}

// =========================
// VALIDAR DATOS RECIBIDOS (CORREGIDO)
// =========================
if(!isset($_POST['nombre']) || !isset($_POST['numMaquinas']) || 
   !isset($_POST['descripcion']) || !isset($_POST['activo']) || 
   !isset($_POST['numLab']) || !isset($_POST['IDDepartamento'])) {
    
    echo json_encode([
        "status" => "error",
        "message" => "Datos incompletos"
    ]);
    exit;
}

// =========================
// OBTENER Y LIMPIAR DATOS (CORREGIDO)
// =========================
$nombre = trim($_POST['nombre']);
$numMaquinas = intval($_POST['numMaquinas']);
$descripcion = trim($_POST['descripcion']);
$activo = intval($_POST['activo']);
$numLab = trim($_POST['numLab']);
$idDepartamento = intval($_POST['IDDepartamento']);

// =========================
// VALIDACIONES
// =========================
if(empty($nombre)) {
    echo json_encode([
        "status" => "error",
        "message" => "El nombre del laboratorio es requerido"
    ]);
    exit;
}

if($numMaquinas < 1) {
    echo json_encode([
        "status" => "error",
        "message" => "El número de máquinas debe ser mayor a 0"
    ]);
    exit;
}

if(strlen($descripcion) < 5) {
    echo json_encode([
        "status" => "error",
        "message" => "La descripción debe tener mínimo 5 caracteres"
    ]);
    exit;
}

if(empty($numLab)) {
    echo json_encode([
        "status" => "error",
        "message" => "El número de laboratorio es requerido"
    ]);
    exit;
}

if($idDepartamento <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Selecciona un departamento válido"
    ]);
    exit;
}

// =========================
// VERIFICAR QUE EL DEPARTAMENTO EXISTA
// =========================
$sqlDepto = "SELECT IDDepartamentos FROM departamentos WHERE IDDepartamentos = ? AND activo = 1";
$stmtDepto = $conn->prepare($sqlDepto);
$stmtDepto->bind_param("i", $idDepartamento);
$stmtDepto->execute();
$resultDepto = $stmtDepto->get_result();

if($resultDepto->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "El departamento seleccionado no existe o está inactivo"
    ]);
    exit;
}

// =========================
// VERIFICAR QUE EL NÚMERO DE LABORATORIO NO ESTÉ DUPLICADO
// =========================
$sqlCheck = "SELECT IDLab FROM laboratorios WHERE numLab = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $numLab);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if($resultCheck->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "El número de laboratorio '$numLab' ya está registrado"
    ]);
    exit;
}

// =========================
// INSERTAR
// =========================
$sql = "INSERT INTO laboratorios 
        (Nombre, numMaquinas, Descripcion, activo, numLab, IDDepartamento) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if(!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al preparar la consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("sisssi", 
    $nombre, 
    $numMaquinas,
    $descripcion,  
    $activo,
    $numLab,
    $idDepartamento
);

if($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Laboratorio registrado correctamente"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Error al guardar: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>