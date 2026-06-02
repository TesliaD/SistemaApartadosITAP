<?php 
include("../../includes/auth.php"); 
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
?>
<link rel="stylesheet" href="/SistemaApartadosITAP/css/noticias.css">
<body>

<div class="content p-4" id="content">

    <!-- TITULO -->
    <div class="mb-4">

        <h2 class="fw-bold titulo-noticias">
            <i class="bi bi-newspaper"></i>
            Publicar Nueva Noticia
        </h2>

        <p class="text-muted">
            Agrega noticias, avisos o información importante para los usuarios.
        </p>

    </div>

    <!-- CARD -->
    <div class="card shadow-lg border-0 rounded-4">

        <!-- HEADER -->
        <div class="card-header noticias-header rounded-top-4 p-3">

            <h4 class="mb-0">
                <i class="bi bi-pencil-square"></i>
                Información de la noticia
            </h4>

        </div>

        <!-- BODY -->
        <div class="card-body p-4">

            <form 
                id="formNoticias"
                enctype="multipart/form-data">

                <div class="row g-4">

                    <!-- TITULO -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Título de la noticia
                        </label>

                        <input 
                            type="text"
                            name="Titulo"
                            class="form-control shadow-sm"
                            placeholder="Ejemplo: Nuevo laboratorio disponible"
                            required>

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
                            placeholder="Nombre del autor"
                            required>

                    </div>

                    <!-- CATEGORIA -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Categoría
                        </label>

                        <select 
                            name="Categoria"
                            class="form-select shadow-sm">

                            <option value="Tecnología">
                                Tecnología
                            </option>

                            <option value="Eventos">
                                Eventos
                            </option>

                            <option value="Avisos">
                                Avisos
                            </option>

                            <option value="Mantenimiento">
                                Mantenimiento
                            </option>

                        </select>

                    </div>

                    <!-- CONTENIDO -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Contenido de la noticia
                        </label>

                        <textarea
                            name="Cuerpo"
                            rows="7"
                            class="form-control shadow-sm"
                            placeholder="Escribe aquí la noticia..."
                            required></textarea>

                    </div>

                    <!-- IMAGEN -->
                    <div class="col-md-12">

                        <label class="form-label fw-bold">
                            Imagen de la noticia
                        </label>

                        <input
                            type="file"
                            name="Imagen"
                            class="form-control shadow-sm"
                            accept="image/*">

                        <!-- PREVIEW -->
                        <div class="mt-3">

                            <img 
                                id="preview"
                                class="img-fluid rounded shadow d-none"
                                style="max-height:250px;">

                        </div>

                    </div>

                    <!-- ESTADO -->
                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Estado
                        </label>

                        <select 
                            name="activo"
                            class="form-select shadow-sm">

                            <option value="1">
                                Publicado
                            </option>

                            <option value="0">
                                Oculto
                            </option>

                        </select>

                    </div>

                </div>

                <!-- BOTONES -->
                <div class="mt-5 d-flex justify-content-end gap-3">

                    <button 
                        type="reset"
                        class="btn btn-outline-secondary px-4">

                        <i class="bi bi-x-circle"></i>
                        Limpiar

                    </button>

                    <button 
                        type="submit"
                        class="btn btn-primary px-4 shadow">

                        <i class="bi bi-send"></i>
                        Publicar Noticia

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- PREVIEW IMAGEN -->
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




<!-- LOGOUT -->
<script src="../../js/logout.js"></script>

<!-- GUARDAR NOTICIAS -->
<script src="../../js/form_guardar_noticias.js"></script>

</body>
</html>