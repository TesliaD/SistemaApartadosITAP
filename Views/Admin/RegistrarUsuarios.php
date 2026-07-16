<?php include("../../includes/auth.php");?> 
<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>
<?php include("../../includes/conexion.php"); ?>

<style>
    .password-wrapper {
        position: relative;
    }
    .password-wrapper .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #6c757d;
        font-size: 1.1rem;
        z-index: 10;
        padding: 0 5px;
    }
    .password-wrapper .toggle-password:hover {
        color: #0d6efd;
    }
    .password-wrapper .form-control {
        padding-right: 40px;
    }
</style>

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
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="col-md-6">
                    <label class="form-label">Confirmar Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small id="passwordHelp" class="text-muted">Las contraseñas deben coincidir</small>
                </div>
                
                <!-- ROL -->
                <div class="col-md-6">
                    <label class="form-label">Rol</label>
                    <select name="rol" id="rol" class="form-select" required>
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

                <!-- ========================================== -->
                <!-- DEPARTAMENTO - SIEMPRE VISIBLE para el admin -->
                <!-- ========================================== -->
                <div class="col-md-6">
                    <label class="form-label">Departamento</label>
                    <select name="IDDepartamento" id="IDDepartamento" class="form-select">
                        <option value="">Sin departamento</option>
                        <?php
                        // Verificar que $conn existe
                        if($conn) {
                            $sql = "SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre";
                            $deptos = $conn->query($sql);
                            
                            if($deptos && $deptos->num_rows > 0) {
                                while($depto = $deptos->fetch_assoc()):
                        ?>
                        <option value="<?= $depto['IDDepartamentos'] ?>"><?= htmlspecialchars($depto['nombre']) ?></option>
                        <?php 
                                endwhile;
                            } else {
                                echo '<option value="">No hay departamentos disponibles</option>';
                            }
                        } else {
                            echo '<option value="">Error de conexión</option>';
                        }
                        ?>
                    </select>
                    <small class="text-muted" id="deptoHelp">Obligatorio solo para usuarios con rol "Maestro"</small>
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

<script>
    // =========================
    // FUNCIÓN PARA MOSTRAR/OCULTAR CONTRASEÑA
    // =========================
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // =========================
    // VALIDACIÓN: DEPARTAMENTO OBLIGATORIO PARA MAESTROS
    // =========================
    document.addEventListener("DOMContentLoaded", function() {
        const rolSelect = document.getElementById("rol");
        const deptoSelect = document.getElementById("IDDepartamento");
        const deptoHelp = document.getElementById("deptoHelp");

        if(rolSelect && deptoSelect && deptoHelp) {
            rolSelect.addEventListener("change", function() {
                if(this.value === "maestro") {
                    deptoSelect.required = true;
                    deptoHelp.textContent = '⚠️ Obligatorio para maestros';
                    deptoHelp.style.color = '#dc3545';
                } else {
                    deptoSelect.required = false;
                    deptoHelp.textContent = 'Opcional para otros roles';
                    deptoHelp.style.color = '#6c757d';
                }
            });
        }
    });
</script>

<!--Logout-->
<script src="../../js/logout.js"></script>

<!--Formulario para Guardar Usuarios-->
<script src="../../js/formguardarusuarios_XY.js"></script>

</body>
</html>