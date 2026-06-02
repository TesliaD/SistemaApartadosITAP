<?php

include("../includes/conexion.php");

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    $sql = "DELETE FROM noticias WHERE IdNoticias = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    if($stmt->execute()){

        header("Location: /SistemaApartadosITAP/views/admin/Ventana_Editar_Noticias.php");
        exit;

    }

}
?>