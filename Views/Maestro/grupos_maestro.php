<?php
include("../../includes/conexion.php");
include("../../includes/auth_maestro.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");

$idUsuario = $_SESSION['id'] ?? 0;

// Obtener datos del usuario
$sqlUsuario = "SELECT nombre, apellidos, num_control FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$nombreCompleto = trim($usuario['nombre'] . " " . ($usuario['apellidos'] ?? ''));
?>

<style>
    body {
        padding-top: 70px;
    }

    .navbar-fixed-top,
    .navbar-sticky-top {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
    }

    .container.mt-4:first-of-type {
        margin-top: 20px !important;
    }
</style>

<div class="container mt-4">
    <!-- Tarjeta para crear/editar grupos -->
    <div class="card shadow-lg p-4 border-0 mb-4">
        <h4 class="mb-4 text-primary">
            <i class="bi bi-people"></i>
            Gestionar Mis Grupos
        </h4>

        <!-- Formulario para grupo -->
        <div class="row g-3">
            <div class="col-md-3">
                <label class="fw-bold">Carrera</label>
                <select id="carrera" class="form-select">
                    <option value="">Seleccionar carrera</option>
                    <?php
                    $carreras = $conn->query("SELECT IDCarrera, Nombre FROM carreras ORDER BY Nombre");
                    while($carrera = $carreras->fetch_assoc()):
                    ?>
                        <option value="<?= $carrera['IDCarrera'] ?>"><?= $carrera['Nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="fw-bold">Semestre</label>
                <select id="semestre" class="form-select">
                    <option value="">Seleccionar</option>
                    <?php for($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?>° Semestre</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="fw-bold">Nombre del Grupo</label>
                <input type="text" id="nombreGrupo" class="form-control" placeholder="Ej: 5° A">
            </div>

            <div class="col-md-2">
                <label class="fw-bold">Cantidad Alumnos</label>
                <input type="number" id="cantidadAlumnos" class="form-control" placeholder="Ej: 30">
            </div>

            <div class="col-md-3">
                <label class="fw-bold">Tipo de Grupo</label>
                <select id="tipoGrupo" class="form-select">
                    <option value="regular">Regular</option>
                    <option value="vespertino">Vespertino</option>
                    <option value="sabado">Sábado</option>
                </select>
            </div>

            <div class="col-md-12">
                <button class="btn btn-success" id="btnGuardarGrupo">
                    <i class="bi bi-save"></i> Guardar Grupo
                </button>
                <button class="btn btn-secondary" id="btnCancelar" style="display:none;">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de grupos existentes -->
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list"></i> Mis Grupos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaGrupos">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Carrera</th>
                            <th>Semestre</th>
                            <th>Nombre Grupo</th>
                            <th>Tipo</th>
                            <th>Alumnos</th>
                            <th>Acciones</th>
                            <th>Subir Lista</th>
                            <th>Ver lista</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <!-- JS llena aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir alumnos -->
<div class="modal fade" id="modalAlumnos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upload"></i> Subir Lista de Alumnos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    El archivo Excel debe tener las columnas: Matrícula, Nombre del estudiante
                </div>
                <form id="formUploadAlumnos" enctype="multipart/form-data">
                    <input type="hidden" id="grupoIdUpload" name="grupoId">
                    <div class="mb-3">
                        <label class="form-label">Archivo Excel (.xlsx)</label>
                        <input type="file" class="form-control" name="archivoAlumnos" accept=".xlsx, .xls" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-cloud-upload"></i> Subir Alumnos
                        </button>
                    </div>
                </form>
                <div id="resultadoUpload" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver lista de alumnos -->
<div class="modal fade" id="modalVerAlumnos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill"></i> 
                    Lista de Alumnos - <span id="tituloGrupoAlumnos"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Matrícula</th>
                                <th>Nombre Completo</th>
                                <th>Plan</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAlumnosBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="bi bi-hourglass-split"></i> Cargando alumnos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/grupos_maestro.js"></script>