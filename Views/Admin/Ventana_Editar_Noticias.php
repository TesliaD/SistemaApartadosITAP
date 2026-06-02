<?php
include("../../includes/auth.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
?>
<link rel="stylesheet" href="/SistemaApartadosITAP/css/noticias.css">
<div class="content" id="content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>

            <h2 class="fw-bold titulo-editar">

                <i class="bi bi-newspaper"></i>
                Gestión de Noticias

            </h2>

            <p class="text-muted mb-0">
                Administra las noticias publicadas del sistema.
            </p>

        </div>

        <!-- BOTON -->
        <a href="Ventana_AgregarNoticias.php"
           class="btn btn-primary shadow">

            <i class="bi bi-plus-circle"></i>
            Nueva Noticia

        </a>

    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-lg rounded-4">

        <div class="card-body p-4">

            <div class="table-responsive">

                <table class="table align-middle table-hover">

                    <thead class="table-light">

                        <tr>

                            <th>#</th>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Categoría</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $sql = "
                        SELECT *
                        FROM noticias
                        ORDER BY FechaPublicacion DESC
                    ";

                    $resultado = $conn->query($sql);

                    if($resultado && $resultado->num_rows > 0):

                        $contador = 1;

                        while($noticia = $resultado->fetch_assoc()):

                    ?>

                        <tr>

                            <!-- ID -->
                            <td>

                                <?= $contador++ ?>

                            </td>

                            <!-- IMAGEN -->
                            <td>

                                <?php if(!empty($noticia['Imagen'])): ?>

                                    <img
                                        src="/SistemaApartadosITAP/uploads/noticias/<?= htmlspecialchars($noticia['Imagen']) ?>"
                                        class="img-noticia">

                                <?php else: ?>

                                    <div class="img-placeholder">

                                        <i class="bi bi-image"></i>

                                    </div>

                                <?php endif; ?>

                            </td>

                            <!-- TITULO -->
                            <td>

                                <div class="fw-bold">

                                    <?= htmlspecialchars($noticia['Titulo']) ?>

                                </div>

                            </td>

                            <!-- AUTOR -->
                            <td>

                                <?= htmlspecialchars($noticia['Nombre']) ?>

                            </td>

                            <!-- CATEGORIA -->
                            <td>

                                <span class="badge bg-primary">

                                    <?= htmlspecialchars($noticia['Categoria']) ?>

                                </span>

                            </td>

                            <!-- FECHA -->
                            <td>

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($noticia['FechaPublicacion'])
                                ) ?>

                            </td>

                            <!-- ESTADO -->
                            <td>

                                <?php if($noticia['Activo'] == 1): ?>

                                    <span class="badge bg-success">
                                        Publicado
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Oculto
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- ACCIONES -->
                            <td class="text-center">

                                <div class="d-flex justify-content-center gap-2">

                                    <!-- EDITAR -->
                                    <a href="EditarNoticias.php?id=<?= $noticia['IDNoticias'] ?>"
                                       class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil-square"></i>

                                    </a>

                                    <!-- ELIMINAR -->
                                    <button
                                        class="btn btn-danger btn-sm btnEliminar"
                                        data-id="<?= $noticia['IDNoticias'] ?>">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    <?php

                        endwhile;

                    else:

                    ?>

                    <tr>

                        <td colspan="8">

                            <div class="text-center py-5">

                                <i class="bi bi-newspaper fs-1 text-secondary"></i>

                                <p class="mt-3 text-muted">
                                    No hay noticias registradas.
                                </p>

                            </div>

                        </td>

                    </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- =========================
ESTILOS
========================= -->
<style>

/* TABLA */

.table{
    vertical-align: middle;
}

/* IMAGEN */

.img-noticia{
    width: 90px;
    height: 65px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid #ddd;
}

/* PLACEHOLDER */

.img-placeholder{
    width: 90px;
    height: 65px;
    border-radius: 12px;
    background: #e5e7eb;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 24px;
    color: #6b7280;
}

/* BOTONES */

.btn-sm{
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;
}

/* DARK MODE */

body.dark .card{
    background: #1e1e1e !important;
    color: white;
}

body.dark .table{
    color: white;
}

body.dark .table-light{
    background: #2d2d2d !important;
}

body.dark .table-hover tbody tr:hover{
    background: rgba(255,255,255,.05);
}

body.dark .img-placeholder{
    background: #2d2d2d;
    color: white;
}

body.dark .text-muted{
    color: #bdbdbd !important;
}

/* RESPONSIVE */

@media(max-width:768px){

    .img-noticia,
    .img-placeholder{
        width: 70px;
        height: 55px;
    }

}

</style>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- ELIMINAR -->
<script>

document.querySelectorAll(".btnEliminar")
.forEach(btn => {

    btn.addEventListener("click", () => {

        const id = btn.dataset.id;

        Swal.fire({

            title: "¿Eliminar noticia?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",

            showCancelButton: true,

            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",

            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"

        }).then((result) => {

            if(result.isConfirmed){

                window.location.href =
                    "../../controllers/EliminarNoticias.php?id=" + id;

            }

        });

    });

});

</script>

<script src="../../js/logout.js"></script>

</body>
</html>