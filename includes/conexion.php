<?php
// Hora local de Agua Prieta, Sonora (sin horario de verano)
date_default_timezone_set('America/Hermosillo');

// Local (XAMPP) u hosting, según el servidor donde corre el sistema
$esLocal = in_array(strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]), ['localhost', '127.0.0.1'], true);

if ($esLocal) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "itap_lab";
} else {
    $host = "db5021391055.hosting-data.io";
    $user = "dbu3431324";
    $pass = "Lab0rat0ri0s.2026AD";
    $db = "dbs16109971";
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}
?>