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

    <a href="admin.php"><i class="bi bi-house"></i><span>Inicio</span></a>

    <!-- SUBMENU -->
     
    <!-- Usuarios -->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-people"></i> <span>Usuarios</span>
    </a>
    <div class="submenu">
        <a href="VerUsuarios_Editar.php"><i class="bi bi-person-badge-fill"></i>Lista</a>
        <a href="RegistrarUsuarios.php"><i class="bi bi-person-add"></i>Agregar</a>
    </div>

    <!-- Laboratorios -->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-laptop"></i> <span>Laboratorios</span>
    </a>

    <div class="submenu">
        <a href="RegistrarLaboratorios.php"><i class="bi bi-building-fill-add"></i>Registrar</a>
        <a href="VerLaboratorios_Editar.php"><i class="bi bi-display"></i>Lista de Laboratorios</a>
    </div>
    
    <!--Reportes-->
    <a href="reportes_asistencia.php"><i class="bi bi-bar-chart"></i> <span>Reportes</span></a> 
    
    <!--Noticias-->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-newspaper"></i> <span>Noticias</span>
    </a>
    <div class="submenu">
        <a href="Ventana_AgregarNoticias.php"><i class="bi bi-alarm"></i>Agregar Noticias</a>
        <a href="Ventana_Editar_Noticias.php"><i class="bi bi-info-circle"></i>Editar Noticias</a>
    </div>
    
    <a href="#"><i class="bi bi-chat-left-text"></i> <span>Mensajes</span></a>
    <a href="VentanaApartarLab.php"><i class="bi bi-calendar-check"></i><span>Apartados</span></a>

    <hr>
        <a href="#" id="logoutBtn">
            <i class="bi bi-box-arrow-right"></i> <span>Salir</span>
        </a>
</div>

<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>