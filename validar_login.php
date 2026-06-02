<?php 
session_start();
include("includes/conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validando...</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php

$num_control = $_POST['num_control'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE num_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $num_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    // VALIDAR PASSWORD
    if ($password == $user['password']) {

        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id'] = $user['IDUsuarios'];

        // REDIRECCIÓN SEGÚN ROL
        switch ($user['rol']) {

            case 'administrador':
                $ruta = "Views/Admin/admin.php";
                break;

            case 'maestro':
                $ruta = "Views/Maestro/maestro.php";
                break;

            default:
                $ruta = "Views/Invitado/invitado.php";
                break;
        }

        echo "
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Bienvenido',
                text: 'Inicio de sesión exitoso',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '$ruta';
            });
        </script>
        ";

    } else {

        echo "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Contraseña incorrecta'
            }).then(() => {
                window.location.href = 'index.html';
            });
        </script>
        ";
    }

} else {

    echo "
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Usuario no encontrado',
            text: 'Verifica tu número de control'
        }).then(() => {
            window.location.href = 'index.html';
        });
    </script>
    ";
}
?>

</body>
</html>