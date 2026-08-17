console.log("JS de grupos cargado");

let editando = false;
let idGrupoEditando = null;


// ============================================================
// DOM READY
// ============================================================

document.addEventListener("DOMContentLoaded", function () {

    console.log("DOM listo");

    cargarGrupos();

    const cantidadAlumnos = document.getElementById("cantidadAlumnos");

    if (cantidadAlumnos) {
        cantidadAlumnos.addEventListener("keydown", function (event) {
            if (["-", "+", "e", "E", ".", ","].includes(event.key)) {
                event.preventDefault();
            }
        });

        cantidadAlumnos.addEventListener("input", function () {
            if (this.value !== "" && (!/^\d+$/.test(this.value) || Number(this.value) < 0)) {
                this.value = "";
            }
        });
    }

    // --------------------------------------------------------
    // BOTÓN GUARDAR
    // --------------------------------------------------------

    const btnGuardar = document.getElementById("btnGuardarGrupo");

    if (btnGuardar) {
        btnGuardar.addEventListener("click", guardarGrupo);
        console.log("Evento guardar asignado");
    } else {
        console.error("No se encontró #btnGuardarGrupo");
    }

    // --------------------------------------------------------
    // BOTÓN CANCELAR
    // --------------------------------------------------------

    const btnCancelar = document.getElementById("btnCancelar");

    if (btnCancelar) {
        btnCancelar.addEventListener("click", cancelarEdicion);
    }

    // --------------------------------------------------------
    // FORMULARIO SUBIR ALUMNOS
    // --------------------------------------------------------

    const formUpload = document.getElementById("formUploadAlumnos");

    if (formUpload) {
        formUpload.addEventListener("submit", subirAlumnos);
        console.log("Evento subir alumnos asignado");
    }

});


// ============================================================
// FUNCIÓN AUXILIAR PARA LEER RESPUESTAS PHP
// ============================================================

async function obtenerJSON(response, nombreControlador = "PHP") {

    const texto = await response.text();

    console.log("================================");
    console.log("HTTP:", response.status);
    console.log("OK:", response.ok);
    console.log("RESPUESTA DE " + nombreControlador + ":");
    console.log(texto);
    console.log("================================");

    try {

        return JSON.parse(texto);

    } catch (error) {

        console.error(
            "NO ES JSON:",
            texto
        );

        throw new Error(
            nombreControlador +
            " no está devolviendo JSON válido. Revisa la consola."
        );
    }
}


// ============================================================
// CARGAR GRUPOS
// ============================================================

function cargarGrupos() {

    console.log("================================");
    console.log("CARGANDO GRUPOS");
    console.log("================================");

    fetch(
        "/SistemaApartadosITAP/controllers/obtener_grupos_maestro.php",
        {
            method: "GET",
            headers: {
                "Accept": "application/json"
            },
            cache: "no-store"
        }
    )

    .then(response => {

        return obtenerJSON(
            response,
            "obtener_grupos_maestro.php"
        );

    })

    .then(data => {

        console.log("Datos recibidos:", data);

        // ----------------------------------------------------
        // ERROR DEVUELTO POR PHP
        // ----------------------------------------------------

        if (data && data.error) {

            console.error(
                "Error del servidor:",
                data.error
            );

            Swal.fire(
                "Error",
                data.error,
                "error"
            );

            return;
        }

        // ----------------------------------------------------
        // VALIDAR QUE SEA ARRAY
        // ----------------------------------------------------

        if (!Array.isArray(data)) {

            console.error(
                "La respuesta no es un arreglo:",
                data
            );

            Swal.fire(
                "Error",
                "El servidor devolvió un formato incorrecto.",
                "error"
            );

            return;
        }

        // ----------------------------------------------------
        // CONSTRUIR TABLA
        // ----------------------------------------------------

        let html = "";

        if (data.length === 0) {

            html = `
                <tr>
                    <td colspan="10" class="text-center">
                        No tienes grupos registrados
                    </td>
                </tr>
            `;

        } else {

            data.forEach(grupo => {

                const nombreGrupo =
                    grupo.Nombre ||
                    (
                        grupo.Semestre
                            ? grupo.Semestre + "° Semestre"
                            : "Sin nombre"
                    );

                const carrera =
                    grupo.Carrera ||
                    "N/A";

                const tituloGrupo =
                    `${carrera} - ${nombreGrupo}`;

                // ------------------------------------------------
                // Evitar problemas con comillas en onclick
                // ------------------------------------------------

                const tituloSeguro =
                    String(tituloGrupo)
                        .replace(/\\/g, "\\\\")
                        .replace(/'/g, "\\'")
                        .replace(/"/g, "&quot;");

                const cantidad =
                    parseInt(grupo.cantidadAlumnos) || 0;

                html += `
                    <tr>

                        <td>
                            ${escapeHTML(carrera)}
                        </td>

                        <td>
                            ${grupo.Semestre || "-"}°
                        </td>

                        <td>
                            ${escapeHTML(grupo.Periodo || "-")}
                        </td>

                        <td>
                            ${grupo.Anio || "-"}
                        </td>

                        <td>
                            ${escapeHTML(nombreGrupo)}
                        </td>

                        <td>
                            ${escapeHTML(grupo.tipoGrupo || "regular")}
                        </td>

                        <td>
                            ${cantidad}
                        </td>

                        <td>

                            <button
                                class="btn btn-warning btn-sm"
                                onclick="editarGrupo(${grupo.IDGrupo})"
                            >
                                <i class="bi bi-pencil"></i>
                                Editar
                            </button>

                            <button
                                class="btn btn-danger btn-sm"
                                onclick="eliminarGrupo(${grupo.IDGrupo})"
                            >
                                <i class="bi bi-trash"></i>
                                Eliminar
                            </button>

                        </td>

                        <td>

                            <button
                                class="btn btn-info btn-sm"
                                onclick="abrirModalAlumnos(${grupo.IDGrupo}, '${tituloSeguro}')"
                            >
                                <i class="bi bi-upload"></i>
                                Subir Alumnos
                            </button>

                        </td>

                        <td>

                            <button
                                class="btn btn-primary btn-sm"
                                onclick="verListaAlumnos(${grupo.IDGrupo}, '${tituloSeguro}')"
                            >
                                <i class="bi bi-eye"></i>
                                Ver Lista
                            </button>

                        </td>

                    </tr>
                `;
            });
        }

        const tbody =
            document.querySelector("#tablaGrupos tbody");

        if (tbody) {

            tbody.innerHTML = html;

        } else {

            console.error(
                "No se encontró #tablaGrupos tbody"
            );
        }

    })

    .catch(error => {

        console.error(
            "================================"
        );

        console.error(
            "ERROR AL CARGAR GRUPOS:",
            error
        );

        console.error(
            "================================"
        );

        Swal.fire(
            "Error",
            error.message ||
            "No se pudieron cargar los grupos.",
            "error"
        );
    });
}


// ============================================================
// GUARDAR / ACTUALIZAR GRUPO
// ============================================================

function guardarGrupo() {

    console.log("================================");
    console.log("GUARDANDO GRUPO");
    console.log("================================");

    // --------------------------------------------------------
    // OBTENER CAMPOS
    // --------------------------------------------------------

    const carreraElement =
        document.getElementById("carrera");

    const semestreElement =
        document.getElementById("semestre");

    const periodoElement =
        document.getElementById("periodo");

    const anioElement =
        document.getElementById("anio");

    const nombreElement =
        document.getElementById("nombreGrupo");

    const tipoElement =
        document.getElementById("tipoGrupo");

    // --------------------------------------------------------
    // VALIDAR EXISTENCIA DE CAMPOS
    // --------------------------------------------------------

    if (
        !carreraElement ||
        !semestreElement ||
        !periodoElement ||
        !anioElement ||
        !nombreElement ||
        !tipoElement
    ) {

        console.error(
            "No se encontraron todos los campos del formulario."
        );

        Swal.fire(
            "Error",
            "No se encontraron todos los campos del formulario.",
            "error"
        );

        return;
    }

    // --------------------------------------------------------
    // OBTENER VALORES
    // --------------------------------------------------------

    const carrera =
        carreraElement.value;

    const semestre =
        semestreElement.value;

    const periodo =
        periodoElement.value;

    const anio =
        anioElement.value;

    const nombreGrupo =
        nombreElement.value.trim();

    const tipoGrupo =
        tipoElement.value;

    // --------------------------------------------------------
    // VALIDACIONES
    // --------------------------------------------------------

    if (
        !carrera ||
        !semestre ||
        !periodo ||
        !anio ||
        !nombreGrupo
    ) {

        Swal.fire(
            "Error",
            "Completa todos los campos obligatorios.",
            "error"
        );

        return;
    }

    const periodosValidos = ["Enero - Junio", "Agosto - Diciembre", "Verano"];
    const tiposValidos = ["regular", "vespertino", "sabado"];
    const anioNumero = Number(anio);
    const semestreNumero = Number(semestre);

    if (
        !Number.isInteger(semestreNumero) || semestreNumero < 1 || semestreNumero > 12 ||
        !Number.isInteger(anioNumero) || anioNumero < 2000 || anioNumero > 2100 ||
        !periodosValidos.includes(periodo) ||
        !tiposValidos.includes(tipoGrupo)
    ) {
        Swal.fire("Error", "Revisa los datos seleccionados del grupo.", "error");
        return;
    }

    // --------------------------------------------------------
    // DATOS
    //
    // IMPORTANTE:
    // cantidadAlumnos YA NO se manda.
    //
    // La cantidad se calcula directamente desde alumnos
    // mediante COUNT(a.NoControl).
    // --------------------------------------------------------

    const datos = {

        IDGrupo:
            idGrupoEditando || 0,

        IDCarrera:
            parseInt(carrera),

        Semestre:
            semestreNumero,

        Periodo:
            periodo,

        Anio:
            anioNumero,

        Nombre:
            nombreGrupo,

        tipoGrupo:
            tipoGrupo

    };

    console.log("================================");
    console.log("DATOS QUE SE VAN A GUARDAR:");
    console.log(datos);
    console.log("================================");

    // --------------------------------------------------------
    // PETICIÓN
    // --------------------------------------------------------

    fetch(
        "/SistemaApartadosITAP/controllers/guardar_grupo_maestro.php",
        {
            method: "POST",

            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },

            body: JSON.stringify(datos)
        }
    )

    .then(response => {

        return obtenerJSON(
            response,
            "guardar_grupo_maestro.php"
        );

    })

    .then(data => {

        console.log(
            "Respuesta procesada:",
            data
        );

        // ----------------------------------------------------
        // ERROR
        // ----------------------------------------------------

        if (data && data.error) {

            Swal.fire(
                "Error",
                data.error,
                "error"
            );

            return;
        }

        // ----------------------------------------------------
        // ÉXITO
        // ----------------------------------------------------

        Swal.fire(
            "Éxito",
            data.mensaje ||
            "Grupo guardado correctamente.",
            "success"
        );

        cancelarEdicion();

        cargarGrupos();

    })

    .catch(error => {

        console.error(
            "ERROR AL GUARDAR GRUPO:",
            error
        );

        Swal.fire(
            "Error",
            error.message ||
            "Error al guardar el grupo.",
            "error"
        );
    });
}


// ============================================================
// EDITAR GRUPO
// ============================================================

function editarGrupo(id) {

    console.log(
        "Editando grupo:",
        id
    );

    fetch(
        `/SistemaApartadosITAP/controllers/obtener_grupo.php?id=${encodeURIComponent(id)}`,
        {
            method: "GET",
            headers: {
                "Accept": "application/json"
            },
            cache: "no-store"
        }
    )

    .then(response => {

        return obtenerJSON(
            response,
            "obtener_grupo.php"
        );

    })

    .then(grupo => {

        console.log(
            "Grupo a editar:",
            grupo
        );

        // ----------------------------------------------------
        // ERROR
        // ----------------------------------------------------

        if (grupo.error) {

            Swal.fire(
                "Error",
                grupo.error,
                "error"
            );

            return;
        }

        // ----------------------------------------------------
        // CARGAR DATOS
        // ----------------------------------------------------

        document.getElementById("carrera").value =
            grupo.IDCarrera || "";

        document.getElementById("semestre").value =
            grupo.Semestre || "";

        document.getElementById("periodo").value =
            grupo.Periodo || "";

        document.getElementById("anio").value =
            grupo.Anio || "";

        const cantidadElement =
            document.getElementById("cantidadAlumnos");

        if (cantidadElement) {

            cantidadElement.value =
                grupo.cantidadAlumnos || 0;

            // La cantidad es informativa.
            cantidadElement.readOnly = true;
        }

        document.getElementById("nombreGrupo").value =
            grupo.Nombre || "";

        document.getElementById("tipoGrupo").value =
            grupo.tipoGrupo || "regular";

        // ----------------------------------------------------
        // ESTADO EDICIÓN
        // ----------------------------------------------------

        editando = true;

        idGrupoEditando = parseInt(id);

        // ----------------------------------------------------
        // CAMBIAR BOTONES
        // ----------------------------------------------------

        const btnGuardar =
            document.getElementById("btnGuardarGrupo");

        if (btnGuardar) {

            btnGuardar.innerHTML =
                '<i class="bi bi-pencil"></i> Actualizar';
        }

        const btnCancelar =
            document.getElementById("btnCancelar");

        if (btnCancelar) {

            btnCancelar.style.display =
                "inline-block";
        }

        // ----------------------------------------------------
        // SCROLL AL FORMULARIO
        // ----------------------------------------------------

        const formulario =
            document.getElementById("btnGuardarGrupo");

        if (formulario) {

            formulario.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }

    })

    .catch(error => {

        console.error(
            "ERROR AL CARGAR GRUPO:",
            error
        );

        Swal.fire(
            "Error",
            error.message ||
            "No se pudo cargar el grupo.",
            "error"
        );
    });
}


// ============================================================
// CANCELAR EDICIÓN
// ============================================================

function cancelarEdicion() {

    console.log(
        "Cancelando edición"
    );

    const carrera =
        document.getElementById("carrera");

    const semestre =
        document.getElementById("semestre");

    const periodo =
        document.getElementById("periodo");

    const anio =
        document.getElementById("anio");

    const cantidad =
        document.getElementById("cantidadAlumnos");

    const nombre =
        document.getElementById("nombreGrupo");

    const tipo =
        document.getElementById("tipoGrupo");

    if (carrera)
        carrera.value = "";

    if (semestre)
        semestre.value = "";

    if (periodo)
        periodo.value = "";

    if (anio)
        anio.value = "";

    if (cantidad) {

        cantidad.value = "";

        cantidad.readOnly = false;
    }

    if (nombre)
        nombre.value = "";

    if (tipo)
        tipo.value = "regular";

    // --------------------------------------------------------
    // RESTABLECER ESTADO
    // --------------------------------------------------------

    editando = false;

    idGrupoEditando = null;

    // --------------------------------------------------------
    // RESTABLECER BOTÓN
    // --------------------------------------------------------

    const btnGuardar =
        document.getElementById("btnGuardarGrupo");

    if (btnGuardar) {

        btnGuardar.innerHTML =
            '<i class="bi bi-save"></i> Guardar Grupo';
    }

    const btnCancelar =
        document.getElementById("btnCancelar");

    if (btnCancelar) {

        btnCancelar.style.display =
            "none";
    }
}


// ============================================================
// ELIMINAR GRUPO
// ============================================================

function eliminarGrupo(id) {

    let nombreGrupo =
        "este grupo";

    // --------------------------------------------------------
    // BUSCAR NOMBRE EN LA TABLA
    // --------------------------------------------------------

    const buttons =
        document.querySelectorAll(
            `[onclick="eliminarGrupo(${id})"]`
        );

    if (buttons.length > 0) {

        const row =
            buttons[0].closest("tr");

        if (
            row &&
            row.cells &&
            row.cells[4]
        ) {

            nombreGrupo =
                row.cells[4].innerText ||
                "este grupo";
        }
    }

    // --------------------------------------------------------
    // CONFIRMACIÓN
    // --------------------------------------------------------

    Swal.fire({

        title: "¿Eliminar grupo?",

        html: `
            ¿Estás seguro de eliminar
            <strong>${escapeHTML(nombreGrupo)}</strong>?

            <br><br>

            <span class="text-danger">

                ⚠️ Esta acción eliminará permanentemente:

                <br>

                • El grupo

                <br>

                • Todos los alumnos asociados a este grupo

            </span>

            <br><br>

            <small class="text-muted">
                Las reservaciones asociadas al grupo deben
                ser manejadas antes de poder eliminarlo.
            </small>
        `,

        icon: "warning",

        showCancelButton: true,

        confirmButtonColor: "#d33",

        confirmButtonText:
            "Sí, eliminar todo",

        cancelButtonText:
            "Cancelar"

    })

    .then(result => {

        if (!result.isConfirmed) {
            return;
        }

        // ----------------------------------------------------
        // LOADING
        // ----------------------------------------------------

        Swal.fire({

            title: "Eliminando...",

            text: "Por favor espera.",

            allowOutsideClick: false,

            didOpen: () => {

                Swal.showLoading();

            }

        });

        console.log(
            "================================"
        );

        console.log(
            "ELIMINANDO GRUPO:",
            id
        );

        console.log(
            "================================"
        );

        // ----------------------------------------------------
        // PETICIÓN
        // ----------------------------------------------------

        fetch(
            "/SistemaApartadosITAP/controllers/eliminar_grupo_maestro.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },

                body: JSON.stringify({
                    id: id
                })
            }
        )

        .then(response => {

            return obtenerJSON(
                response,
                "eliminar_grupo_maestro.php"
            );

        })

        .then(data => {

            console.log(
                "Respuesta eliminar:",
                data
            );

            // ------------------------------------------------
            // ERROR
            // ------------------------------------------------

            if (data && data.error) {

                Swal.fire(
                    "No se pudo eliminar",
                    data.error,
                    "error"
                );

                return;
            }

            // ------------------------------------------------
            // ÉXITO
            // ------------------------------------------------

            Swal.fire(
                "Eliminado",
                data.mensaje ||
                "Grupo eliminado correctamente.",
                "success"
            );

            cargarGrupos();

        })

        .catch(error => {

            console.error(
                "ERROR COMPLETO AL ELIMINAR:",
                error
            );

            Swal.fire(
                "Error",
                error.message ||
                "No se pudo eliminar el grupo.",
                "error"
            );
        });

    });
}


// ============================================================
// ABRIR MODAL PARA SUBIR ALUMNOS
// ============================================================

function abrirModalAlumnos(
    grupoId,
    grupoNombre
) {

    console.log(
        "Abriendo modal alumnos:",
        grupoId,
        grupoNombre
    );

    const grupoIdElement =
        document.getElementById("grupoIdUpload");

    if (grupoIdElement) {

        grupoIdElement.value =
            grupoId;
    }

    const modalElement =
        document.getElementById("modalAlumnos");

    if (!modalElement) {

        console.error(
            "No se encontró #modalAlumnos"
        );

        return;
    }

    const modal =
        new bootstrap.Modal(modalElement);

    modal.show();
}


// ============================================================
// SUBIR ALUMNOS
// ============================================================

function subirAlumnos(e) {

    e.preventDefault();

    console.log(
        "================================"
    );

    console.log(
        "SUBIENDO ALUMNOS"
    );

    console.log(
        "================================"
    );

    const form =
        document.getElementById(
            "formUploadAlumnos"
        );

    const resultadoDiv =
        document.getElementById(
            "resultadoUpload"
        );

    if (!form) {

        console.error(
            "No se encontró #formUploadAlumnos"
        );

        return;
    }

    if (!resultadoDiv) {

        console.error(
            "No se encontró #resultadoUpload"
        );

        return;
    }

    const formData =
        new FormData(form);

    resultadoDiv.innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-hourglass-split"></i>
            Procesando archivo...
        </div>
    `;

    fetch(
        "/SistemaApartadosITAP/controllers/subir_alumnos_excel.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(response => {

        return obtenerJSON(
            response,
            "subir_alumnos_excel.php"
        );

    })

    .then(data => {

        console.log(
            "Respuesta subida:",
            data
        );

        // ----------------------------------------------------
        // ERROR
        // ----------------------------------------------------

        if (data && data.error) {

            resultadoDiv.innerHTML = `
                <div class="alert alert-danger">

                    <strong>Error:</strong>

                    ${escapeHTML(data.error)}

                </div>
            `;

            return;
        }

        // ----------------------------------------------------
        // DATOS
        // ----------------------------------------------------

        const procesados =
            parseInt(data.procesados) || 0;

        const duplicados =
            parseInt(data.duplicados) || 0;

        const errores =
            parseInt(data.errores) || 0;

        const total =
            parseInt(data.total) ||
            procesados;

        // ----------------------------------------------------
        // MENSAJE
        // ----------------------------------------------------

        let mensajeHtml = `

            <div class="alert alert-success">

                <strong>
                    ¡Proceso completado!
                </strong>

                <br>

                ✅ Alumnos procesados:
                ${procesados}

                <br>

                ⚠️ Duplicados ignorados:
                ${duplicados}

                <br>

                ❌ Errores:
                ${errores}

                <br>

                📊 Total:
                ${total}

        `;

        // ----------------------------------------------------
        // DETALLES
        // ----------------------------------------------------

        if (
            data.mensajes &&
            Array.isArray(data.mensajes) &&
            data.mensajes.length > 0
        ) {

            mensajeHtml += `
                <hr>

                <strong>
                    Detalles:
                </strong>

                <ul>
            `;

            data.mensajes
                .slice(0, 10)
                .forEach(msg => {

                    mensajeHtml += `
                        <li>
                            ${escapeHTML(String(msg))}
                        </li>
                    `;
                });

            if (data.mensajes.length > 10) {

                mensajeHtml += `
                    <li>
                        <em>
                            ...y
                            ${data.mensajes.length - 10}
                            más
                        </em>
                    </li>
                `;
            }

            mensajeHtml += `
                </ul>
            `;
        }

        mensajeHtml += `
            </div>
        `;

        resultadoDiv.innerHTML =
            mensajeHtml;

        // ----------------------------------------------------
        // RECARGAR GRUPOS
        // ----------------------------------------------------

        cargarGrupos();

        // ----------------------------------------------------
        // CERRAR MODAL
        // ----------------------------------------------------

        if (
            procesados > 0 ||
            duplicados > 0
        ) {

            setTimeout(() => {

                const modalElement =
                    document.getElementById(
                        "modalAlumnos"
                    );

                if (modalElement) {

                    const modal =
                        bootstrap.Modal.getInstance(
                            modalElement
                        );

                    if (modal) {

                        modal.hide();

                    }
                }

                resultadoDiv.innerHTML = "";

            }, 3000);
        }

    })

    .catch(error => {

        console.error(
            "ERROR AL SUBIR ALUMNOS:",
            error
        );

        resultadoDiv.innerHTML = `

            <div class="alert alert-danger">

                <strong>
                    Error inesperado:
                </strong>

                <br>

                ${escapeHTML(
                    error.message ||
                    "Error desconocido"
                )}

                <br><br>

                <small>
                    Revisa la consola del navegador
                    para más detalles.
                </small>

            </div>
        `;
    });
}


// ============================================================
// VER LISTA DE ALUMNOS
// ============================================================

function verListaAlumnos(
    grupoId,
    grupoNombre
) {

    console.log(
        "Ver alumnos del grupo:",
        grupoId,
        grupoNombre
    );

    const titulo =
        document.getElementById(
            "tituloGrupoAlumnos"
        );

    if (titulo) {

        titulo.innerText =
            grupoNombre;
    }

    const tbody =
        document.getElementById(
            "tablaAlumnosBody"
        );

    if (!tbody) {

        console.error(
            "No se encontró #tablaAlumnosBody"
        );

        return;
    }

    tbody.innerHTML = `

        <tr>

            <td
                colspan="4"
                class="text-center text-muted"
            >

                <i class="bi bi-hourglass-split"></i>

                Cargando alumnos...

            </td>

        </tr>
    `;

    // --------------------------------------------------------
    // ABRIR MODAL
    // --------------------------------------------------------

    const modalElement =
        document.getElementById(
            "modalVerAlumnos"
        );

    if (!modalElement) {

        console.error(
            "No se encontró #modalVerAlumnos"
        );

        return;
    }

    const modal =
        new bootstrap.Modal(
            modalElement
        );

    modal.show();

    // --------------------------------------------------------
    // OBTENER ALUMNOS
    // --------------------------------------------------------

    fetch(
        `/SistemaApartadosITAP/controllers/obtener_alumnos_por_grupo.php?id=${encodeURIComponent(grupoId)}`,
        {
            method: "GET",

            headers: {
                "Accept": "application/json"
            },

            cache: "no-store"
        }
    )

    .then(response => {

        return obtenerJSON(
            response,
            "obtener_alumnos_por_grupo.php"
        );

    })

    .then(data => {

        console.log(
            "Alumnos recibidos:",
            data
        );

        // ----------------------------------------------------
        // ERROR
        // ----------------------------------------------------

        if (data && data.error) {

            tbody.innerHTML = `

                <tr>

                    <td
                        colspan="4"
                        class="text-center text-danger"
                    >

                        <i
                            class="bi bi-exclamation-triangle"
                        ></i>

                        Error:
                        ${escapeHTML(data.error)}

                    </td>

                </tr>
            `;

            return;
        }

        // ----------------------------------------------------
        // VALIDAR ARRAY
        // ----------------------------------------------------

        if (!Array.isArray(data)) {

            tbody.innerHTML = `

                <tr>

                    <td
                        colspan="4"
                        class="text-center text-danger"
                    >

                        Respuesta inválida del servidor.

                    </td>

                </tr>
            `;

            return;
        }

        // ----------------------------------------------------
        // SIN ALUMNOS
        // ----------------------------------------------------

        if (data.length === 0) {

            tbody.innerHTML = `

                <tr>

                    <td
                        colspan="4"
                        class="text-center text-warning"
                    >

                        <i
                            class="bi bi-emoji-frown"
                        ></i>

                        No hay alumnos registrados
                        en este grupo.

                    </td>

                </tr>
            `;

            return;
        }

        // ----------------------------------------------------
        // CONSTRUIR LISTA
        // ----------------------------------------------------

        let html = "";

        data.forEach(
            (alumno, index) => {

                html += `

                    <tr>

                        <td class="text-center">
                            ${index + 1}
                        </td>

                        <td>
                            ${escapeHTML(
                                alumno.NoControl ||
                                "N/A"
                            )}
                        </td>

                        <td>
                            ${escapeHTML(
                                alumno.nombre ||
                                "N/A"
                            )}
                        </td>

                        <td>
                            ${escapeHTML(
                                alumno.plan ||
                                "No registrado"
                            )}
                        </td>

                    </tr>

                `;
            }
        );

        tbody.innerHTML =
            html;

    })

    .catch(error => {

        console.error(
            "ERROR AL CARGAR ALUMNOS:",
            error
        );

        tbody.innerHTML = `

            <tr>

                <td
                    colspan="4"
                    class="text-center text-danger"
                >

                    <i
                        class="bi bi-exclamation-triangle"
                    ></i>

                    Error al cargar los alumnos.

                    <br>

                    <small>
                        ${escapeHTML(
                            error.message ||
                            ""
                        )}
                    </small>

                </td>

            </tr>
        `;
    });
}


// ============================================================
// ESCAPAR HTML
// ============================================================

function escapeHTML(valor) {

    if (valor === null || valor === undefined) {
        return "";
    }

    return String(valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


// ============================================================
// HACER FUNCIONES DISPONIBLES PARA onclick DEL HTML
// ============================================================

window.cargarGrupos =
    cargarGrupos;

window.guardarGrupo =
    guardarGrupo;

window.editarGrupo =
    editarGrupo;

window.cancelarEdicion =
    cancelarEdicion;

window.eliminarGrupo =
    eliminarGrupo;

window.abrirModalAlumnos =
    abrirModalAlumnos;

window.subirAlumnos =
    subirAlumnos;

window.verListaAlumnos =
    verListaAlumnos;

console.log(
    "Todas las funciones de grupos están disponibles."
);
