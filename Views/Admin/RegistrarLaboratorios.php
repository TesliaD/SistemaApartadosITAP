<?php include("../../includes/auth.php");?> 

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>
<body>


<!-- CONTENIDO -->
<!--Campos para registrar Laboratorios-->
<div class="content" id="content">

    <h3 class="mb-4">
        <i class="bi bi-pc-display"></i> Registro de Laboratorios
    </h3>

    <div class="card p-4">

        <form action="guardar_laboratorio.php" method="POST">

            <div class="row g-3">

                <!-- Nombre del Laboratorio -->
                <div class="col-md-6">
                    <label class="form-label">Nombre del Laboratorio</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <!-- No. de Maquinas -->
                <div class="col-md-6">
                    <label class="form-label">No. de Maqinas</label>
                    <input type="number" name="num_maquinas" class="form-control" required>
                </div>

                <!--Descripcion-->
                <div class="col-md-6">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" required>
                </div>

                <!-- Numero Laboratorio -->
                <div class="col-md-6">
                    <label class="form-label">Número de Laboratorio</label>
                    <input type="text" name="num_lab" class="form-control" required>
                </div>

                <!--Departamento-->
                <div class="col-md-6">
                    <label class="form-label">Departamento</label>
                    <select name="id_departamento" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="1">Sistemas</option>
                        <option value="2">Electrónica</option>
                    </select>
                </div>

                <!-- ESTADO -->
                <div class="col-md-6">
                    <label class="form-label">Estado del Laboratorio</label>
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
<script src="../../js/form_guardar_lab.js"></script>

</body>
</html>