// ============================================================
// RESERVACIONES - USUARIO INVITADO
// ============================================================

let paginaActual = 1;


// ============================================================
// DOM READY
// ============================================================

document.addEventListener("DOMContentLoaded", function () {

    console.log("========================================");
    console.log(" RESERVACIONES INVITADO INICIADO");
    console.log("========================================");


    // ========================================================
    // ESTABLECER SEMANA ACTUAL
    // ========================================================

    establecerSemanaActual();


    // ========================================================
    // CARGAR DEPARTAMENTOS
    // ========================================================

    cargarDepartamentos();


    // ========================================================
    // CARGAR TODOS LOS LABORATORIOS
    // ========================================================

    cargarLaboratorios();


    // ========================================================
    // CARGAR TABLA
    // ========================================================

    cargarTabla(1);


    // ========================================================
    // CAMBIO DE DEPARTAMENTO
    // ========================================================

    const departamento =
        document.getElementById("filtroDepartamento");


    if (departamento) {

        departamento.addEventListener(
            "change",
            function () {

                console.log(
                    "Departamento seleccionado:",
                    this.value
                );


                // Cargar solamente los laboratorios
                // pertenecientes al departamento

                cargarLaboratorios(
                    this.value
                );

            }
        );

    }


    // ========================================================
    // ENTER EN EL CAMPO DE BÚSQUEDA
    // ========================================================

    const buscar =
        document.getElementById("buscar");


    if (buscar) {

        buscar.addEventListener(
            "keypress",
            function (event) {

                if (event.key === "Enter") {

                    event.preventDefault();

                    cargarTabla(1);

                }

            }
        );

    }

});


// ============================================================
// ESTABLECER SEMANA ACTUAL
// ============================================================

function establecerSemanaActual() {

    const inputInicio =
        document.getElementById("fechaInicio");


    const inputFin =
        document.getElementById("fechaFin");


    if (!inputInicio || !inputFin) {

        console.warn(
            " No se encontraron los campos de fecha"
        );

        return;

    }


    const hoy = new Date();


    // ========================================================
    // OBTENER DÍA DE LA SEMANA
    // ========================================================
    //
    // Domingo = 0
    // Lunes   = 1
    // Martes  = 2
    // Miércoles = 3
    // Jueves  = 4
    // Viernes = 5
    // Sábado  = 6
    //
    // La semana será:
    // Lunes → Domingo
    // ========================================================

    const diaSemana =
        hoy.getDay();


    let diferenciaLunes;


    if (diaSemana === 0) {

        diferenciaLunes = 6;

    } else {

        diferenciaLunes =
            diaSemana - 1;

    }


    // ========================================================
    // CALCULAR LUNES
    // ========================================================

    const lunes =
        new Date(hoy);


    lunes.setDate(
        hoy.getDate() - diferenciaLunes
    );


    // ========================================================
    // CALCULAR DOMINGO
    // ========================================================

    const domingo =
        new Date(lunes);


    domingo.setDate(
        lunes.getDate() + 6
    );


    // ========================================================
    // ASIGNAR FECHAS
    // ========================================================

    inputInicio.value =
        convertirFechaInput(lunes);


    inputFin.value =
        convertirFechaInput(domingo);


    console.log(
        "📅 Semana actual:",
        inputInicio.value,
        "→",
        inputFin.value
    );

}


// ============================================================
// CONVERTIR FECHA A YYYY-MM-DD
// ============================================================

function convertirFechaInput(fecha) {

    const año =
        fecha.getFullYear();


    const mes =
        String(
            fecha.getMonth() + 1
        ).padStart(2, "0");


    const dia =
        String(
            fecha.getDate()
        ).padStart(2, "0");


    return `${año}-${mes}-${dia}`;

}


// ============================================================
// CARGAR DEPARTAMENTOS
// ============================================================

function cargarDepartamentos() {

    console.log(
        "🔄 Cargando departamentos..."
    );


    fetch(
        "../../controllers/obtener_departamentos_invitado.php"
    )

    .then(response => {

        if (!response.ok) {

            throw new Error(
                "HTTP " + response.status
            );

        }

        return response.json();

    })

    .then(data => {

        console.log(
            "📋 Departamentos recibidos:",
            data
        );


        const select =
            document.getElementById(
                "filtroDepartamento"
            );


        if (!select) {

            console.error(
                "❌ No existe #filtroDepartamento"
            );

            return;

        }


        // ====================================================
        // LIMPIAR SELECT
        // ====================================================

        select.innerHTML = `

            <option value="">
                Todos los departamentos
            </option>

        `;


        // ====================================================
        // VALIDAR RESPUESTA
        // ====================================================

        if (!Array.isArray(data)) {

            console.error(
                "❌ Respuesta incorrecta:",
                data
            );

            return;

        }


        // ====================================================
        // AGREGAR DEPARTAMENTOS
        // ====================================================

        data.forEach(item => {

            const option =
                document.createElement("option");


            option.value =
                item.IDDepartamentos;


            option.textContent =
                item.nombre;


            select.appendChild(
                option
            );

        });


        console.log(
            "✅ Departamentos cargados:",
            data.length
        );

    })

    .catch(error => {

        console.error(
            "❌ Error cargando departamentos:",
            error
        );

    });

}


// ============================================================
// CARGAR LABORATORIOS
// ============================================================

function cargarLaboratorios(
    departamento = ""
) {

    console.log(
        "🔄 Cargando laboratorios...",
        departamento
    );


    let url =
        "../../controllers/" +
        "obtener_laboratorios_invitado.php";


    // ========================================================
    // FILTRAR POR DEPARTAMENTO
    // ========================================================

    if (departamento !== "") {

        url +=
            "?departamento=" +
            encodeURIComponent(
                departamento
            );

    }


    fetch(url)

    .then(response => {

        if (!response.ok) {

            throw new Error(
                "HTTP " + response.status
            );

        }

        return response.json();

    })

    .then(data => {

        console.log(
            "🧪 Laboratorios recibidos:",
            data
        );


        const select =
            document.getElementById(
                "filtroLaboratorio"
            );


        if (!select) {

            console.error(
                "❌ No existe #filtroLaboratorio"
            );

            return;

        }


        // ====================================================
        // LIMPIAR SELECT
        // ====================================================

        select.innerHTML = `

            <option value="">
                Todos los laboratorios
            </option>

        `;


        // ====================================================
        // VALIDAR RESPUESTA
        // ====================================================

        if (!Array.isArray(data)) {

            console.error(
                "❌ Respuesta incorrecta:",
                data
            );

            return;

        }


        // ====================================================
        // AGREGAR LABORATORIOS
        // ====================================================

        data.forEach(item => {

            const option =
                document.createElement("option");


            option.value =
                item.IDLab;


            option.textContent =
                item.Nombre;


            select.appendChild(
                option
            );

        });


        console.log(
            "✅ Laboratorios cargados:",
            data.length
        );

    })

    .catch(error => {

        console.error(
            "❌ Error cargando laboratorios:",
            error
        );

    });

}


// ============================================================
// CARGAR TABLA
// ============================================================

function cargarTabla(page = 1) {

    paginaActual = page;


    console.log(
        "========================================"
    );

    console.log(
        "🔍 CARGANDO RESERVACIONES"
    );

    console.log(
        "Página:",
        page
    );


    // ========================================================
    // OBTENER ELEMENTOS
    // ========================================================

    const fechaInicio =
        document.getElementById(
            "fechaInicio"
        );


    const fechaFin =
        document.getElementById(
            "fechaFin"
        );


    const buscar =
        document.getElementById(
            "buscar"
        );


    const estado =
        document.getElementById(
            "filtroEstado"
        );


    const departamento =
        document.getElementById(
            "filtroDepartamento"
        );


    const laboratorio =
        document.getElementById(
            "filtroLaboratorio"
        );


    // ========================================================
    // CONSTRUIR URL
    // ========================================================

    let url =
        "../../controllers/" +
        "obtener_reservaciones_invitado.php";


    url +=
        "?page=" +
        encodeURIComponent(page);


    // ========================================================
    // FECHA INICIO
    // ========================================================

    if (
        fechaInicio &&
        fechaInicio.value !== ""
    ) {

        url +=
            "&inicio=" +
            encodeURIComponent(
                fechaInicio.value
            );

    }


    // ========================================================
    // FECHA FIN
    // ========================================================

    if (
        fechaFin &&
        fechaFin.value !== ""
    ) {

        url +=
            "&fin=" +
            encodeURIComponent(
                fechaFin.value
            );

    }


    // ========================================================
    // BÚSQUEDA
    // ========================================================

    if (
        buscar &&
        buscar.value.trim() !== ""
    ) {

        url +=
            "&buscar=" +
            encodeURIComponent(
                buscar.value.trim()
            );

    }


    // ========================================================
    // ESTADO
    // ========================================================

    if (
        estado &&
        estado.value !== ""
    ) {

        url +=
            "&estado=" +
            encodeURIComponent(
                estado.value
            );

    }


    // ========================================================
    // DEPARTAMENTO
    // ========================================================

    if (
        departamento &&
        departamento.value !== ""
    ) {

        url +=
            "&departamento=" +
            encodeURIComponent(
                departamento.value
            );

    }


    // ========================================================
    // LABORATORIO
    // ========================================================

    if (
        laboratorio &&
        laboratorio.value !== ""
    ) {

        url +=
            "&laboratorio=" +
            encodeURIComponent(
                laboratorio.value
            );

    }


    console.log(
        "🌐 URL:",
        url
    );


    // ========================================================
    // MOSTRAR CARGANDO
    // ========================================================

    mostrarCargando();


    // ========================================================
    // REALIZAR PETICIÓN
    // ========================================================

    fetch(url)

    .then(response => {

        if (!response.ok) {

            throw new Error(
                "HTTP " + response.status
            );

        }

        return response.json();

    })

    .then(resp => {

        console.log(
            "📊 Respuesta:",
            resp
        );


        // ====================================================
        // VALIDAR ERROR DEL SERVIDOR
        // ====================================================

        if (resp.error) {

            throw new Error(
                resp.error
            );

        }


        // ====================================================
        // MOSTRAR DATOS
        // ====================================================

        mostrarReservaciones(
            resp.data || []
        );


        // ====================================================
        // PAGINACIÓN
        // ====================================================

        generarPaginacion(
            resp.total || 0,
            resp.totalPages || 0
        );

    })

    .catch(error => {

        console.error(
            "❌ Error cargando reservaciones:",
            error
        );


        mostrarError(
            error.message
        );

    });

}


// ============================================================
// MOSTRAR RESERVACIONES
// ============================================================

function mostrarReservaciones(
    reservaciones
) {

    const tbody =
        document.querySelector(
            "#tablaReservaciones tbody"
        );


    if (!tbody) {

        console.error(
            "❌ No se encontró el tbody"
        );

        return;

    }


    // ========================================================
    // SIN RESULTADOS
    // ========================================================

    if (
        !reservaciones ||
        reservaciones.length === 0
    ) {

        tbody.innerHTML = `

            <tr>

                <td
                    colspan="9"
                    class="text-center py-5"
                >

                    <i
                        class="bi bi-calendar-x fs-1 text-muted"
                    ></i>

                    <div
                        class="mt-3 fw-bold text-muted"
                    >

                        No se encontraron
                        reservaciones.

                    </div>

                    <small class="text-muted">

                        Intenta cambiar los filtros
                        o seleccionar otra fecha.

                    </small>

                </td>

            </tr>

        `;

        return;

    }


    // ========================================================
    // CONSTRUIR TABLA
    // ========================================================

    let html = "";


    reservaciones.forEach(
        function (r) {


            // ================================================
            // ESTADO
            // ================================================

            const estado =
                (
                    r.Estado ||
                    ""
                ).toLowerCase();


            let claseEstado =
                "bg-secondary";


            let textoEstado =
                r.Estado ||
                "Sin estado";


            if (estado === "activa") {

                claseEstado =
                    "bg-success";

                textoEstado =
                    "Activa";

            }

            else if (
                estado === "cancelada"
            ) {

                claseEstado =
                    "bg-danger";

                textoEstado =
                    "Cancelada";

            }

            else if (
                estado === "finalizada"
            ) {

                claseEstado =
                    "bg-secondary";

                textoEstado =
                    "Finalizada";

            }


            // ================================================
            // GRUPO
            // ================================================

            let grupo =
                r.grupoNombre ||
                "N/A";


            if (r.Semestre) {

                grupo +=
                    " (" +
                    r.Semestre +
                    "°)";

            }


            // ================================================
            // FECHA
            // ================================================

            const fecha =
                formatearFecha(
                    r.fecha
                );


            // ================================================
            // HORARIO
            // ================================================

            const horaInicio =
                formatearHora(
                    r.horaInicio
                );


            const horaFin =
                formatearHora(
                    r.horaFin
                );


            // ================================================
            // CREAR FILA
            // ================================================

            html += `

                <tr>

                    <td>

                        ${escaparHTML(
                            fecha
                        )}

                    </td>


                    <td>

                        <span class="fw-semibold">

                            ${escaparHTML(
                                horaInicio
                            )}

                            -

                            ${escaparHTML(
                                horaFin
                            )}

                        </span>

                    </td>


                    <td>

                        ${escaparHTML(
                            r.laboratorio ||
                            "N/A"
                        )}

                    </td>


                    <td>

                        ${escaparHTML(
                            r.departamento ||
                            "N/A"
                        )}

                    </td>


                    <td>

                        ${escaparHTML(
                            r.docente ||
                            "N/A"
                        )}

                    </td>


                    <td>

                        ${escaparHTML(
                            grupo
                        )}

                    </td>


                    <td>

                        ${escaparHTML(
                            r.Practica ||
                            "N/A"
                        )}

                    </td>


                    <td>

                        ${escaparHTML(
                            r.Software ||
                            "N/A"
                        )}

                    </td>


                    <td>

                        <span
                            class="badge ${claseEstado}"
                        >

                            ${escaparHTML(
                                textoEstado
                            )}

                        </span>

                    </td>

                </tr>

            `;

        }
    );


    tbody.innerHTML =
        html;

}


// ============================================================
// MOSTRAR CARGANDO
// ============================================================

function mostrarCargando() {

    const tbody =
        document.querySelector(
            "#tablaReservaciones tbody"
        );


    if (!tbody) return;


    tbody.innerHTML = `

        <tr>

            <td
                colspan="9"
                class="text-center py-5"
            >

                <div
                    class="spinner-border text-primary"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <div
                    class="mt-3 text-muted"
                >

                    Cargando reservaciones...

                </div>

            </td>

        </tr>

    `;

}


// ============================================================
// MOSTRAR ERROR
// ============================================================

function mostrarError(
    mensaje
) {

    const tbody =
        document.querySelector(
            "#tablaReservaciones tbody"
        );


    if (!tbody) return;


    tbody.innerHTML = `

        <tr>

            <td
                colspan="9"
                class="text-center py-5 text-danger"
            >

                <i
                    class="bi bi-exclamation-triangle fs-1"
                ></i>

                <div
                    class="mt-3 fw-bold"
                >

                    Error al cargar las reservaciones.

                </div>

                <small>

                    ${escaparHTML(
                        mensaje
                    )}

                </small>

            </td>

        </tr>

    `;


    const paginacion =
        document.getElementById(
            "paginacion"
        );


    if (paginacion) {

        paginacion.innerHTML = "";

    }

}


// ============================================================
// GENERAR PAGINACIÓN
// ============================================================

function generarPaginacion(
    total,
    totalPages
) {

    const contenedor =
        document.getElementById(
            "paginacion"
        );


    if (!contenedor) return;


    // ========================================================
    // SI NO HAY PÁGINAS
    // ========================================================

    if (
        totalPages <= 1 ||
        total <= 10
    ) {

        contenedor.innerHTML = "";

        return;

    }


    let html = `

        <div class="d-flex justify-content-center">

            <div
                class="btn-group"
                role="group"
                aria-label="Paginación"
            >

    `;


    // ========================================================
    // BOTÓN ANTERIOR
    // ========================================================

    html += `

        <button
            type="button"
            class="btn btn-outline-primary"
            onclick="cargarTabla(${paginaActual - 1})"
            ${paginaActual <= 1 ? "disabled" : ""}
        >

            <i class="bi bi-chevron-left"></i>

        </button>

    `;


    // ========================================================
    // NÚMEROS
    // ========================================================

    for (
        let i = 1;
        i <= totalPages;
        i++
    ) {

        html += `

            <button
                type="button"
                class="btn ${
                    i === paginaActual
                        ? "btn-primary"
                        : "btn-outline-primary"
                }"
                onclick="cargarTabla(${i})"
            >

                ${i}

            </button>

        `;

    }


    // ========================================================
    // BOTÓN SIGUIENTE
    // ========================================================

    html += `

        <button
            type="button"
            class="btn btn-outline-primary"
            onclick="cargarTabla(${paginaActual + 1})"
            ${
                paginaActual >= totalPages
                    ? "disabled"
                    : ""
            }
        >

            <i class="bi bi-chevron-right"></i>

        </button>

    `;


    html += `

            </div>

        </div>

        <div class="text-center mt-2 text-muted">

            Total de reservaciones:
            <strong>${total}</strong>

        </div>

    `;


    contenedor.innerHTML =
        html;

}


// ============================================================
// LIMPIAR FILTROS
// ============================================================

function limpiarFiltros() {

    console.log(
        "🧹 Limpiando filtros..."
    );


    // ========================================================
    // RESTABLECER SEMANA ACTUAL
    // ========================================================

    establecerSemanaActual();


    // ========================================================
    // LIMPIAR BÚSQUEDA
    // ========================================================

    const buscar =
        document.getElementById(
            "buscar"
        );


    if (buscar) {

        buscar.value = "";

    }


    // ========================================================
    // LIMPIAR ESTADO
    // ========================================================

    const estado =
        document.getElementById(
            "filtroEstado"
        );


    if (estado) {

        estado.value = "";

    }


    // ========================================================
    // LIMPIAR DEPARTAMENTO
    // ========================================================

    const departamento =
        document.getElementById(
            "filtroDepartamento"
        );


    if (departamento) {

        departamento.value = "";

    }


    // ========================================================
    // RESTABLECER LABORATORIOS
    // ========================================================

    cargarLaboratorios();


    // ========================================================
    // VOLVER A PRIMERA PÁGINA
    // ========================================================

    cargarTabla(1);

}


// ============================================================
// FORMATEAR FECHA
// ============================================================

function formatearFecha(
    fecha
) {

    if (!fecha) {

        return "N/A";

    }


    // Si viene como YYYY-MM-DD

    const partes =
        fecha.split("-");


    if (partes.length === 3) {

        return (
            partes[2] +
            "/" +
            partes[1] +
            "/" +
            partes[0]
        );

    }


    return fecha;

}


// ============================================================
// FORMATEAR HORA
// ============================================================

function formatearHora(
    hora
) {

    if (!hora) {

        return "--:--";

    }


    // ========================================================
    // Si viene como:
    // 07:00:00
    //
    // Mostrar:
    // 07:00
    // ========================================================

    return hora.substring(
        0,
        5
    );

}


// ============================================================
// ESCAPAR HTML
// ============================================================

function escaparHTML(
    texto
) {

    if (
        texto === null ||
        texto === undefined
    ) {

        return "";

    }


    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        String(texto);


    return div.innerHTML;

}
