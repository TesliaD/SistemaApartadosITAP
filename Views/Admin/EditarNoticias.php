<?php

include("../../includes/auth.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");


/* =========================
VALIDAR ID
========================= */

if(!isset($_GET['id'])){

    header("Location: Ventana_Editar_Noticias.php");
    exit();

}

$id = intval($_GET['id']);

/* =========================
OBTENER NOTICIA
========================= */

$sql = "
    SELECT *
    FROM noticias
    WHERE IDNoticias = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows <= 0){

    header("Location: Ventana_Editar_Noticias.php");
    exit();

}

$noticia = $resultado->fetch_assoc();

?>
<link rel="stylesheet" href="/SistemaApartadosITAP/css/noticias.css">
<div class="content" id="content">

    <!-- TITULO -->
    <div class="mb-4">

        <h2 class="fw-bold titulo-editar">

            <i class="bi bi-pencil-square"></i>
            Editar Noticia

        </h2>

        <p class="text-muted">
            Modifica la información de la noticia.
        </p>

    </div>

    <!-- CARD -->
    <div class="card shadow-lg border-0 rounded-4">

        <!-- HEADER -->
        <div class="card-header editar-header rounded-top-4 p-3">

            <h4 class="mb-0">

                <i class="bi bi-newspaper"></i>
                Información de la noticia

            </h4>

        </div>

        <!-- BODY -->
        <div class="card-body p-4">

            <form

                id="formNoticias"
                action="/SistemaApartadosITAP/controllers/ActualizarNoticias.php"
                method="POST"
                enctype="multipart/form-data">

                <!-- ID -->
                <input
                    type="hidden"
                    name="IdNoticias"
                    value="<?= $noticia['IDNoticias'] ?>">

                <!-- IMAGEN ACTUAL -->
                <input
                    type="hidden"
                    name="ImagenActual"
                    value="<?= $noticia['Imagen'] ?>">

                <div class="row g-4">

                    <!-- TITULO -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Título
                        </label>

                        <input
                            type="text"
                            name="Titulo"
                            class="form-control shadow-sm"
                            required
                            value="<?= htmlspecialchars($noticia['Titulo']) ?>">

                    </div>

                    <!-- AUTOR -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Autor
                        </label>

                        <input
                            type="text"
                            name="Nombre"
                            class="form-control shadow-sm"
                            required
                            value="<?= htmlspecialchars($noticia['Nombre']) ?>">

                    </div>

                    <!-- CATEGORIA -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Categoría
                        </label>

                        <select
                            name="Categoria"
                            class="form-select shadow-sm">

                            <?php

                            $categorias = [
                                "Tecnología",
                                "Eventos",
                                "Avisos",
                                "Mantenimiento"
                            ];

                            foreach($categorias as $categoria):

                            ?>

                            <option
                                value="<?= $categoria ?>"
                                <?= ($noticia['Categoria'] == $categoria)
                                    ? 'selected'
                                    : '' ?>>

                                <?= $categoria ?>

                            </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- CONTENIDO -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Contenido
                        </label>

                        <textarea
                            name="Cuerpo"
                            rows="7"
                            class="form-control shadow-sm"
                            required><?= htmlspecialchars($noticia['Cuerpo']) ?></textarea>

                    </div>

                    <!-- IMAGEN -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Cambiar Imagen
                        </label>

                        <input
                            type="file"
                            name="Imagen"
                            class="form-control shadow-sm"
                            accept="image/*">

                    </div>

                    <!-- PREVIEW -->
                    <div class="col-md-12">

                        <?php if(!empty($noticia['Imagen'])): ?>

                            <img
                                src="/SistemaApartadosITAP/uploads/noticias/<?= htmlspecialchars($noticia['Imagen']) ?>"
                                id="preview"
                                class="img-preview shadow rounded">

                        <?php else: ?>

                            <img
                                id="preview"
                                class="img-preview shadow rounded d-none">

                        <?php endif; ?>

                    </div>

                    <!-- ESTADO -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Estado
                        </label>

                        <select
                            name="Activo"
                            class="form-select shadow-sm">

                            <option
                                value="1"
                                <?= ($noticia['Activo'] == 1)
                                    ? 'selected'
                                    : '' ?>>

                                Publicado

                            </option>

                            <option
                                value="0"
                                <?= ($noticia['Activo'] == 0)
                                    ? 'selected'
                                    : '' ?>>

                                Oculto

                            </option>

                        </select>

                    </div>

                </div>

                <!-- BOTONES -->
                <div class="mt-5 d-flex justify-content-end gap-3">

                    <a href="Ventana_Editar_Noticias.php"
                       class="btn btn-secondary px-4">

                        <i class="bi bi-arrow-left"></i>
                        Volver

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary btn-guardar px-4 shadow">

                        <i class="bi bi-save"></i>
                        Guardar Cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<style>

.img-preview{
    width: 100%;
    max-width: 350px;
    max-height: 250px;
    object-fit: cover;
    border-radius: 15px;
}

/* DARK MODE */

body.dark .card{
    background: #1e1e1e !important;
    color: white;
}

body.dark .form-control,
body.dark .form-select{
    background: #2d2d2d;
    color: white;
    border: 1px solid #444;
}

body.dark .form-control:focus,
body.dark .form-select:focus{
    background: #2d2d2d;
    color: white;
}

body.dark .text-muted{
    color: #bdbdbd !important;
}

</style>

<script>

document.querySelector('input[name="Imagen"]')
.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(file){

        const reader = new FileReader();

        reader.onload = function(ev){

            const preview =
                document.getElementById("preview");

            preview.src = ev.target.result;

            preview.classList.remove("d-none");

        }

        reader.readAsDataURL(file);

    }

});

</script>

<script>
    document.getElementById("formNoticias").addEventListener("submit", async function(e){

    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    const response = await fetch(form.action, {
        method: "POST",
        body: data
    });

    const result = await response.json();

    if(result.status === "success"){

        Swal.fire({
            icon: "success",
            title: "¡Listo!",
            text: result.mensaje
        }).then(() => {

            window.location.href =
            "/SistemaApartadosITAP/views/Admin/Ventana_Editar_Noticias.php";

        });

    } else {

        Swal.fire({
            icon: "error",
            text: result.mensaje
        });

    }

});
</script>

<script src="../../js/logout.js"></script>

</body>
</html>