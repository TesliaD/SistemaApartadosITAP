<link rel="stylesheet" href="/SistemaApartadosITAP/css/navbarmaestros.css">
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

    <a href="maestro.php"><i class="bi bi-house"></i><span>Inicio</span></a>

    <!-- SUBMENU -->
    <!-- Laboratorios -->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-laptop"></i> <span>Laboratorios</span>
    </a>

    <div class="submenu">
        <a href="maestro_apartar_lab.php"><i class="bi bi-building-fill-add"></i>Apartar Laboratorio</a>
    </div>

    <!-- Grupos -->
    <a class="toggle-submenu">
        <i class="bi bi-people"></i> Mis Grupos
    </a>
 
    <div class="submenu">
        <a href="grupos_maestro.php"><i class="bi bi-building-fill-add"></i>Dar de Alta Grupos</a>
        <a href="VerLaboratorios_Editar.php"><i class="bi bi-display"></i>Mis Grupos</a>
    </div>
    
    <!-- Reportes -->
    <a class="toggle-submenu">
        <i class="bi bi-people"></i> Reportes
    </a>
 
    <div class="submenu">
        <a href="reporte_maestros.php"><i class="bi bi-building-fill-add"></i>Reporte de Asistencia</a>
    </div>
    
    <a class="toggle-submenu">
        <i class="bi bi-calendar3"></i> Reservaciones
    </a>
    <div class="submenu">
        <a href="maestro_mis_reservaciones.php"><i class="bi bi-building-fill-add"></i>Mis Reservaciones</a>
    </div>
   
    <a href="#" id="logoutBtn">
        <i class="bi bi-box-arrow-right"></i> <span>Salir</span>
    </a>
</div>

<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>