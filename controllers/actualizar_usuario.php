<?php

include("../includes/conexion.php");

header('Content-Type: application/json');

/* =========================
VALIDAR DATOS
========================= */

if(
    empty($_POST['id']) ||
    empty($_POST['nombre']) ||
    empty($_POST['email']) ||
    empty($_POST['rol'])
){

    echo json_encode([
        "status" => "error",
        "error"  => "Todos los campos son obligatorios."
    ]);

    exit;
}

$id     = intval($_POST['id']);
$nombre = trim($_POST['nombre']);
$email  = trim($_POST['email']);
$rol    = trim($_POST['rol']);

/* =========================
VALIDAR EMAIL
========================= */

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

    echo json_encode([
        "status" => "error",
        "error"  => "Correo electrónico inválido."
    ]);

    exit;
}

/* =========================
VALIDAR ROL
========================= */

$rolesValidos = [
    "administrador",
    "invitado",
    "maestro"
];

if(!in_array($rol, $rolesValidos)){

    echo json_encode([
        "status" => "error",
        "error"  => "Rol inválido."
    ]);

    exit;
}

/* =========================
ACTUALIZAR
========================= */

$sql = "
    UPDATE usuarios
    SET
        nombre = ?,
        email = ?,
        rol = ?
    WHERE IDUsuarios = ?
";

$stmt = $conn->prepare($sql);

if(!$stmt){

    echo json_encode([
        "status" => "error",
        "error"  => $conn->error
    ]);

    exit;
}

$stmt->bind_param(
    "sssi",
    $nombre,
    $email,
    $rol,
    $id
);

if($stmt->execute()){

    echo json_encode([
        "status" => "success"
    ]);

}else{

    echo json_encode([
        "status" => "error",
        "error"  => $stmt->error
    ]);

}
?>