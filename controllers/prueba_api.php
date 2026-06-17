<?php
// archivo: prueba_api.php
// Ubicación: C:\xampp\htdocs\SistemaApartadosITAP\prueba_api.php

header('Content-Type: application/json');
echo json_encode(["mensaje" => "API funciona correctamente", "status" => "ok"]);
?>