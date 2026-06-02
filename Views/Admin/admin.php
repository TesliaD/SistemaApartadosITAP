<?php 
include("../../includes/conexion.php"); 
include("../../includes/auth.php"); 
include("../../includes/header.php");
include("../../includes/navbar.php");
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
<!-- ESTILOS -->
<!-- ========================= -->
<style>

/* ==========================
BODY
========================== */
body{
    color: #212529;
}

/* ==========================
CONTENIDO
========================== */
.content{
    padding: 25px;
    min-height: 100vh;
}

/* ==========================
TARJETAS DASHBOARD
========================== */
.dashboard-card{
    border-radius: 22px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    transition: .3s ease;
    overflow: hidden;
}

.dashboard-card:hover{
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,.08);
}

/* ==========================
ICONOS
========================== */
.icon-circle{
    width: 85px;
    height: 85px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    font-size: 2rem;
}

/* ==========================
AVISO
========================== */
.aviso-card{
    background: #ffffff;
    border-radius: 22px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,.05);
}

/* ==========================
NOTICIAS
========================== */
.news-section{
    margin-top: 50px;
}

.news-title{
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.news-subtitle{
    color: #6b7280;
}

/* ==========================
CARD NOTICIAS
========================== */
.noticia-card{
    background: #ffffff;
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    transition: all .3s ease;
    height: 100%;
    box-shadow: 0 4px 14px rgba(0,0,0,.05);
}

.noticia-card:hover{
    transform: translateY(-5px);
    box-shadow: 0 14px 30px rgba(0,0,0,.08);
}

/* ==========================
IMAGEN
========================== */
.noticia-img{
    width: 100%;
    height: 240px;
    object-fit: cover;
    display: block;
}

/* ==========================
BODY CARD
========================== */
.noticia-body{
    padding: 24px;
}

/* ==========================
TITULO
========================== */
.noticia-titulo{
    font-size: 1.5rem;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 12px;
}

/* ==========================
INFO
========================== */
.noticia-info{
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 15px;
}

/* ==========================
BADGE
========================== */
.noticia-badge{
    background: #2563eb;
    color: white;
    padding: 8px 14px;
    border-radius: 50px;
    display: inline-block;
    font-size: 13px;
    margin-bottom: 15px;
}

/* ==========================
TEXTO
========================== */
.noticia-texto{
    color: #374151;
    line-height: 1.8;
    font-size: 15px;
}

/* ==========================
BOTON
========================== */
.btn-news{
    margin-top: 15px;
    border-radius: 12px;
    padding: 10px 20px;
    background: #2563eb;
    color: white;
    border: none;
    transition: .3s;
    font-weight: 600;
}

.btn-news:hover{
    background: #1d4ed8;
}

/* ==========================
RESPONSIVE
========================== */
@media(max-width:991px){

    .noticia-img{
        height: 220px;
    }

}

@media(max-width:768px){

    .content{
        padding: 15px;
    }

    .news-title{
        font-size: 1.6rem;
    }

    .noticia-img{
        height: 190px;
    }

    .noticia-titulo{
        font-size: 1.3rem;
    }

}

</style>

<!-- ========================= -->
<!-- JS -->
<!-- ========================= -->
<script src="../../js/logout.js"></script>

</body>
</html>