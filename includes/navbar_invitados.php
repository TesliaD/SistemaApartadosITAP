<link rel="stylesheet" href="/SistemaApartadosITAP/css/navbar.css">
<link rel="stylesheet" href="/SistemaApartadosITAP/css/dashcards.css">
<div class="overlay" id="overlay"></div>

<!-- NAVBAR -->
<nav class="navbar navbar-dark px-3">

    <button class="btn btn-light" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    <span class="navbar-brand ms-2">
        <i class="bi bi-pc-display"></i> Centro de Cómputo
    </span>

    <div class="ms-auto d-flex align-items-center gap-3 text-white">

        <!-- DARK MODE -->
        <button class="btn btn-sm btn-light" id="darkModeBtn">
            <i class="bi bi-moon"></i>
        </button>

        <div class="text-end">
            <strong><?php echo $_SESSION['usuario']; ?></strong><br>
            <small><?php echo $_SESSION['rol']; ?></small>
        </div>

    </div>

</nav>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <h5 class="mb-4">Menú</h5>

    <a href="inicio_invitado.php"><i class="bi bi-house"></i><span>Inicio</span></a>

    <!-- SUBMENU -->
    <!--Departamentos-->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-building-fill-check"></i> <span>Departamentos</span>
    </a>
    <div class="submenu">
        <a href="invitado.php"><i class="bi bi-building-fill-add"></i></i>Agregar Departamentos</a>
    </div>

    <!--Reservaciones-->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-newspaper"></i> <span>Reservaciones</span>
    </a>
    <div class="submenu">
        <a href="VentanaApartarLab.php"><i class="bi bi-calendar-check"></i><span>Apartados</span></a>
        <a href="VerReservaciones.php"><i class="bi bi-info-circle"></i>Ver Reservaciones</a>
        <a href="../Admin/ReservacionesPorDia.php"><i class="bi bi-info-circle"></i>Reservaciones Diarias</a>
    </div>
    
    <hr>
        <a href="#" id="logoutBtn">
            <i class="bi bi-box-arrow-right"></i> <span>Salir</span>
        </a>
</div>

<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>