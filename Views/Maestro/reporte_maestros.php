<?php
include("../../includes/auth_maestro.php");
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");
include("../../includes/reportes_maestros_dise.php");

$idUsuario = $_SESSION['id'] ?? 0;

// Obtener datos del maestro
$sqlMaestro = "SELECT nombre, apellidos FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlMaestro);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$maestro = $result->fetch_assoc();
$nombreMaestro = trim($maestro['nombre'] . ' ' . ($maestro['apellidos'] ?? ''));
?>

<div class="container mt-4">

    <div class="card-modern">

        <!-- HEADER -->
        <div class="card-header-modern">
            <h4>
                <i class="bi bi-file-earmark-text"></i> Reporte de Asistencia a Prácticas
            </h4>
            <span class="badge-admin" style="background:rgba(255,255,255,0.15); color:white; padding:4px 14px; border-radius:20px; font-size:0.7rem;">
                <i class="bi bi-person"></i> <?= htmlspecialchars($nombreMaestro) ?>
            </span>
        </div>

        <!-- BODY -->
        <div class="card-body-modern">

            <!-- FILTROS -->
            <div class="row g-3">

                <!-- Grupo (solo los del maestro) -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-people"></i> Grupo <span class="required" style="color:#e63946;">*</span>
                    </label>
                    <select id="filtroGrupo" class="form-select-modern form-select">
                        <option value="">Seleccionar grupo</option>
                        <?php
                        $sqlGrupos = "
                            SELECT g.IDGrupo, g.Nombre, g.Semestre, c.Nombre AS carrera
                            FROM grupos g
                            LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
                            WHERE g.IDUsuario = ?
                            ORDER BY g.Semestre, g.Nombre
                        ";
                        $stmtGrupos = $conn->prepare($sqlGrupos);
                        $stmtGrupos->bind_param("i", $idUsuario);
                        $stmtGrupos->execute();
                        $resultGrupos = $stmtGrupos->get_result();
                        while($grupo = $resultGrupos->fetch_assoc()):
                        ?>
                        <option value="<?= $grupo['IDGrupo'] ?>">
                            <?= $grupo['carrera'] ?? 'Sin carrera' ?> - <?= $grupo['Nombre'] ?? $grupo['Semestre'] . '° Semestre' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Fecha -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar3"></i> Fecha <span class="required" style="color:#e63946;">*</span>
                    </label>
                    <input type="date" id="fechaReporte" class="form-control-modern form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Hora -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-clock"></i> Hora <span class="required" style="color:#e63946;">*</span>
                    </label>
                    <select id="horaReporte" class="form-select-modern form-select">
                        <option value="">Seleccionar hora</option>
                        <?php
                        $horas = [
                            "07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00",
                            "11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00",
                            "15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00",
                            "19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"
                        ];
                        foreach($horas as $h):
                        ?>
                        <option value="<?= $h ?>"><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <!-- Mensajes de validación de reservación -->
            <div id="reservacionOk" class="reservacion-ok">
                <i class="bi bi-check-circle-fill"></i> 
                <span id="mensajeOk">Reservación confirmada. Datos cargados automáticamente.</span>
            </div>
            <div id="reservacionError" class="reservacion-error">
                <i class="bi bi-exclamation-circle-fill"></i> 
                <span id="mensajeError">No existe una reservación para esta fecha y hora.</span>
            </div>

            <div class="row g-3 mt-2">

                <!-- Laboratorio (auto-llenado) -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-laptop"></i> Laboratorio
                    </label>
                    <input type="text" id="laboratorioReporte" class="form-control-modern form-control" readonly placeholder="Selecciona grupo, fecha y hora">
                </div>

                <!-- Práctica (auto-llenado) -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-file-text"></i> Práctica
                    </label>
                    <input type="text" id="practicaReporte" class="form-control-modern form-control" readonly placeholder="Se auto-llenará">
                </div>

                <!-- Software / Materia (auto-llenado) -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-code-square"></i> Software / Materia
                    </label>
                    <input type="text" id="softwareReporte" class="form-control-modern form-control" readonly placeholder="Se auto-llenará">
                </div>

            </div>

            <!-- BOTONES -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <button class="btn-reporte" id="btnGenerar" disabled onclick="generarReporte()">
                            <i class="bi bi-search"></i> Generar Reporte
                        </button>
                        <button class="btn-excel" id="btnExcel" disabled onclick="exportarExcel()">
                            <i class="bi bi-file-excel"></i> Exportar Excel
                        </button>
                        <button class="btn-imprimir" id="btnImprimir" disabled onclick="imprimirReporte()">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================= -->
            <!-- VISTA PREVIA DEL REPORTE -->
            <!-- ========================= -->
            <div class="reporte-preview" id="reportePreview">
                <div id="contenidoReporte">
                    <!-- Se llena con JavaScript -->
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/logout.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // =========================================================
    // ELEMENTOS DEL DOM
    // =========================================================

    const filtroGrupo = document.getElementById('filtroGrupo');
    const fechaReporte = document.getElementById('fechaReporte');
    const horaReporte = document.getElementById('horaReporte');

    const btnGenerar = document.getElementById('btnGenerar');
    const btnExcel = document.getElementById('btnExcel');
    const btnImprimir = document.getElementById('btnImprimir');

    const reservacionOk = document.getElementById('reservacionOk');
    const reservacionError = document.getElementById('reservacionError');

    const mensajeOk = document.getElementById('mensajeOk');
    const mensajeError = document.getElementById('mensajeError');

    const laboratorioInput =
        document.getElementById('laboratorioReporte');

    const practicaInput =
        document.getElementById('practicaReporte');

    const softwareInput =
        document.getElementById('softwareReporte');

    const reportePreview =
        document.getElementById('reportePreview');

    const contenidoReporte =
        document.getElementById('contenidoReporte');


    // =========================================================
    // VARIABLE PARA GUARDAR LA RESERVACIÓN
    // =========================================================

    let reservacionActual = null;


    // =========================================================
    // ESCAPAR HTML
    // =========================================================

    function escaparHTML(valor) {

        if (valor === null || valor === undefined) {
            return '';
        }

        return String(valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    // =========================================================
    // OCULTAR MENSAJES
    // =========================================================

    function ocultarMensajes() {

        reservacionOk.style.display = 'none';
        reservacionError.style.display = 'none';

    }


    // =========================================================
    // LIMPIAR DATOS
    // =========================================================

    function limpiarDatosReservacion() {

        laboratorioInput.value = '';
        practicaInput.value = '';
        softwareInput.value = '';

        reservacionActual = null;

        btnGenerar.dataset.reserva = '';

    }


    // =========================================================
    // DESHABILITAR BOTONES
    // =========================================================

    function deshabilitarBotones() {

        btnGenerar.disabled = true;
        btnExcel.disabled = true;
        btnImprimir.disabled = true;

    }


    // =========================================================
    // HABILITAR BOTONES
    // =========================================================

    function habilitarBotones() {

        btnGenerar.disabled = false;
        btnExcel.disabled = false;

        // Imprimir solamente después de generar
        btnImprimir.disabled = true;

    }


    // =========================================================
    // VERIFICAR CAMPOS
    // =========================================================

    function verificarCampos() {

        const grupoVal = filtroGrupo.value;
        const fechaVal = fechaReporte.value;
        const horaVal = horaReporte.value;

        if (!grupoVal || !fechaVal || !horaVal) {

            deshabilitarBotones();

            ocultarMensajes();

            limpiarDatosReservacion();

            reportePreview.style.display = 'none';

            return;
        }

        buscarReservacion(
            grupoVal,
            fechaVal,
            horaVal
        );

    }


    // =========================================================
    // BUSCAR RESERVACIÓN
    // =========================================================

    function buscarReservacion(idGrupo, fecha, hora) {

        reservacionOk.style.display = 'none';
        reservacionError.style.display = 'none';

        laboratorioInput.value = 'Buscando...';
        practicaInput.value = 'Buscando...';
        softwareInput.value = 'Buscando...';

        deshabilitarBotones();

        // Obtener únicamente la hora inicial
        const [horaInicio = '', horaFin = ''] = hora
            .split('-')
            .map(valor => valor.trim());


        // URL
        const url =
            `/SistemaApartadosITAP/controllers/obtener_datos_reporte_maestro.php` +
            `?grupo=${encodeURIComponent(idGrupo)}` +
            `&fecha=${encodeURIComponent(fecha)}` +
            `&hora=${encodeURIComponent(horaInicio)}` +
            `&horaFin=${encodeURIComponent(horaFin)}`;


        console.log("================================");
        console.log("BUSCANDO RESERVACIÓN");
        console.log("Grupo:", idGrupo);
        console.log("Fecha:", fecha);
        console.log("Hora:", horaInicio);
        console.log("Hora fin:", horaFin);
        console.log("URL:", url);
        console.log("================================");


        fetch(url)

            .then(response => {

                console.log(
                    "HTTP:",
                    response.status,
                    response.statusText
                );

                if (!response.ok) {

                    throw new Error(
                        `Error HTTP ${response.status}: ${response.statusText}`
                    );

                }

                return response.text();

            })


            .then(text => {

                console.log("================================");
                console.log("RESPUESTA DEL SERVIDOR:");
                console.log(text);
                console.log("================================");

                let data;

                try {

                    data = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "La respuesta del servidor NO es JSON válido."
                    );

                    console.error(
                        "Respuesta recibida:",
                        text
                    );

                    throw new Error(
                        "El servidor no devolvió JSON válido."
                    );

                }


                console.log("JSON recibido:", data);


                // =================================================
                // ERROR DEVUELTO POR PHP
                // =================================================

                if (data.error) {

                    reservacionActual = null;

                    btnGenerar.dataset.reserva = '';

                    laboratorioInput.value = '';
                    practicaInput.value = '';
                    softwareInput.value = '';

                    reservacionOk.style.display = 'none';

                    reservacionError.style.display = 'block';

                    mensajeError.innerText =
                        data.error;

                    deshabilitarBotones();

                    return;

                }


                // =================================================
                // VALIDAR DATA
                // =================================================

                if (!data.data) {

                    throw new Error(
                        "La respuesta no contiene el objeto 'data'."
                    );

                }


                // =================================================
                // RESERVACIÓN ENCONTRADA
                // =================================================

                const r = data.data;

                reservacionActual = r;

                btnGenerar.dataset.reserva =
                    JSON.stringify(r);


                console.log(
                    "Reservación encontrada:",
                    r
                );


                // =================================================
                // MOSTRAR MENSAJE
                // =================================================

                reservacionError.style.display = 'none';
                reservacionOk.style.display = 'block';

                mensajeOk.innerText =
                    'Reservación confirmada. Datos cargados automáticamente.';


                // =================================================
                // LABORATORIO
                // =================================================

                laboratorioInput.value =
                    r.laboratorio &&
                    String(r.laboratorio).trim() !== ''

                        ? r.laboratorio

                        : 'No especificado';


                // =================================================
                // PRÁCTICA
                // =================================================

                practicaInput.value =
                    r.Practica &&
                    String(r.Practica).trim() !== ''

                        ? r.Practica

                        : 'Sin especificar';


                // =================================================
                // SOFTWARE
                // =================================================

                softwareInput.value =
                    r.Software &&
                    String(r.Software).trim() !== ''

                        ? r.Software

                        : 'Sin especificar';


                // =================================================
                // MOSTRAR CANTIDAD DE ALUMNOS EN CONSOLA
                // =================================================

                if (Array.isArray(r.listaAlumnos)) {

                    console.log(
                        "Total de alumnos:",
                        r.Alumnos.length
                    );

                } else {

                    console.warn(
                        "r.Alumnos no es un arreglo:",
                        r.Alumnos
                    );

                }


                // =================================================
                // HABILITAR BOTONES
                // =================================================

                habilitarBotones();

            })


            // =====================================================
            // ERROR
            // =====================================================

            .catch(error => {

                console.error(
                    "Error buscando reservación:",
                    error
                );

                reservacionActual = null;

                btnGenerar.dataset.reserva = '';

                laboratorioInput.value = '';
                practicaInput.value = '';
                softwareInput.value = '';

                reservacionOk.style.display = 'none';

                reservacionError.style.display = 'block';

                mensajeError.innerText =
                    error.message ||
                    "Error al consultar la reservación.";

                deshabilitarBotones();

            });

    }


   // =========================================================
// OBTENER ALUMNOS
// =========================================================

function obtenerAlumnos(reservacion) {

    // Si no existe la reservación,
    // regresamos un arreglo vacío.
    if (!reservacion) {
        return [];
    }

    if (Array.isArray(reservacion.listaAlumnos)) {

        return reservacion.listaAlumnos;

    }


    if (Array.isArray(reservacion.Alumnos)) {

        return reservacion.Alumnos;

    }


    // =====================================================
    // SI listaAlumnos LLEGA COMO TEXTO JSON
    // =====================================================

    if (typeof reservacion.listaAlumnos === 'string') {

        try {

            const alumnosParseados =
                JSON.parse(reservacion.listaAlumnos);

            if (Array.isArray(alumnosParseados)) {

                return alumnosParseados;

            }

        } catch (error) {

            console.warn(
                "No se pudo convertir listaAlumnos a arreglo.",
                error
            );

        }
    }


    // =====================================================
    // SI Alumnos LLEGA COMO TEXTO JSON
    // =====================================================

    if (typeof reservacion.Alumnos === 'string') {

        try {

            const alumnosParseados =
                JSON.parse(reservacion.Alumnos);

            if (Array.isArray(alumnosParseados)) {

                return alumnosParseados;

            }

        } catch (error) {

            console.warn(
                "No se pudo convertir Alumnos a arreglo.",
                error
            );

        }
    }


    // =====================================================
    // NO SE ENCONTRARON ALUMNOS
    // =====================================================

    return [];
}

    // =========================================================
// OBTENER NÚMERO DE CONTROL
// =========================================================

function obtenerNumeroControl(alumno) {

    if (!alumno) {
        return '';
    }

    return (

        alumno.NoControl ??

        alumno.numeroControl ??

        alumno.NumeroControl ??

        alumno.numControl ??

        alumno.NumControl ??

        alumno.nControl ??

        alumno.NControl ??

        alumno.control ??

        alumno.Control ??

        ''

    );
}


    // =========================================================
    // OBTENER NOMBRE DEL ALUMNO
    // =========================================================

    function obtenerNombreAlumno(alumno) {

        if (!alumno) {
            return '';
        }

        return (
            alumno.nombre ??
            alumno.Nombre ??
            alumno.nombreAlumno ??
            alumno.NombreAlumno ??
            alumno.alumno ??
            alumno.Alumno ??
            ''
        );

    }

    function generarFilasAlumnos(alumnos) {

        let filas = '';


        if (!Array.isArray(alumnos)) {

            alumnos = [];

        }


        // =====================================================
        // TODOS LOS ALUMNOS
        // =====================================================

        alumnos.forEach((alumno, index) => {

            const numeroControl =
                obtenerNumeroControl(alumno);

            const nombre =
                obtenerNombreAlumno(alumno);


            filas += `

                <tr>

                    <td style="
                        border:1px solid #1d3557 !important;
                        padding:7px;
                        text-align:center;
                        width:8%;
                    ">
                        ${index + 1}
                    </td>


                    <td style="
                        border:1px solid #1d3557 !important;
                        padding:7px;
                        width:25%;
                    ">
                        ${escaparHTML(numeroControl)}
                    </td>


                    <td style="
                        border:1px solid #1d3557 !important;
                        padding:7px;
                        width:42%;
                    ">
                        ${escaparHTML(nombre)}
                    </td>


                    <td style="
                        border:1px solid #1d3557 !important;
                        padding:7px;
                        width:25%;
                        height:32px;
                    ">
                    </td>

                </tr>

            `;

        });


        // =====================================================
        // SI NO HAY ALUMNOS
        // =====================================================

        if (alumnos.length === 0) {

            filas = `

                <tr>

                    <td
                        colspan="4"
                        style="
                            text-align:center;
                            padding:20px;
                            color:#777;
                        "
                    >
                        No hay alumnos registrados en este grupo.
                    </td>

                </tr>

            `;

        }


        return filas;

    }


    // =========================================================
    // GENERAR REPORTE
    // =========================================================

    window.generarReporte = function () {

        let r = reservacionActual;


        // Si no existe, intentar obtenerla del dataset
        if (!r && btnGenerar.dataset.reserva) {

            try {

                r = JSON.parse(
                    btnGenerar.dataset.reserva
                );

            } catch (error) {

                console.error(
                    "Error leyendo la reservación:",
                    error
                );

                Swal.fire(
                    "Error",
                    "No se pudieron leer los datos de la reservación.",
                    "error"
                );

                return;

            }

        }


        // =====================================================
        // VALIDAR
        // =====================================================

        if (!r) {

            Swal.fire(
                "Aviso",
                "Primero selecciona una reservación válida.",
                "warning"
            );

            return;

        }


        // =====================================================
        // GRUPO
        // =====================================================

        const grupoSelect =
            document.getElementById('filtroGrupo');

        const grupoNombre =
            grupoSelect.selectedOptions[0]
                ? grupoSelect.selectedOptions[0].text.trim()
                : 'No especificado';


        // =====================================================
        // FECHA
        // =====================================================

        const fechaFormat =
            r.fecha
                ? new Date(
                    r.fecha + 'T00:00:00'
                ).toLocaleDateString('es-MX')
                : 'No especificada';


        // =====================================================
        // HORA
        // =====================================================

        const rangoSeleccionado = horaReporte.value.split('-').map(hora => hora.trim());
        const horaInicio = r.horaInicio || rangoSeleccionado[0] || '';
        const horaFin = r.horaFin || rangoSeleccionado[1] || '';
        const horaCompleta = `${horaInicio} - ${horaFin}`;


        // =====================================================
        // DATOS
        // =====================================================

        const laboratorio =
            r.laboratorio || 'No especificado';

        const departamento =
            r.departamento || 'Sin asignar';


        const practica =
            r.Practica &&
            String(r.Practica).trim() !== ''

                ? r.Practica

                : 'Sin especificar';


        const software =
            r.Software &&
            String(r.Software).trim() !== ''

                ? r.Software

                : 'Sin especificar';


        // =====================================================
        // OBTENER TODOS LOS ALUMNOS
        // =====================================================

        const alumnos =
            obtenerAlumnos(r);


        console.log(
            "================================"
        );

        console.log(
            "ALUMNOS DEL REPORTE:"
        );

        console.log(
            "Total:",
            alumnos.length
        );

        console.log(
            alumnos
        );

        console.log(
            "================================"
        );


        // =====================================================
        // GENERAR FILAS
        // =====================================================

        const filasAlumnos =
            generarFilasAlumnos(alumnos);


        // =====================================================
        // GENERAR HTML
        // =====================================================

        contenidoReporte.innerHTML = `

            <!-- =========================================
                 ENCABEZADO
            ========================================== -->

            <div
                style="
                    text-align:center;
                    margin-bottom:20px;
                "
            >

                <div
                    style="
                        font-size:1.15rem;
                        font-weight:700;
                    "
                >
                    INSTITUTO TECNOLÓGICO DE AGUA PRIETA
                </div>


                <div
                    style="
                        font-size:0.95rem;
                        font-weight:600;
                        margin-top:8px;
                    "
                >
                    Departamento De
                    ${escaparHTML(departamento)}
                </div>


                <div
                    style="
                        font-size:1rem;
                        font-weight:700;
                        margin-top:5px;
                    "
                >
                    CONTROL DE ASISTENCIA A PRÁCTICAS
                </div>

            </div>


            <!-- =========================================
                 INFORMACIÓN DE LA RESERVACIÓN
            ========================================== -->

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    margin-bottom:15px;
                "
            >

                <tbody>

                    <tr>

                        <td
                            style="
                                width:50%;
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Laboratorio:</strong>
                            ${escaparHTML(laboratorio)}
                        </td>


                        <td
                            style="
                                width:50%;
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Carrera:</strong>
                            ${escaparHTML(grupoNombre)}
                        </td>

                    </tr>


                    <tr>

                        <td
                            style="
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Nombre del Maestro(a):</strong>
                            <?= htmlspecialchars($nombreMaestro) ?>
                        </td>


                        <td
                            style="
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Materia:</strong>
                            ${escaparHTML(software)}
                        </td>

                    </tr>


                    <tr>

                        <td
                            colspan="2"
                            style="
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Nombre de la práctica:</strong>
                            ${escaparHTML(practica)}
                        </td>

                    </tr>


                    <tr>

                        <td
                            style="
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Grupo:</strong>
                            ${escaparHTML(grupoNombre)}
                        </td>


                        <td
                            style="
                                border:1px solid #1d3557;
                                padding:8px;
                            "
                        >
                            <strong>Fecha:</strong>
                            ${escaparHTML(fechaFormat)}

                            &nbsp;&nbsp;

                            <strong>Hora:</strong>
                            ${escaparHTML(horaCompleta)}
                        </td>

                    </tr>

                </tbody>

            </table>


            <!-- =========================================
                 TABLA DE ALUMNOS
            ========================================== -->

            <table
                class="tabla-alumnos"
                border="1"
                cellspacing="0"
                style="
                    width:100%;
                    border-collapse:collapse;
                    font-size:0.85rem;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                border:1px solid #1d3557;
                                padding:7px;
                                background:#e8f0f5;
                                text-align:center;
                                width:8%;
                            "
                        >
                            No.
                        </th>


                        <th
                            style="
                                border:1px solid #1d3557;
                                padding:7px;
                                background:#e8f0f5;
                                text-align:center;
                                width:25%;
                            "
                        >
                            Núm. de control
                        </th>


                        <th
                            style="
                                border:1px solid #1d3557;
                                padding:7px;
                                background:#e8f0f5;
                                text-align:center;
                                width:42%;
                            "
                        >
                            Nombre del alumno(a)
                        </th>


                        <th
                            style="
                                border:1px solid #1d3557;
                                padding:7px;
                                background:#e8f0f5;
                                text-align:center;
                                width:25%;
                            "
                        >
                            Firma
                        </th>

                    </tr>

                </thead>


                <tbody>

                    ${filasAlumnos}

                </tbody>

            </table>


            <!-- =========================================
                 FIRMA DEL MAESTRO
            ========================================== -->

            <div
                style="
                    margin-top:60px;
                    text-align:center;
                "
            >

                <div
                    style="
                        display:inline-block;
                        width:280px;
                        border-top:1px solid #1d3557;
                        padding-top:8px;
                    "
                >

                    Nombre y firma del maestro(a)

                </div>

            </div>


            <!-- =========================================
                 PIE DEL REPORTE
            ========================================== -->

            <div
                style="
                    text-align:center;
                    margin-top:25px;
                    font-size:0.75rem;
                    color:#757474;
                "
            >

                Reporte generado por el sistema
                de apartados ITAP el

                ${new Date().toLocaleDateString('es-MX')}

                a las

                ${new Date().toLocaleTimeString('es-MX')}

            </div>

        `;


        // =====================================================
        // MOSTRAR PREVIEW
        // =====================================================

        reportePreview.style.display = 'block';


        // =====================================================
        // HABILITAR IMPRIMIR
        // =====================================================

        btnImprimir.disabled = false;


        // =====================================================
        // SCROLL
        // =====================================================

        reportePreview.scrollIntoView({

            behavior: 'smooth',

            block: 'start'

        });

    };


    // =========================================================
    // IMPRIMIR REPORTE
    // =========================================================

    window.imprimirReporte = function () {

        if (
            reportePreview.style.display === 'none' ||
            !reportePreview.innerHTML.trim()
        ) {

            Swal.fire(
                "Aviso",
                "Primero genera el reporte.",
                "warning"
            );

            return;

        }

        window.print();

    };


    // =========================================================
    // EXPORTAR EXCEL
    // =========================================================

    window.exportarExcel = function () {

        let r = reservacionActual;


        if (!r && btnGenerar.dataset.reserva) {

            try {

                r = JSON.parse(
                    btnGenerar.dataset.reserva
                );

            } catch (error) {

                console.error(
                    "Error leyendo la reservación:",
                    error
                );

                Swal.fire(
                    "Error",
                    "No se pudieron leer los datos.",
                    "error"
                );

                return;

            }

        }


        // =====================================================
        // VALIDAR
        // =====================================================

        if (!r) {

            Swal.fire(
                "Aviso",
                "Primero selecciona una reservación válida.",
                "warning"
            );

            return;

        }


        // =====================================================
        // GRUPO
        // =====================================================

        const grupoSelect =
            document.getElementById('filtroGrupo');

        const grupoNombre =
            grupoSelect.selectedOptions[0]
                ? grupoSelect.selectedOptions[0].text.trim()
                : 'No especificado';


        // =====================================================
        // FECHA
        // =====================================================

        const fechaFormat =
            r.fecha
                ? new Date(
                    r.fecha + 'T00:00:00'
                ).toLocaleDateString('es-MX')
                : 'No especificada';


        // =====================================================
        // HORA
        // =====================================================

        const rangoSeleccionado = horaReporte.value.split('-').map(hora => hora.trim());
        const horaInicio = r.horaInicio || rangoSeleccionado[0] || '';
        const horaFin = r.horaFin || rangoSeleccionado[1] || '';
        const horaCompleta = `${horaInicio} - ${horaFin}`;


        // =====================================================
        // DATOS
        // =====================================================

        const laboratorio =
            r.laboratorio || 'No especificado';

        const departamento =
            r.departamento || 'Sin asignar';


        const practica =
            r.Practica &&
            String(r.Practica).trim() !== ''

                ? r.Practica

                : 'Sin especificar';


        const software =
            r.Software &&
            String(r.Software).trim() !== ''

                ? r.Software

                : 'Sin especificar';


        // =====================================================
        // TODOS LOS ALUMNOS
        // =====================================================

        const alumnos =
            obtenerAlumnos(r);


        console.log(
            "Exportando alumnos:",
            alumnos.length
        );


        // =====================================================
        // FILAS PARA EXCEL
        // =====================================================

        let filasExcel = '';


        alumnos.forEach((alumno, index) => {

            const numeroControl =
                obtenerNumeroControl(alumno);

            const nombre =
                obtenerNombreAlumno(alumno);


            filasExcel += `

                <tr>

                    <td
                        style="
                            text-align:center;
                            border:1px solid #000;
                        "
                    >
                        ${index + 1}
                    </td>


                    <td
                        style="
                            border:1px solid #000;
                        "
                    >
                        ${escaparHTML(numeroControl)}
                    </td>


                    <td
                        style="
                            border:1px solid #000;
                        "
                    >
                        ${escaparHTML(nombre)}
                    </td>


                    <td
                        style="
                            border:1px solid #000;
                            height:35px;
                        "
                    >
                    </td>

                </tr>

            `;

        });


        // =====================================================
        // SI NO HAY ALUMNOS
        // =====================================================

        if (alumnos.length === 0) {

            filasExcel = `

                <tr>

                    <td
                        colspan="4"
                        style="
                            text-align:center;
                            border:1px solid #000;
                            padding:15px;
                        "
                    >
                        No hay alumnos registrados en este grupo.
                    </td>

                </tr>

            `;

        }


        // =====================================================
        // HTML PARA EXCEL
        // =====================================================

        const html = `

<html>

<head>

    <meta charset="UTF-8">

    <title>
        Reporte Asistencia
    </title>

</head>


<body>


    <h2 style="text-align:center;">

        INSTITUTO TECNOLÓGICO DE AGUA PRIETA

    </h2>


    <h3 style="text-align:center;">

        DEPARTAMENTO DE ${escaparHTML(departamento)}

    </h3>


    <h3 style="text-align:center;">

        CONTROL DE ASISTENCIA A PRÁCTICAS

    </h3>


    <br>


    <table
        border="1"
        cellpadding="6"
        cellspacing="0"
        style="
            width:100%;
            border-collapse:collapse;
        "
    >

        <tr>

            <th>
                Laboratorio
            </th>

            <td>
                ${escaparHTML(laboratorio)}
            </td>


            <th>
                Carrera / Grupo
            </th>

            <td>
                ${escaparHTML(grupoNombre)}
            </td>

        </tr>


        <tr>

            <th>
                Nombre del Maestro(a)
            </th>

            <td>
                <?= htmlspecialchars($nombreMaestro) ?>
            </td>


            <th>
                Materia
            </th>

            <td>
                ${escaparHTML(software)}
            </td>

        </tr>


        <tr>

            <th>
                Nombre de la práctica
            </th>

            <td colspan="3">
                ${escaparHTML(practica)}
            </td>

        </tr>


        <tr>

            <th>
                Fecha
            </th>

            <td>
                ${escaparHTML(fechaFormat)}
            </td>


            <th>
                Hora
            </th>

            <td>
                ${escaparHTML(horaCompleta)}
            </td>

        </tr>

    </table>


    <br>


    <table
        border="1"
        cellpadding="6"
        cellspacing="0"
        style="
            width:100%;
            border-collapse:collapse;
        "
    >

        <thead>

            <tr>

                <th>
                    No.
                </th>

                <th>
                    Núm. de control
                </th>

                <th>
                    Nombre del alumno(a)
                </th>

                <th>
                    Firma
                </th>

            </tr>

        </thead>


        <tbody>

            ${filasExcel}

        </tbody>

    </table>


    <br><br><br>


    <div style="text-align:center;">

        ________________________________

        <br>

        Nombre y firma del maestro(a)

    </div>


    <br>


    <p style="text-align:center;">

        Generado el
        ${new Date().toLocaleString('es-MX')}

    </p>


</body>

</html>

`;


        // =====================================================
        // CREAR ARCHIVO
        // =====================================================

        const blob = new Blob(
            [html],
            {
                type:
                    'application/vnd.ms-excel;charset=utf-8;'
            }
        );


        const url =
            URL.createObjectURL(blob);


        const a =
            document.createElement('a');


        a.href = url;


        a.download =
            `Reporte_Asistencia_${r.fecha}.xls`;


        document.body.appendChild(a);

        a.click();

        document.body.removeChild(a);


        URL.revokeObjectURL(url);

    };


    // =========================================================
    // EVENTOS
    // =========================================================

    filtroGrupo.addEventListener(
        'change',
        verificarCampos
    );


    fechaReporte.addEventListener(
        'change',
        verificarCampos
    );


    horaReporte.addEventListener(
        'change',
        verificarCampos
    );


    // =========================================================
    // ESTADO INICIAL
    // =========================================================

    deshabilitarBotones();

    ocultarMensajes();

    limpiarDatosReservacion();

    reportePreview.style.display = 'none';


    // =========================================================
    // VERIFICAR AL CARGAR
    // =========================================================

    verificarCampos();

});
</script>

</body>
</html>
