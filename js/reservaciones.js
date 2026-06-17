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

    // Cargar grupos solo si hay un docente preseleccionado
    if(docente.value) {
        cargarGrupos();
    }

    // ==========================
    // SELECCIONAR HORAS
    // ==========================
    document.querySelectorAll(".hora-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            if(btn.classList.contains("ocupada")) return;
            btn.classList.toggle("activa");
            let hora = btn.innerText.trim();
            if(horasSeleccionadas.includes(hora)){
                horasSeleccionadas = horasSeleccionadas.filter(h => h !== hora);
            } else {
                horasSeleccionadas.push(hora);
            }
        });
    });

    // ==========================
    // GUARDAR RESERVACION
    // ==========================
    const btnGuardar = document.getElementById("btnGuardar");
    if(btnGuardar) {
        btnGuardar.addEventListener("click", guardarReservacion);
    }

    // ==========================
    // FILTROS
    // ==========================
    if(fechaInicio){
        fechaInicio.addEventListener("change", () => cargarTabla(1));
    }
    if(fechaFin){
        fechaFin.addEventListener("change", () => cargarTabla(1));
    }
    if(buscar){
        buscar.addEventListener("input", () => cargarTabla(1));
    }

    // ==========================
    // EVENTOS
    // ==========================
    if(docente) {
        docente.addEventListener("change", function() {
            cargarGrupos();
            // Limpiar grupo y alumnos cuando cambia docente
            grupo.innerHTML = '<option value="">Seleccionar grupo</option>';
            alumnos.value = "";
        });
    }

    if(grupo) {
        grupo.addEventListener("change", cargarAlumnos);
    }

    if(fecha) {
        fecha.addEventListener("change", cargarHorasOcupadas);
    }

    if(lab) {
        lab.addEventListener("change", cargarHorasOcupadas);
    }

    // ==========================
    // CARGA INICIAL
    // ==========================
    cargarTabla();
});

// ==========================
// GUARDAR RESERVACION
// ==========================
function guardarReservacion() {
    // VALIDACIONES
    if(!fecha.value){
        Swal.fire("Error", "Selecciona fecha", "error");
        return;
    }

    if(horasSeleccionadas.length === 0){
        Swal.fire("Error", "Selecciona horas", "error");
        return;
    }

    if(!lab.value){
        Swal.fire("Error", "Selecciona laboratorio", "error");
        return;
    }

    if(!docente.value){
        Swal.fire("Error", "Selecciona un docente", "error");
        return;
    }

    if(!grupo.value){
        Swal.fire("Error", "Selecciona un grupo", "error");
        return;
    }

    // Preparar datos
    const datos = {
        fecha: fecha.value,
        horas: horasSeleccionadas,
        IDLab: lab.value,
        IDUsuario: docente.value,  // Usando IDUsuario (maestro)
        IDGrupo: grupo.value || null,
        software: software.value || '',
        Alumnos: alumnos.value || 0,
        Practica: practica.value || ''
    };

    console.log("Enviando reservación:", datos);

    fetch('/SistemaApartadosITAP/controllers/guardar_reservacion.php', {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if(data.error) {
            Swal.fire("Error", data.error, "error");
            return;
        }
        Swal.fire("Éxito", data.mensaje || "Reservación guardada", "success");
        
        // LIMPIAR
        horasSeleccionadas = [];
        document.querySelectorAll(".hora-btn").forEach(btn => {
            btn.classList.remove("activa");
        });
        practica.value = "";
        software.value = "";
        alumnos.value = "";
        grupo.value = "";
        
        // RECARGAR
        cargarTabla();
        cargarHorasOcupadas();
    })
    .catch(err => {
        console.error("Error:", err);
        Swal.fire("Error", "Error al guardar reservación", "error");
    });
}

// ==========================
// TABLA
// ==========================
function cargarTabla(page = 1){
    paginaActual = page;
    let url = `/SistemaApartadosITAP/controllers/obtener_reservacion.php?page=${page}`;

    if(fechaInicio && fechaFin && fechaInicio.value && fechaFin.value){
        url += `&inicio=${fechaInicio.value}&fin=${fechaFin.value}`;
    }

    if(buscar && buscar.value){
        url += `&buscar=${encodeURIComponent(buscar.value)}`;
    }

    fetch(url)
    .then(response => response.json())
    .then(resp => {
        let html = "";
        if(resp.data && resp.data.length > 0) {
            resp.data.forEach(r => {
                html += `
                <tr>
                    <td>${r.fecha || '-'}</td>
                    <td>${r.horaInicio || ''} - ${r.horaFin || ''}</td>
                    <td>${r.laboratorio || 'N/A'}</td>
                    <td>${r.docente || 'N/A'}</td>
                    <td>${r.carrera || ''} ${r.Semestre ? 'Sem ' + r.Semestre : ''}</td>
                    <td>${r.Practica || 'N/A'}</td>
                    <td>${r.Software || 'N/A'}</td>
                    <td>
                        <span class="badge ${r.Estado === 'cancelada' ? 'bg-danger' : 'bg-success'}">
                            ${r.Estado || 'Activo'}
                        </span>
                    </td>
                    <td>
                        ${r.Estado !== 'cancelada' ? `
                            <button class="btn btn-danger btn-sm" onclick="cancelar(${r.IDReservacion})">
                                Cancelar
                            </button>
                        ` : '-'}
                    </td>
                </tr>
                `;
            });
        } else {
            html = `<tr><td colspan="9" class="text-center py-3">No hay reservaciones</td></tr>`;
        }

        const tbody = document.querySelector("#tablaReservaciones tbody");
        if(tbody) {
            tbody.innerHTML = html;
        }
        generarPaginacion(resp.total || 0);
    })
    .catch(err => {
        console.error("Error cargando tabla:", err);
        Swal.fire("Error", "Error al cargar las reservaciones", "error");
    });
}

// ==========================
// PAGINACION
// ==========================
function generarPaginacion(total){
    const cont = document.getElementById("paginacion");
    if(!cont) return;
    
    let totalPaginas = Math.ceil(total / 10);
    if(totalPaginas <= 1) {
        cont.innerHTML = '';
        return;
    }
    
    let html = '';
    for(let i = 1; i <= totalPaginas; i++){
        html += `
        <button class="btn ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1" 
                onclick="cargarTabla(${i})">
            ${i}
        </button>
        `;
    }
    cont.innerHTML = html;
}

// ==========================
// FILTRAR GRUPOS POR DOCENTE
// ==========================
function cargarGrupos(){
    const idDocente = docente.value;
    if(!idDocente) {
        grupo.innerHTML = `<option value="">Seleccionar grupo</option>`;
        return;
    }

    fetch(`/SistemaApartadosITAP/controllers/obtener_grupos_docente.php?id=${idDocente}`)
    .then(response => response.json())
    .then(data => {
        grupo.innerHTML = `<option value="">Seleccionar grupo</option>`;
        
        if(data.error) {
            console.error("Error:", data.error);
            Swal.fire("Error", "No se pudieron cargar los grupos", "error");
            return;
        }
        
        if(data.length === 0) {
            grupo.innerHTML = `<option value="">No hay grupos para este docente</option>`;
            return;
        }
        
        data.forEach(g => {
            const nombreGrupo = g.Nombre || `${g.Semestre}° Semestre`;
            grupo.innerHTML += `
                <option value="${g.IDGrupo}" data-alumnos="${g.cantidadAlumnos || 0}">
                    ${g.Carrera || 'Sin carrera'} - ${nombreGrupo}
                </option>
            `;
        });
    })
    .catch(err => {
        console.error("Error cargando grupos:", err);
        Swal.fire("Error", "Error al cargar los grupos", "error");
    });
}

// ==========================
// AUTOLLENAR ALUMNOS
// ==========================
function cargarAlumnos(){
    if(!grupo.value || grupo.selectedIndex < 0){
        alumnos.value = "";
        return;
    }
    const option = grupo.options[grupo.selectedIndex];
    const cantidad = option.dataset.alumnos || 0;
    alumnos.value = cantidad;
}

// ==========================
// HORAS OCUPADAS
// ==========================
function cargarHorasOcupadas(){
    if(!fecha.value || !lab.value) return;

    fetch(`/SistemaApartadosITAP/controllers/obtener_horas_ocupadas.php?fecha=${fecha.value}&lab=${lab.value}`)
    .then(response => response.json())
    .then(horas => {
        // Si la respuesta es un objeto con error
        if(horas.error) {
            console.error(horas.error);
            return;
        }
        
        // Si es un array de horas
        document.querySelectorAll(".hora-btn").forEach(btn => {
            let hora = btn.innerText.trim();
            btn.classList.remove("ocupada");
            btn.disabled = false;
            
            if(Array.isArray(horas) && horas.includes(hora)){
                btn.classList.add("ocupada");
                btn.disabled = true;
                btn.classList.remove("activa");
            }
        });
    })
    .catch(err => console.error("Error cargando horas ocupadas:", err));
}

// ==========================
// CANCELAR RESERVACION
// ==========================
function cancelar(id){
    Swal.fire({
        title: "¿Cancelar reservación?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No"
    })
    .then(result => {
        if(result.isConfirmed){
            fetch('/SistemaApartadosITAP/controllers/cancelar_reservacion.php', {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    Swal.fire("Error", data.error, "error");
                    return;
                }
                Swal.fire("Cancelado", data.mensaje || "La reservación fue cancelada", "success");
                cargarTabla();
                cargarHorasOcupadas();
            })
            .catch(err => {
                console.error("Error cancelando:", err);
                Swal.fire("Error", "No se pudo cancelar la reservación", "error");
            });
        }
    });
}