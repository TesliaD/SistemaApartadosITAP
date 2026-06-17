<?php 
include("../../includes/conexion.php"); 
include("../../includes/auth_maestro.php"); 
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");
?>

<!-- ========================= -->
<!-- CONTENIDO -->
<!-- ========================= -->
<div class="content" id="content">

    <!-- TITULO -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>

            <h3 class="fw-bold mb-1">
                <i class="bi bi-speedometer2 text-primary"></i>
                Dashboard
            </h3>

            <p class="text-muted mb-0">
                Panel principal del sistema de apartados.
            </p>

        </div>

    </div>

    <!-- ========================= -->
    <!-- TARJETAS -->
    <!-- ========================= -->
    <div class="row g-4">

        <!-- CARD 1 -->
        <div class="col-lg-4 col-md-6">

            <div class="card dashboard-card h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-circle bg-primary-subtle mb-3">

                        <i class="bi bi-people-fill text-primary"></i>

                    </div>

                    <h5 class="fw-bold">
                        Usuarios
                    </h5>

                    <p class="text-muted mb-0">
                        Gestión de usuarios del sistema.
                    </p>

                </div>

            </div>

        </div>

        <!-- CARD 2 -->
        <div class="col-lg-4 col-md-6">

            <div class="card dashboard-card h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-circle bg-success-subtle mb-3">

                        <i class="bi bi-laptop text-success"></i>

                    </div>

                    <h5 class="fw-bold">
                        Laboratorios
                    </h5>

                    <p class="text-muted mb-0">
                        Administración de laboratorios.
                    </p>

                </div>

            </div>

        </div>

        <!-- CARD 3 -->
        <div class="col-lg-4 col-md-12">

            <div class="card dashboard-card h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-circle bg-danger-subtle mb-3">

                        <i class="bi bi-bar-chart-fill text-danger"></i>

                    </div>

                    <h5 class="fw-bold">
                        Reportes
                    </h5>

                    <p class="text-muted mb-0">
                        Estadísticas y reportes del sistema.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- ========================= -->
    <!-- AVISO -->
    <!-- ========================= -->
    <div class="card aviso-card mt-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                <i class="bi bi-info-circle-fill text-warning"></i>

                Aviso Importante

            </h5>

            <p class="mb-0 text-muted">

                Se les recuerda que está prohibido introducir alimentos o bebidas.
                Mantenga el uso adecuado del equipo de cómputo.

            </p>

        </div>

    </div>

    <!-- ========================= -->
    <!-- NOTICIAS -->
    <!-- ========================= -->
    <div class="news-section">

        <div class="mb-4">

            <h3 class="news-title">

                <i class="bi bi-newspaper text-primary"></i>

                Últimas Noticias

            </h3>

            <p class="news-subtitle">
                Noticias y avisos recientes del sistema.
            </p>

        </div>

        <div class="row g-4">

            <?php

            $sql = "
                SELECT *
                FROM noticias
                WHERE Activo = 1
                ORDER BY FechaPublicacion DESC
                LIMIT 6
            ";

            $resultado = $conn->query($sql);

            if($resultado && $resultado->num_rows > 0):

                while($noticia = $resultado->fetch_assoc()):

            ?>

            <!-- CARD NOTICIA -->
            <div class="col-xl-4 col-md-6">

                <div class="noticia-card">

                    <!-- IMAGEN -->
                    <?php if(!empty($noticia['Imagen'])): ?>

                        <img 
                            src="/SistemaApartadosITAP/uploads/noticias/<?= htmlspecialchars($noticia['Imagen']) ?>"
                            class="noticia-img"
                            alt="Noticia">

                    <?php endif; ?>

                    <!-- BODY -->
                    <div class="noticia-body">

                        <!-- TITULO -->
                        <h4 class="noticia-titulo">

                            <?= htmlspecialchars($noticia['Titulo']) ?>

                        </h4>

                        <!-- INFO -->
                        <div class="noticia-info">

                            <span>

                                <i class="bi bi-person-fill"></i>

                                <?= htmlspecialchars($noticia['Nombre']) ?>

                            </span>

                            &nbsp;&nbsp;

                            <span>

                                <i class="bi bi-calendar-event"></i>

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($noticia['FechaPublicacion'])
                                ) ?>

                            </span>

                        </div>

                        <!-- CATEGORIA -->
                        <?php if(!empty($noticia['Categoria'])): ?>

                            <span class="noticia-badge">

                                <?= htmlspecialchars($noticia['Categoria']) ?>

                            </span>

                        <?php endif; ?>

                        <!-- TEXTO -->
                        <p class="noticia-texto">

                            <?= mb_strimwidth(
                                strip_tags($noticia['Cuerpo']),
                                0,
                                160,
                                "..."
                            ) ?>

                        </p>

                        <!-- BOTON -->
                        <button class="btn-news">

                            Ver Más

                        </button>

                    </div>

                </div>

            </div>

            <?php

                endwhile;

            else:

            ?>

            <!-- SIN NOTICIAS -->
            <div class="col-12">

                <div class="card border-0 shadow-sm p-5 text-center rounded-4">

                    <i class="bi bi-newspaper fs-1 text-secondary"></i>

                    <p class="mt-3 text-muted mb-0">
                        No hay noticias disponibles.
                    </p>

                </div>

            </div>

            <?php endif; ?>

        </div>

    </div>

</div>



<!-- ========================= -->
<!-- JS -->
<!-- ========================= -->
<script src="../../js/logout.js"></script>

</body>
</html>