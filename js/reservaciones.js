let horasSeleccionadas = [];
let paginaActual = 1;


// ==========================
// ELEMENTOS
// ==========================
const fecha = document.getElementById("fecha");
const lab = document.getElementById("lab");
const docente = document.getElementById("docente");
const grupo = document.getElementById("grupo");
const alumnos = document.getElementById("alumnos");
const software = document.getElementById("software");
const practica = document.getElementById("practica");

// FILTROS
const fechaInicio = document.getElementById("fechaInicio");
const fechaFin = document.getElementById("fechaFin");
const buscar = document.getElementById("buscar");


// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", () => {

    // ==========================
    // SELECCIONAR HORAS
    // ==========================
    document.querySelectorAll(".hora-btn").forEach(btn => {

        btn.addEventListener("click", () => {

            if(btn.classList.contains("ocupada")) return;

            btn.classList.toggle("activa");

            let hora = btn.innerText.trim();

            if(horasSeleccionadas.includes(hora)){

                horasSeleccionadas =
                    horasSeleccionadas.filter(h => h !== hora);

            } else {

                horasSeleccionadas.push(hora);

            }

        });

    });


    // ==========================
    // GUARDAR RESERVACION
    // ==========================
    document.getElementById("btnGuardar").addEventListener("click", () => {

        // ==========================
        // VALIDACIONES
        // ==========================
        if(!fecha.value){

            Swal.fire(
                "Error",
                "Selecciona fecha",
                "error"
            );

            return;

        }

        if(horasSeleccionadas.length === 0){

            Swal.fire(
                "Error",
                "Selecciona horas",
                "error"
            );

            return;

        }

        if(!lab.value){

            Swal.fire(
                "Error",
                "Selecciona laboratorio",
                "error"
            );

            return;

        }

        // ==========================
        // DATOS
        // ==========================
        let nombrePractica = practica.value;

        // ==========================
        // FETCH
        // ==========================
        fetch(
            '/SistemaApartadosITAP/controllers/guardar_reservacion.php',
            {

                method: "POST",

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({

                    fecha: fecha.value,

                    horas: horasSeleccionadas,

                    IDLab: lab.value,

                    IDDocentes: docente.value || null,

                    IDGrupo: grupo.value || null,

                    software: software.value,

                    Alumnos: alumnos.value || 0,

                    Practica: nombrePractica

                })

            }
        )

        .then(res => res.json())

        .then(data => {

            Swal.fire(
                "OK",
                data.mensaje,
                "success"
            );

            // ==========================
            // LIMPIAR
            // ==========================
            horasSeleccionadas = [];

            document.querySelectorAll(".hora-btn").forEach(btn => {

                btn.classList.remove("activa");

            });

            practica.value = "";
            software.value = "";
            alumnos.value = "";

            // ==========================
            // RECARGAR
            // ==========================
            cargarTabla();

            cargarHorasOcupadas();

        })

        .catch(err => {

            console.error(err);

            Swal.fire(
                "Error",
                "Error al guardar reservación",
                "error"
            );

        });

    });


    // ==========================
    // FILTROS
    // ==========================
    if(fechaInicio){

        fechaInicio.addEventListener(
            "change",
            () => cargarTabla(1)
        );

    }

    if(fechaFin){

        fechaFin.addEventListener(
            "change",
            () => cargarTabla(1)
        );

    }

    if(buscar){

        buscar.addEventListener(
            "keyup",
            () => cargarTabla(1)
        );

    }


    // ==========================
    // EVENTOS
    // ==========================
    docente.addEventListener("change", cargarGrupos);

    grupo.addEventListener("change", cargarAlumnos);

    fecha.addEventListener("change", cargarHorasOcupadas);

    lab.addEventListener("change", cargarHorasOcupadas);


    // ==========================
    // CARGA INICIAL
    // ==========================
    cargarTabla();

});


// ==========================
// TABLA
// ==========================
function cargarTabla(page = 1){

    paginaActual = page;

    let url =
        `/SistemaApartadosITAP/controllers/obtener_reservacion.php?page=${page}`;

    // ==========================
    // FECHAS
    // ==========================
    if(
        fechaInicio &&
        fechaFin &&
        fechaInicio.value &&
        fechaFin.value
    ){

        url +=
            `&inicio=${fechaInicio.value}&fin=${fechaFin.value}`;

    }

    // ==========================
    // BUSQUEDA
    // ==========================
    if(buscar && buscar.value){

        url += `&buscar=${buscar.value}`;

    }

    fetch(url)

    .then(res => res.json())

    .then(resp => {

        let html = "";

        resp.data.forEach(r => {

            html += `
            <tr>

                <td>
                    ${r.fecha}
                </td>

                <td>
                    ${r.horaInicio} - ${r.horaFin}
                </td>

                <td>
                    ${r.laboratorio ?? 'N/A'}
                </td>

                <td>
                    ${r.docente ?? 'N/A'}
                </td>

                <td>
                    ${r.carrera ?? ''}
                    ${r.Semestre ? 'Sem ' + r.Semestre : ''}
                </td>

                <td>
                    ${r.Practica ?? 'N/A'}
                </td>

                <td>
                    ${r.Software ?? 'N/A'}
                </td>

                <td>
                    ${r.Estado ?? 'Activo'}
                </td>

                <td>

                    <button
                        class="btn btn-danger btn-sm"
                        onclick="cancelar(${r.IDReservacion})">

                        Cancelar

                    </button>

                </td>

            </tr>
            `;

        });

        document.querySelector(
            "#tablaReservaciones tbody"
        ).innerHTML = html;

        generarPaginacion(resp.total);

    })

    .catch(err => {

        console.error(err);

    });

}


// ==========================
// PAGINACION
// ==========================
function generarPaginacion(total){

    let totalPaginas = Math.ceil(total / 10);

    let html = "";

    for(let i = 1; i <= totalPaginas; i++){

        html += `
        <button
            class="btn ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1"
            onclick="cargarTabla(${i})">

            ${i}

        </button>
        `;
    }

    const cont = document.getElementById("paginacion");

    if(cont){

        cont.innerHTML = html;

    }

}


// ==========================
// FILTRAR GRUPOS
// ==========================
function cargarGrupos(){

    if(!docente.value) return;

    fetch(
        `/SistemaApartadosITAP/controllers/obtener_grupos_docente.php?id=${docente.value}`
    )

    .then(res => res.json())

    .then(data => {

        grupo.innerHTML = `
            <option value="">
                Seleccionar grupo
            </option>
        `;

        data.forEach(g => {

            grupo.innerHTML += `
                <option value="${g.IDGrupo}">
                    ${g.carrera} - Sem ${g.Semestre}
                </option>
            `;

        });

    });

}


// ==========================
// AUTOLLENAR ALUMNOS
// ==========================
function cargarAlumnos(){

    if(!grupo.value) return;

    fetch(
        `/SistemaApartadosITAP/controllers/obtener_alumnos_grupo.php?id=${grupo.value}`
    )

    .then(res => res.json())

    .then(data => {

        alumnos.value = data.cantidad;

    });

}


// ==========================
// HORAS OCUPADAS
// ==========================
function cargarHorasOcupadas(){

    if(!fecha.value || !lab.value) return;

    fetch(
        `/SistemaApartadosITAP/controllers/obtener_horas_ocupadas.php?fecha=${fecha.value}&lab=${lab.value}`
    )

    .then(res => res.json())

    .then(horas => {

        document.querySelectorAll(".hora-btn").forEach(btn => {

            let hora = btn.innerText.trim();

            btn.classList.remove("ocupada");

            btn.disabled = false;

            if(horas.includes(hora)){

                btn.classList.add("ocupada");

                btn.disabled = true;

            }

        });

    });

}


// ==========================
// CANCELAR
// ==========================
function cancelar(id){

    Swal.fire({

        title: "¿Cancelar reservación?",

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Sí, cancelar",

        cancelButtonText: "No"

    })

    .then(r => {

        if(r.isConfirmed){

            fetch(
                '/SistemaApartadosITAP/controllers/cancelar_reservacion.php',
                {

                    method: "POST",

                    headers: {
                        'Content-Type': 'application/json'
                    },

                    body: JSON.stringify({ id })

                }
            )

            .then(res => res.json())

            .then(() => {

                Swal.fire(
                    "Cancelado",
                    "La reservación fue cancelada",
                    "success"
                );

                cargarTabla();

                cargarHorasOcupadas();

            });

        }

    });

}