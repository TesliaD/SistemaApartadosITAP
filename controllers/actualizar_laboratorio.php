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
// OBTENER DATOS
// =========================
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$numMaquinas = isset($_POST['num_maquinas']) ? (int)$_POST['num_maquinas'] : 0;
$descripcion = trim($_POST['descripcion'] ?? '');
$numLab = trim($_POST['num_lab'] ?? '');
$idDepartamento = isset($_POST['id_departamento']) ? (int)$_POST['id_departamento'] : 0;
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

// =========================
// VALIDACIONES
// =========================
if(!$id) {
    echo json_encode([
        "status" => "error",
        "message" => "ID de laboratorio requerido"
    ]);
    exit;
}

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
// VERIFICAR QUE EL LABORATORIO EXISTA
// =========================
$sqlCheck = "SELECT IDLab FROM laboratorios WHERE IDLab = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if($resultCheck->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "El laboratorio no existe"
    ]);
    exit;
}

// =========================
// VERIFICAR QUE EL NÚMERO DE LABORATORIO NO ESTÉ DUPLICADO (excepto el mismo)
// =========================
$sqlDuplicado = "SELECT IDLab FROM laboratorios WHERE numLab = ? AND IDLab != ?";
$stmtDuplicado = $conn->prepare($sqlDuplicado);
$stmtDuplicado->bind_param("si", $numLab, $id);
$stmtDuplicado->execute();
$resultDuplicado = $stmtDuplicado->get_result();

if($resultDuplicado->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "El número de laboratorio '$numLab' ya está registrado en otro laboratorio"
    ]);
    exit;
}

// =========================
// ACTUALIZAR
// =========================
$sql = "UPDATE laboratorios SET 
            Nombre = ?, 
            numMaquinas = ?, 
            Descripcion = ?, 
            numLab = ?, 
            IDDepartamento = ?, 
            activo = ? 
        WHERE IDLab = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssii", 
    $nombre, 
    $numMaquinas, 
    $descripcion, 
    $numLab, 
    $idDepartamento, 
    $activo, 
    $id
);

if($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Laboratorio actualizado correctamente"
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