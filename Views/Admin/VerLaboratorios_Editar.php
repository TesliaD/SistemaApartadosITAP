<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

$sql = "SELECT l.*, d.nombre AS nombre_departamento
        FROM laboratorios l
        INNER JOIN departamentos d 
        ON l.IDDepartamento = d.IDDepartamentos";
$result = $conn->query($sql);
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<div class="content" id="content">

    <h3 class="mb-4">
        <i class="bi bi-pc-display"></i> Lista de laboratorios
    </h3>

    <!-- BUSCADOR -->
    <div class="mb-3">
        <input type="text" id="buscador" class="form-control" placeholder="Buscar usuario...">
    </div>

    <!-- TABLA -->
    <div class="card p-3">
        <table class="table table-hover" id="usuarios">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Número Maquinas</th>
                    <th>Descripcion</th>
                    <th>Activo</th>
                    <th>Número Laboratorio</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['Nombre']; ?></td>
                    <td><?php echo $row['numMaquinas']; ?></td>
                    <td><?php echo $row['Descripcion']; ?></td>
                    <td>
                        <?php echo $row['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </td>
                    <td><?php echo $row['numLab']; ?></td>
                    <td><?php echo $row['nombre_departamento']; ?></td>
                    <td>
                        <!--EDITAR -->
                        <button 
                            class="btn btn-warning btn-sm btnEditar"
                            data-id="<?php echo $row['IDLab']; ?>"
                            data-nombre="<?php echo $row['Nombre']; ?>"
                            data-num_maquinas="<?php echo $row['numMaquinas']; ?>"
                            data-descripcion="<?php echo $row['Descripcion']; ?>"
                            data-num_lab="<?php echo $row['numLab']; ?>"
                            data-departamento="<?php echo $row['IDDepartamento']; ?>"
                            data-activo="<?php echo $row['activo']; ?>"

                        ><i class="bi bi-pencil"></i></button>

                        <!--ELIMINAR -->
                        <button 
                            class="btn btn-danger btn-sm btnEliminar" 
                            data-id="<?php echo $row['IDLab']; ?>"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>
    </div>

</div>

<!-- 🪟 MODAL EDITAR LABORATORIO -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar Laboratorio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formEditar">

          <input type="hidden" id="edit_id" name="id">

          <div class="mb-3">
            <label>Nombre</label>
            <input type="text" id="edit_nombre" name="nombre" class="form-control">
          </div>

          <div class="mb-3">
            <label>No. Máquinas</label>
            <input type="number" id="edit_num_maquinas" name="num_maquinas" class="form-control">
          </div>

          <div class="mb-3">
            <label>Descripción</label>
            <input type="text" id="edit_descripcion" name="descripcion" class="form-control">
          </div>

          <div class="mb-3">
            <label>Número de Laboratorio</label>
            <input type="text" id="edit_num_lab" name="num_lab" class="form-control">
          </div>

          <div class="mb-3">
            <label>Departamento</label>
            <select id="edit_departamento" name="id_departamento" class="form-select">
                <option value="1">Sistemas</option>
                <option value="2">Electrónica</option>
            </select>
          </div>

          <div class="mb-3">
            <label>Estado</label>
            <select id="edit_activo" name="activo" class="form-select">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarCambios">Guardar</button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- TUS SCRIPTS -->
<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>
<script src="../../js/actualizarlaboratorio.js"></script>
