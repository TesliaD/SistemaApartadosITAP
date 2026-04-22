<?php include("../../includes/auth.php");?> 
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin - Centro de Cómputo</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Estilos propios -->
<link rel="stylesheet" href="../../css/variablesglobales.css">
<link rel="stylesheet" href="../../css/adminDash.css">

<!--SweetAlert2-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

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

<!-- CONTENIDO -->
<div class="content" id="content">

    <h3 class="mb-4">Dashboard</h3>

    <div class="row g-4">

        <!-- CARD 1 --> 
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="bi bi-people fs-1 text-primary"></i>
                <h5 class="mt-3">Usuarios</h5>
            </div>
        </div>

        <!-- CARD 2 --> 
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="bi bi-laptop fs-1 text-success"></i>
                <h5 class="mt-3">Laboratorios</h5>
            </div>
        </div>

         <!-- CARD 3 --> 
        <div class="col-md-4"> 
            <div class="card p-4 text-center"> 
                <i class="bi bi-bar-chart fs-1 text-danger"></i> 
                <h5 class="mt-3">Reportes</h5> 
            </div> 
        </div> 

        <!-- MENSAJE --> 
        <div class="card mt-4 p-3"> <h5><i class="bi bi-info-circle"></i> Aviso</h5> 
            <p> Se les recuerda que está prohibido introducir alimentos o bebidas. Mantenga el uso adecuado del equipo de cómputo. </p> 
        </div> 
        
        <!--Noticias--> 
        <div class="card mt-4 p-3"> <h5><i class="bi bi-newspaper"></i> Noticias</h5> 
            <p> 
                <p>1.- Aviso Importante</p> 
                <p>El servicio de los laboratorios de centro de cómputo es de 9:00 am a 2:00 pm y de 4:00pm a 7:00pm, 
                    fuera de ese horario no se garantiza el servicio; para que tome sus debidas precauciones.
                </p> 
                <p>2.- Uso de Laboratorios</p> 
                <p>Se les Recuerda que esta prohibido introducir alimentos o bebidas; 
                les pedimos su cooperación y apoyo para hacer un uso correcto de los equipos de cómputo.
                </p> 
                <p>Por su atención, ¡Muchas Gracias!</p> 
            </p>
        </div>
    </div>
</div>

<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>

</body>
</html>