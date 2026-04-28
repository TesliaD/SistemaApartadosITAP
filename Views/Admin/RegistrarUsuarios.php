<?php include("../../includes/auth.php");?> 

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>


<body>


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
<script src="../../js/formguardarusuarios_XY.js"></script>

</body>
</html>