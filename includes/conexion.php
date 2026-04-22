<?php 
$host = "localhost";
$user = "root";
$pass = "";
$db = "itap_lab";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}
?>