<?php

session_start();

include("../includes/conexion.php");

header('Content-Type: application/json');

// MOSTRAR ERRORES PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {

    // VALIDAR METODO
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido"
        ]);

        exit;
    }

    // OBTENER DATOS
    $num_control = trim($_POST['num_control']);
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $area = trim($_POST['area']);
    $email = trim($_POST['email']);
    $passwordTexto = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $rol = isset($_POST['rol']) ? $_POST['rol'] : 'usuario';
    $activo = $_POST['activo'];
    $IDDepartamento = isset($_POST['IDDepartamento']) ? (int)$_POST['IDDepartamento'] : null; // NUEVO

    // VALIDACIONES

    if(empty($num_control) || empty($nombre) || empty($apellidos) || empty($area) || empty($email) || empty($passwordTexto) || empty($confirm_password)) {

        echo json_encode([
            "status" => "error",
            "message" => "Todos los campos son obligatorios"
        ]);

        exit;
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            "status" => "error",
            "message" => "Correo inválido"
        ]);

        exit;
    }

    if(strlen($passwordTexto) < 8) {

        echo json_encode([
            "status" => "error",
            "message" => "La contraseña debe tener mínimo 8 caracteres"
        ]);

        exit;
    }

    // VALIDACIÓN DE CONTRASEÑAS
    if($passwordTexto !== $confirm_password) {

        echo json_encode([
            "status" => "error",
            "message" => "Las contraseñas no coinciden"
        ]);

        exit;
    }

    // VALIDACIÓN DE DEPARTAMENTO (si el usuario es maestro, debe tener departamento)
    if($rol === 'maestro' && empty($IDDepartamento)) {
        echo json_encode([
            "status" => "error",
            "message" => "Los maestros deben tener un departamento asignado"
        ]);

        exit;
    }

    // ENCRIPTAR PASSWORD
    $password = password_hash($passwordTexto, PASSWORD_DEFAULT);

    // INSERTAR (con IDDepartamento)
    $sql = "INSERT INTO usuarios 
    (num_control, nombre, apellidos, area, email, password, rol, activo, IDDepartamento)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if(!$stmt) {

        echo json_encode([
            "status" => "error",
            "message" => $conn->error
        ]);

        exit;
    }

    // BIND (9 parámetros)
    $stmt->bind_param(
        "sssssssii", // 7 strings + 2 ints
        $num_control,
        $nombre,
        $apellidos,
        $area,
        $email,
        $password,
        $rol,
        $activo,
        $IDDepartamento
    );

    if($stmt->execute()) {

        echo json_encode([
            "status" => "success",
            "message" => "Usuario registrado correctamente"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}
?>