<?php include("../../includes/auth.php"); ?>
<?php include("../../includes/header.php"); ?>
<?php include("../../includes/navbar.php"); ?>
<?php include("../../includes/conexion.php"); ?>

<style>
    .preview-img {
        max-width: 200px;
        max-height: 150px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        margin-top: 10px;
    }
</style>

<div class="content" id="content">

    <h3 class="mb-4">
        <i class="bi bi-laptop"></i> Registrar Laboratorio
    </h3>

    <div class="card p-4">

        <form id="formLaboratorio" method="POST" enctype="multipart/form-data">

            <div class="row g-3">

                <!-- NOMBRE DEL LABORATORIO -->
                <div class="col-md-6">
                    <label class="form-label">Nombre del Laboratorio</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Laboratorio de Redes" required>
                </div>

                <!-- NÚMERO DE LABORATORIO -->
                <div class="col-md-6">
                    <label class="form-label">Número de Laboratorio</label>
                    <input type="text" name="numLab" id="numLab" class="form-control" placeholder="Ej: 3, 4, 5, etc." required>
                </div>

                <!-- NÚMERO DE MÁQUINAS -->
                <div class="col-md-6">
                    <label class="form-label">Número de Máquinas</label>
                    <input type="number" name="numMaquinas" id="numMaquinas" class="form-control" placeholder="Ej: 30" required>
                </div>

                <!-- DEPARTAMENTO -->
                <div class="col-md-6">
                    <label class="form-label">Departamento</label>
                    <select name="IDDepartamento" id="IDDepartamento" class="form-select" required>
                        <option value="">Seleccionar departamento</option>
                        <?php
                        $deptos = $conn->query("SELECT IDDepartamentos, nombre FROM departamentos ORDER BY nombre");
                        while($depto = $deptos->fetch_assoc()):
                        ?>
                        <option value="<?= $depto['IDDepartamentos'] ?>"><?= htmlspecialchars($depto['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- DESCRIPCIÓN -->
                <div class="col-md-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3" placeholder="Descripción del laboratorio..."></textarea>
                </div>

                <!-- ESTADO -->
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="activo" id="activo" class="form-select">
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
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="bi bi-save"></i> Guardar Laboratorio
                </button>
            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/form_guardar_lab.js"></script>
