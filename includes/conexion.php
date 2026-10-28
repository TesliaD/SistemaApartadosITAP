<<<<<<< HEAD
<?php
// Hora local de Agua Prieta, Sonora (sin horario de verano)
date_default_timezone_set('America/Hermosillo');

$host = "localhost";
$user = "root";
$pass = "";
$db = "itap_lab";
=======
<?php 
$host = "db5021391055.hosting-data.io";
$user = "dbu3431324";
$pass = "Lab0rat0ri0s.2026AD";
$db = "dbs16109971";
>>>>>>> 4ac5f64288c895680aefcbe7404e718476b34518

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}
?>