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
    $rol = isset($_POST['rol']) ? $_POST['rol'] : 'usuario';
    $activo = $_POST['activo'];

    // VALIDACIONES

    if(empty($num_control) || empty($nombre) || empty($apellidos) || empty($area) || empty($email) || empty($passwordTexto)) {

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

    // ENCRIPTAR PASSWORD
    $password = password_hash($passwordTexto, PASSWORD_DEFAULT);

    // INSERTAR
    $sql = "INSERT INTO usuarios 
    (num_control, nombre, apellidos, area, email, password, rol, activo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // ERROR PREPARE
    if(!$stmt) {

        echo json_encode([
            "status" => "error",
            "message" => $conn->error
        ]);

        exit;
    }

    // BIND
    $stmt->bind_param(
        "sssssssi",
        $num_control,
        $nombre,
        $apellidos,
        $area,
        $email,
        $password,
        $rol,
        $activo
    );

    // EJECUTAR
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