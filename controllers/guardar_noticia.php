<?php

include("../includes/conexion.php");

header('Content-Type: application/json');

/* ==========================
   VALIDAR MÉTODO
========================== */

if($_SERVER["REQUEST_METHOD"] != "POST"){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Método no permitido"
    ]);

    exit;
}

/* ==========================
   OBTENER DATOS
========================== */

$titulo =
    $_POST['Titulo'] ?? '';

$nombre =
    $_POST['Nombre'] ?? '';

$cuerpo =
    $_POST['Cuerpo'] ?? '';

$categoria =
    $_POST['Categoria'] ?? '';

$activo =
    $_POST['activo'] ?? 1;

/* ==========================
   USUARIO
========================== */

/* CAMBIA ESTO DESPUÉS
   POR EL USUARIO REAL */

$noControl = "admin";

/* ==========================
   LIMPIAR DATOS
========================== */

$titulo =
    trim($titulo);

$nombre =
    trim($nombre);

$cuerpo =
    trim($cuerpo);

$categoria =
    trim($categoria);

/* ==========================
   EVITAR XSS
========================== */

$titulo =
    htmlspecialchars($titulo);

$nombre =
    htmlspecialchars($nombre);

$cuerpo =
    htmlspecialchars($cuerpo);

$categoria =
    htmlspecialchars($categoria);

/* ==========================
   VALIDACIONES
========================== */

// CAMPOS VACÍOS

if(
    empty($titulo) ||
    empty($nombre) ||
    empty($cuerpo)
){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Completa todos los campos"
    ]);

    exit;
}

// TÍTULO

if(strlen($titulo) < 5){

    echo json_encode([
        "status" => "error",
        "mensaje" => "El título debe tener al menos 5 caracteres"
    ]);

    exit;
}

if(strlen($titulo) > 120){

    echo json_encode([
        "status" => "error",
        "mensaje" => "El título es demasiado largo"
    ]);

    exit;
}

// NOMBRE

if(strlen($nombre) < 3){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Nombre inválido"
    ]);

    exit;
}

if(strlen($nombre) > 60){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Nombre demasiado largo"
    ]);

    exit;
}

// CONTENIDO

if(strlen($cuerpo) < 20){

    echo json_encode([
        "status" => "error",
        "mensaje" => "La noticia es demasiado corta"
    ]);

    exit;
}

if(strlen($cuerpo) > 5000){

    echo json_encode([
        "status" => "error",
        "mensaje" => "La noticia es demasiado larga"
    ]);

    exit;
}

/* ==========================
   VALIDAR CATEGORÍA
========================== */

$categoriasValidas = [
    "Tecnología",
    "Eventos",
    "Avisos",
    "Mantenimiento"
];

if(
    !in_array(
        $categoria,
        $categoriasValidas
    )
){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Categoría inválida"
    ]);

    exit;
}

/* ==========================
   IMAGEN
========================== */

$nombreImagen = null;

if(
    isset($_FILES['Imagen']) &&
    $_FILES['Imagen']['error'] == 0
){

    $carpeta =
        "../uploads/noticias/";

    // CREAR CARPETA

    if(!file_exists($carpeta)){

        mkdir($carpeta, 0777, true);

    }

    // OBTENER EXTENSIÓN

    $extension =
        strtolower(
            pathinfo(
                $_FILES['Imagen']['name'],
                PATHINFO_EXTENSION
            )
        );

    /* ==========================
       VALIDAR EXTENSIÓN
    ========================== */

    $permitidos = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    if(
        !in_array(
            $extension,
            $permitidos
        )
    ){

        echo json_encode([
            "status" => "error",
            "mensaje" => "Formato de imagen inválido"
        ]);

        exit;
    }

    /* ==========================
       VALIDAR TAMAÑO
    ========================== */

    $maxSize =
        5 * 1024 * 1024;

    if(
        $_FILES['Imagen']['size'] > $maxSize
    ){

        echo json_encode([
            "status" => "error",
            "mensaje" => "La imagen excede el límite de 5MB"
        ]);

        exit;
    }

    /* ==========================
       GENERAR NOMBRE
    ========================== */

    $nombreImagen =
        time() .
        "_" .
        rand(1000,9999) .
        "." .
        $extension;

    $rutaFinal =
        $carpeta .
        $nombreImagen;

    /* ==========================
       MOVER IMAGEN
    ========================== */

    if(
        !move_uploaded_file(
            $_FILES['Imagen']['tmp_name'],
            $rutaFinal
        )
    ){

        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al subir la imagen"
        ]);

        exit;
    }

}

/* ==========================
   INSERTAR
========================== */

$sql = "

INSERT INTO noticias
(
    Titulo,
    Nombre,
    Cuerpo,
    noControl,
    Activo,
    Categoria,
    Imagen
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?
)

";

$stmt =
    $conn->prepare($sql);

/* ==========================
   VALIDAR PREPARE
========================== */

if(!$stmt){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Error en la consulta SQL"
    ]);

    exit;
}

/* ==========================
   BIND PARAMS
========================== */

$stmt->bind_param(
    "ssssiis",
    $titulo,
    $nombre,
    $cuerpo,
    $noControl,
    $activo,
    $categoria,
    $nombreImagen
);

/* ==========================
   EJECUTAR
========================== */

if($stmt->execute()){

    echo json_encode([
        "status" => "success",
        "mensaje" => "Noticia publicada correctamente"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al guardar la noticia"
    ]);

}

/* ==========================
   CERRAR
========================== */

$stmt->close();

$conn->close();

?>