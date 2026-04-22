<?php
session_start();
include("conexion.php");

$num_control = trim($_POST['num_control']);
$password = trim($_POST['password']);

$sql = "SELECT * FROM usuarios WHERE num_control = '$num_control' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    // 🔐 Guardar datos en sesión (NORMALIZADOS)
    $_SESSION['usuario'] = $user['nombre'];
    $_SESSION['rol'] = strtolower(trim($user['rol']));
    $_SESSION['num_control'] = $user['num_control'];

    echo "ok";

} else {
    echo "error";
}
?>