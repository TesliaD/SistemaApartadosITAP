<?php
include("../includes/conexion.php");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];

$conn->query("UPDATE reservaciones SET Estado='Cancelado' WHERE IDReservacion=$id");

echo json_encode(["ok"=>true]);