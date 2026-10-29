<?php

include("../includes/conexion.php");

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    $sql = "DELETE FROM noticias WHERE IdNoticias = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    if($stmt->execute()){

        // Ruta relativa y con mayúsculas exactas (el hosting Linux distingue Views/Admin de views/admin)
        header("Location: ../Views/Admin/Ventana_Editar_Noticias.php");
        exit;

    }

}
?>