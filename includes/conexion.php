<?php 
$host = "db5021391055.hosting-data.io";
$user = "dbu3431324";
$pass = "Lab0rat0ri0s.2026AD";
$db = "dbs16109971";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}
?>