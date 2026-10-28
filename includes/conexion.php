<?php
// Hora local de Agua Prieta, Sonora (sin horario de verano)
date_default_timezone_set('America/Hermosillo');

$host = "localhost";
$user = "root";
$pass = "";
$db = "itap_lab";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}
?>