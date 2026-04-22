<?php
session_start();
include("includes/conexion.php");

$num_control = $_POST['num_control'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE num_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $num_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    // si usas password normal
    if ($password == $user['password']) {

        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id'] = $user['IDUsuarios'];

        // REDIRECCIÓN POR ROL
        switch ($user['rol']) {
            case 'administrador':
                header("Location: Views/Admin/admin.php");
                break;
            case 'maestro': 
                header("Location: Views/Maestro/maestro.php");
                break;
            default:
                header("Location: Views/Invitado/invitado.php");
                break;
        }

    } else {
        echo "Contraseña incorrecta";
    }

} else {
    echo "Usuario no encontrado";
}
?>