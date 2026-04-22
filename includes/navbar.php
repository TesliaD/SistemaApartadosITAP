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

    <a href="#"><i class="bi bi-house"></i><span>Inicio</span></a>

    <!-- SUBMENU -->
    <a href="#" class="toggle-submenu">
        <i class="bi bi-people"></i> <span>Usuarios</span>
    </a>
    <div class="submenu">
        <a href="VerUsuarios_Editar.php">Lista</a>
        <a href="RegistrarUsuarios.php">Agregar</a>
    </div>

    <a href="#"><i class="bi bi-laptop"></i> <span>Laboratorios</span></a>
    <a href="#"><i class="bi bi-bar-chart"></i> <span>Reportes</span></a> 
    <a href="#"><i class="bi bi-newspaper"></i> <span>Noticias</span></a> 
    <a href="#"><i class="bi bi-chat-left-text"></i> <span>Mensajes</span></a> 
    <a href="#"><i class="bi bi-calendar-check"></i> <span>Apartados</span></a>

    <hr>
        <a href="#" id="logoutBtn">
            <i class="bi bi-box-arrow-right"></i> <span>Salir</span>
        </a>
</div>