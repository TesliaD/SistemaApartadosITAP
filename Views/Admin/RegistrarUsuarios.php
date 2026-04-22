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
        <a href="#">Lista</a>
        <a href="#">Agregar</a>
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

    <h3 class="mb-4">
        <i class="bi bi-person-plus"></i> Registro de Usuarios
    </h3>

    <div class="card p-4">

        <form action="guardar_usuario.php" method="POST">

            <div class="row g-3">

                <!-- Numero de Control -->
                <div class="col-md-6">
                    <label class="form-label">Número de Control</label>
                    <input type="text" name="num_control" class="form-control" required>
                </div>

                <!-- NOMBRE -->
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <!-- APELLIDOS -->
                <div class="col-md-6">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>

                <!--Area-->
                <div class="col-md-6">
                    <label class="form-label">Area</label>
                    <input type="text" name="area" class="form-control" required>
                </div>

                <!-- EMAIL -->
                <div class="col-md-6">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!-- CONTRASEÑA -->
                <div class="col-md-6">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                
                <!-- ROL -->
                <div class="col-md-6">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="administrador">Administrador</option>
                        <option value="invitado">Invitado</option>
                        <option value="maestro">Maestro</option>
                    </select>
                </div>

                <!-- ESTADO -->
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>

            </div>

            <!-- BOTONES -->
            <div class="mt-4 d-flex justify-content-end gap-2">

                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Usuario
                </button>

            </div>
        </form>

    </div>

</div>
<!--Dark Mode y menú lateral-->
<script src="../../js/darkmode.js"></script>

<!--Logout-->
<script src="../../js/logout.js"></script>

<!--Formulario para Guarar Usuarios-->
<script src="../../js/formguardarusuarios.js"></script>

</body>
</html>