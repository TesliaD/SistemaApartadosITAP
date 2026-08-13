let horaSeleccionada = null; // Cambiado a null en lugar de array
let paginaActual = 1;

// ==========================
// ELEMENTOS
// ==========================
const fecha = document.getElementById("fecha");
const lab = document.getElementById("lab");
const grupo = document.getElementById("grupo");
const alumnos = document.getElementById("alumnos");
const software = document.getElementById("software");
const practica = document.getElementById("practica");

// FILTROS
const fechaInicio = document.getElementById("fechaInicio");
const fechaFin = document.getElementById("fechaFin");
const buscar = document.getElementById("buscar");
const filtroEstado = document.getElementById("filtroEstado");

// ==========================
// LIMPIAR JSON
// ==========================
function limpiarJSON(texto) {
    if(!texto) return texto;
    
    if (texto.charCodeAt(0) === 0xFEFF) {
        texto = texto.substring(1);
    }
    
    texto = texto.replace(/[\x00-\x1F\x7F¬]+$/g, '');
    texto = texto.trim();
    
    return texto;
}

// ==========================
// VALIDACIONES DE FECHA
// ==========================
function configurarFechas() {
    if(!fecha) return;
    
    const hoy = new Date();
    const minFecha = new Date(hoy);
    minFecha.setDate(hoy.getDate() + 1);
    
    const maxFecha = new Date(hoy);
    maxFecha.setDate(hoy.getDate() + 3);
    
    const minFechaStr = minFecha.toISOString().split('T')[0];
    const maxFechaStr = maxFecha.toISOString().split('T')[0];
    
    fecha.min = minFechaStr;
    fecha.max = maxFechaStr;
    
    if(!fecha.value || fecha.value < minFechaStr) {
        fecha.value = minFechaStr;
    }
    
    fecha.addEventListener("change", validarFecha);
}

function validarFecha() {
    if(!fecha) return;
    
    const fechaSeleccionada = new Date(fecha.value);
    const diaSemana = fechaSeleccionada.getDay();
    
    if(diaSemana === 0) {
        Swal.fire("Error", "No se pueden hacer reservaciones en domingo", "error");
        const hoy = new Date();
        const minFecha = new Date(hoy);
        minFecha.setDate(hoy.getDate() + 1);
        while(minFecha.getDay() === 0) {
            minFecha.setDate(minFecha.getDate() + 1);
        }
        fecha.value = minFecha.toISOString().split('T')[0];
    }
    
    cargarHorasOcupadas();
}

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", () => {

    configurarFechas();

    // ==========================
    // SELECCIONAR HORA - UNA SOLA
    // ==========================
    document.querySelectorAll(".hora-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            if(btn.classList.contains("ocupada")) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hora no disponible',
                    text: 'Esta hora ya está ocupada. Selecciona otra.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }
            
            const hora = btn.innerText.trim();
            
            // Si ya hay una hora seleccionada y es diferente
            if(horaSeleccionada && horaSeleccionada !== hora) {
                // Deseleccionar la anterior
                document.querySelectorAll(".hora-btn").forEach(b => {
                    if(b.innerText.trim() === horaSeleccionada) {
                        b.classList.remove("activa");
                    }
                });
            }
            
            // Si ya está seleccionada, la deseleccionamos
            if(horaSeleccionada === hora) {
                btn.classList.remove("activa");
                horaSeleccionada = null;
                console.log("Hora deseleccionada");
                return;
            }
            
            // Seleccionar la nueva hora
            btn.classList.add("activa");
            horaSeleccionada = hora;
            console.log("Hora seleccionada:", horaSeleccionada);
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
    if(filtroEstado){
        filtroEstado.addEventListener("change", () => cargarTabla(1));
    }

    // ==========================
    // EVENTOS
    // ==========================
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
    cargarDatosMaestro();
});

// ==========================
// LIMPIAR FILTROS
// ==========================
function limpiarFiltros() {
    console.log("Limpiando filtros...");
    
    if(fechaInicio) fechaInicio.value = "";
    if(fechaFin) fechaFin.value = "";
    if(buscar) buscar.value = "";
    if(filtroEstado) filtroEstado.value = "";
    
    paginaActual = 1;
    cargarTabla(1);
}

// ==========================
// GUARDAR RESERVACION
// ==========================
function guardarReservacion() {
    // ==========================
    // VALIDACIONES
    // ==========================
    if(!fecha.value){
        Swal.fire("Error", "Selecciona una fecha", "error");
        return;
    }
    
    const fechaSeleccionada = new Date(fecha.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if(fechaSeleccionada <= hoy) {
        Swal.fire("Error", "No puedes reservar para el día actual. Solo con 1 día de anticipación", "error");
        return;
    }
    
    const diffTime = fechaSeleccionada - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if(diffDays > 3) {
        Swal.fire("Error", "Solo puedes reservar con máximo 3 días de anticipación", "error");
        return;
    }
    
    if(fechaSeleccionada.getDay() === 0) {
        Swal.fire("Error", "No se pueden hacer reservaciones en domingo", "error");
        return;
    }
    
    // ==========================
    // VALIDAR QUE SOLO SEA UNA HORA
    // ==========================
    if(!horaSeleccionada){
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona una hora',
            text: 'Debes seleccionar una hora para la reservación',
            confirmButtonColor: '#1d3557'
        });
        return;
    }
    
    if(!lab.value){
        Swal.fire("Error", "Selecciona un laboratorio", "error");
        return;
    }
    
    if(!grupo.value){
        Swal.fire("Error", "Selecciona un grupo", "error");
        return;
    }
    
    // Preparar datos - ahora solo una hora
    const datos = {
        fecha: fecha.value,
        horas: [horaSeleccionada], // Enviamos como array de una sola hora
        IDLab: lab.value,
        IDGrupo: grupo.value || null,
        software: software.value || '',
        Alumnos: alumnos.value || 0,
        Practica: practica.value || ''
    };
    
    console.log("Enviando reservación (1 hora):", datos);
    
    Swal.fire({
        title: 'Guardando reservación...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/SistemaApartadosITAP/controllers/guardar_reservacion_maestro.php', {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if(data.error) {
            Swal.fire("Error", data.error, "error");
            return;
        }
        
        Swal.fire("Éxito", data.mensaje || "Reservación guardada correctamente", "success");
        
        // LIMPIAR
        horaSeleccionada = null;
        document.querySelectorAll(".hora-btn").forEach(btn => {
            btn.classList.remove("activa");
        });
        practica.value = "";
        software.value = "";
        alumnos.value = "";
        
        cargarTabla();
        cargarHorasOcupadas();
    })
    .catch(err => {
        Swal.close();
        console.error("Error:", err);
        Swal.fire("Error", "Error al guardar reservación", "error");
    });
}

// ==========================
// CARGAR GRUPOS DEL MAESTRO
// ==========================
function cargarDatosMaestro() {
    fetch('/SistemaApartadosITAP/controllers/obtener_grupos_maestro_reservacion.php')
        .then(response => response.json())
        .then(data => {
            if(data.error){
                Swal.fire("Error", data.error, "error");
                return;
            }
            
            const grupoSelect = document.getElementById("grupo");
            if(grupoSelect) {
                grupoSelect.innerHTML = '<option value="">Seleccionar grupo</option>';
                
                if(data.length === 0) {
                    grupoSelect.innerHTML += '<option value="" disabled>No tienes grupos registrados</option>';
                } else {
                    data.forEach(g => {
                        const nombreGrupo = g.NombreCompleto || `${g.Carrera} - Semestre ${g.Semestre}`;
                        grupoSelect.innerHTML += `
                            <option value="${g.IDGrupo}" data-alumnos="${g.cantidadAlumnos || 0}">
                                ${nombreGrupo}
                            </option>
                        `;
                    });
                }
            }
        })
        .catch(err => {
            console.error("Error cargando datos del maestro:", err);
            Swal.fire("Error", "No se pudieron cargar los grupos", "error");
        });
}

// ==========================
// TABLA DE RESERVACIONES
// ==========================
function cargarTabla(page = 1){
    paginaActual = page;
    let url = `/SistemaApartadosITAP/controllers/obtener_reservaciones_maestro.php?page=${page}`;
    
    if(fechaInicio && fechaInicio.value){
        url += `&inicio=${fechaInicio.value}`;
    }
    if(fechaFin && fechaFin.value){
        url += `&fin=${fechaFin.value}`;
    }
    if(buscar && buscar.value){
        url += `&buscar=${encodeURIComponent(buscar.value)}`;
    }
    if(filtroEstado && filtroEstado.value){
        url += `&estado=${encodeURIComponent(filtroEstado.value)}`;
    }
    
    console.log("Cargando URL:", url);
    
    fetch(url)
        .then(response => response.text())
        .then(text => {
            console.log("RAW Response (primeros 200 chars):", text.substring(0, 200));
            
            text = limpiarJSON(text);
            
            if(!text || text === "") {
                throw new Error("Respuesta vacía del servidor");
            }
            
            try {
                const resp = JSON.parse(text);
                console.log("Respuesta parseada:", resp);
                
                if(resp.error) {
                    Swal.fire("Error", resp.error, "error");
                    return;
                }
                
                let html = "";
                if(resp.data && resp.data.length > 0) {
                    resp.data.forEach(r => {
                        const estadoNormalizado = (r.Estado || '').toLowerCase();
                        let estadoBadge = '';
                        
                        if(estadoNormalizado === 'cancelada' || estadoNormalizado === 'cancelado') {
                            estadoBadge = '<span class="badge bg-danger">Cancelada</span>';
                        } else if(estadoNormalizado === 'finalizada' || estadoNormalizado === 'finalizado') {
                            estadoBadge = '<span class="badge bg-secondary">Finalizada</span>';
                        } else {
                            estadoBadge = '<span class="badge bg-success">Activa</span>';
                        }
                        
                        const mostrarCancelar = estadoNormalizado === 'activa' || estadoNormalizado === 'activo';
                        
                        html += `
                            <tr>
                                <td>${r.fecha || 'N/A'}</td>
                                <td>${r.horaInicio || ''} - ${r.horaFin || ''}</td>
                                <td>${r.laboratorio || 'N/A'}</td>
                                <td>${r.docente || 'N/A'}</td>
                                <td>${r.grupo || 'N/A'}</td>
                                <td>${r.Practica || 'N/A'}</td>
                                <td>${r.Software || 'N/A'}</td>
                                <td>${estadoBadge}</td>
                                <td>
                                    ${mostrarCancelar ? 
                                        `<button class="btn btn-danger btn-sm" onclick="cancelar(${r.IDReservacion})">
                                            Cancelar
                                        </button>` : 
                                        `<span class="text-muted">—</span>`
                                    }
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="9" class="text-center py-3">No hay reservaciones</td></tr>';
                }
                
                const tbody = document.querySelector("#tablaReservaciones tbody");
                if(tbody) tbody.innerHTML = html;
                
                generarPaginacion(resp.total || 0);
                
            } catch (e) {
                console.error("Error parseando JSON:", e);
                console.error("Texto que causó error (primeros 500 chars):", text.substring(0, 500));
                Swal.fire("Error", "Error al procesar la respuesta del servidor", "error");
            }
        })
        .catch(err => {
            console.error("Error en fetch:", err);
            Swal.fire("Error", "Error al cargar reservaciones: " + err.message, "error");
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
        cont.innerHTML = "";
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
// HORAS OCUPADAS (CORREGIDA CON NORMALIZACIÓN)
// ==========================
// ==========================
// HORAS OCUPADAS (CORREGIDA PARA SEGUNDOS)
// ==========================
function cargarHorasOcupadas(){
    if(!fecha.value || !lab.value) return;
    
    fetch(`/SistemaApartadosITAP/controllers/obtener_horas_ocupadas.php?fecha=${fecha.value}&lab=${lab.value}`)
        .then(response => response.json())
        .then(horas => {
            console.log("🟡 Horas ocupadas recibidas del backend:", horas);
            
            document.querySelectorAll(".hora-btn").forEach(btn => {
                let horaBoton = btn.innerText.trim();
                let horaInicio = horaBoton.split('-')[0].trim(); // "22:00"
                
                btn.classList.remove("ocupada");
                btn.disabled = false;
                
                if(Array.isArray(horas)){
                    // COMPARACIÓN A PRUEBA DE SEGUNDOS:
                    // Solo comparamos los primeros 5 caracteres (HH:MM)
                    const ocupada = horas.some(h => {
                        const horaBackend = h.trim();          // "22:00:00"
                        const hRecortada = horaBackend.substring(0, 5); // "22:00"
                        return hRecortada === horaInicio;
                    });
                    
                    if(ocupada){
                        console.log(`🟢 Bloqueando hora: "${horaBoton}"`);
                        btn.classList.add("ocupada");
                        btn.disabled = true;
                        
                        // Si la hora ocupada estaba seleccionada, la deseleccionamos
                        if(horaSeleccionada === horaBoton || horaSeleccionada === horaInicio) {
                            btn.classList.remove("activa");
                            horaSeleccionada = null;
                        }
                    }
                }
            });
        })
        .catch(err => console.error("Error al cargar horas ocupadas:", err));
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
    }).then(result => {
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
                cargarTabla(paginaActual);
                cargarHorasOcupadas();
            })
            .catch(err => {
                console.error("Error:", err);
                Swal.fire("Error", "No se pudo cancelar la reservación", "error");
            });
        }
    });
}