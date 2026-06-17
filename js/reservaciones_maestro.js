let horasSeleccionadas = [];
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

// ==========================
// VALIDACIONES DE FECHA
// ==========================
function configurarFechas() {
    if(!fecha) return;
    
    const hoy = new Date();
    const minFecha = new Date(hoy);
    minFecha.setDate(hoy.getDate() + 1); // +1 día
    
    const maxFecha = new Date(hoy);
    maxFecha.setDate(hoy.getDate() + 3); // +3 días
    
    const minFechaStr = minFecha.toISOString().split('T')[0];
    const maxFechaStr = maxFecha.toISOString().split('T')[0];
    
    fecha.min = minFechaStr;
    fecha.max = maxFechaStr;
    
    // Si la fecha actual está vacía o es menor a la mínima, establecer la mínima
    if(!fecha.value || fecha.value < minFechaStr) {
        fecha.value = minFechaStr;
    }
    
    // Validar al cambiar fecha
    fecha.addEventListener("change", validarFecha);
}

function validarFecha() {
    if(!fecha) return;
    
    const fechaSeleccionada = new Date(fecha.value);
    const diaSemana = fechaSeleccionada.getDay();
    
    // Domingo = 0
    if(diaSemana === 0) {
        Swal.fire("Error", "No se pueden hacer reservaciones en domingo", "error");
        // Resetear a la fecha mínima disponible
        const hoy = new Date();
        const minFecha = new Date(hoy);
        minFecha.setDate(hoy.getDate() + 1);
        // Avanzar hasta encontrar un día que no sea domingo
        while(minFecha.getDay() === 0) {
            minFecha.setDate(minFecha.getDate() + 1);
        }
        fecha.value = minFecha.toISOString().split('T')[0];
    }
    
    // Recargar horas ocupadas
    cargarHorasOcupadas();
}

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", () => {

    // Configurar fechas al inicio
    configurarFechas();

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
    document.getElementById("btnGuardar").addEventListener("click", () => {
        // VALIDACIONES DE FECHA
        if(!fecha.value){
            Swal.fire("Error", "Selecciona fecha", "error");
            return;
        }
        
        // Validar que no sea el día actual
        const fechaSeleccionada = new Date(fecha.value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if(fechaSeleccionada <= hoy) {
            Swal.fire("Error", "No puedes reservar para el día actual. Solo con 1 día de anticipación", "error");
            return;
        }
        
        // Validar diferencia máxima de 3 días
        const diffTime = fechaSeleccionada - hoy;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if(diffDays > 3) {
            Swal.fire("Error", "Solo puedes reservar con máximo 3 días de anticipación", "error");
            return;
        }
        
        // Validar que no sea domingo
        if(fechaSeleccionada.getDay() === 0) {
            Swal.fire("Error", "No se pueden hacer reservaciones en domingo", "error");
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
        
        if(!grupo.value){
            Swal.fire("Error", "Selecciona un grupo", "error");
            return;
        }
        
        let nombrePractica = practica.value;
        
        // FETCH - Guardar reservación
        fetch('/SistemaApartadosITAP/controllers/guardar_reservacion_maestro.php', {
            method: "POST",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fecha: fecha.value,
                horas: horasSeleccionadas,
                IDLab: lab.value,
                IDGrupo: grupo.value || null,
                software: software.value,
                Alumnos: alumnos.value || 0,
                Practica: nombrePractica
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) {
                Swal.fire("Error", data.error, "error");
                return;
            }
            
            Swal.fire("OK", data.mensaje, "success");
            
            // LIMPIAR
            horasSeleccionadas = [];
            document.querySelectorAll(".hora-btn").forEach(btn => {
                btn.classList.remove("activa");
            });
            practica.value = "";
            software.value = "";
            alumnos.value = "";
            
            // RECARGAR
            cargarTabla();
            cargarHorasOcupadas();
        })
        .catch(err => {
            console.error(err);
            Swal.fire("Error", "Error al guardar reservación", "error");
        });
    });

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
        buscar.addEventListener("keyup", () => cargarTabla(1));
    }

    // ==========================
    // EVENTOS
    // ==========================
    grupo.addEventListener("change", cargarAlumnos);
    fecha.addEventListener("change", cargarHorasOcupadas);
    lab.addEventListener("change", cargarHorasOcupadas);

    // ==========================
    // CARGA INICIAL
    // ==========================
    cargarTabla();
    cargarDatosMaestro(); // Cargar los grupos del maestro
});

// ==========================
// CARGAR GRUPOS DEL MAESTRO
// ==========================
function cargarDatosMaestro() {
    
    fetch('/SistemaApartadosITAP/controllers/obtener_grupos_maestro_reservacion.php')
        .then(res => res.json())
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

function cargarTabla(page = 1){
    paginaActual = page;
    let url = `/SistemaApartadosITAP/controllers/obtener_reservaciones_maestro.php?page=${page}`;
    
    // Filtros de fecha
    if(fechaInicio && fechaInicio.value){
        url += `&inicio=${fechaInicio.value}`;
    }
    if(fechaFin && fechaFin.value){
        url += `&fin=${fechaFin.value}`;
    }
    
    // Filtro de búsqueda
    if(buscar && buscar.value){
        url += `&buscar=${encodeURIComponent(buscar.value)}`;
    }
    
    // Filtro de estado
    const filtroEstado = document.getElementById("filtroEstado");
    if(filtroEstado && filtroEstado.value){
        url += `&estado=${encodeURIComponent(filtroEstado.value)}`;
    }
    
    console.log("Cargando URL:", url);
    
    fetch(url)
        .then(response => {
            console.log("Status:", response.status);
            if(!response.ok) {
                throw new Error("HTTP Error: " + response.status);
            }
            return response.text(); // Primero obtener como texto
        })
        .then(text => {
            console.log("Respuesta RAW:", text);
            
            // Limpiar caracteres extra
            text = text.trim();
            if (text.charCodeAt(0) === 0xFEFF) {
                text = text.substring(1);
            }
            text = text.replace(/¬+$/, '');
            
            if(!text || text === "") {
                throw new Error("Respuesta vacía del servidor");
            }
            
            try {
                const resp = JSON.parse(text);
                console.log("Respuesta parseada:", resp);
                
                if(resp.error) {
                    console.error("Error:", resp.error);
                    Swal.fire("Error", resp.error, "error");
                    return;
                }
                
                let html = "";
                if(resp.data && resp.data.length > 0) {
                    resp.data.forEach(r => {
                        // Normalizar estado (comparar en minúsculas)
                        const estadoNormalizado = (r.Estado || '').toLowerCase();
                        let estadoBadge = '';
                        
                        if(estadoNormalizado === 'cancelada' || estadoNormalizado === 'cancelado') {
                            estadoBadge = '<span class="badge bg-danger">Cancelada</span>';
                        } else if(estadoNormalizado === 'finalizada' || estadoNormalizado === 'finalizado') {
                            estadoBadge = '<span class="badge bg-secondary">Finalizada</span>';
                        } else {
                            estadoBadge = '<span class="badge bg-success">Activa</span>';
                        }
                        
                        // Mostrar botón de cancelar solo si está activa
                        const mostrarCancelar = estadoNormalizado === 'activa' || estadoNormalizado === 'activo';
                        
                        html += `
                            <tr>
                                <td>${r.fecha || 'N/A'}</td>
                                <td>${r.horario || r.horaInicio + ' - ' + r.horaFin}</td>
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
                                        `<button class="btn btn-secondary btn-sm" disabled>
                                            ${estadoNormalizado === 'cancelada' ? 'Cancelada' : 'Finalizada'}
                                        </button>`
                                    }
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="9" class="text-center">No hay reservaciones</td></tr>';
                }
                
                const tbody = document.querySelector("#tablaReservaciones tbody");
                if(tbody) tbody.innerHTML = html;
                generarPaginacion(resp.total || 0);
                
            } catch (e) {
                console.error("Error parseando JSON:", e);
                console.error("Texto que causó error:", text);
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
    let totalPaginas = Math.ceil(total / 10);
    let html = "";
    
    if(totalPaginas <= 1) {
        const cont = document.getElementById("paginacion");
        if(cont) cont.innerHTML = "";
        return;
    }
    
    for(let i = 1; i <= totalPaginas; i++){
        html += `
            <button class="btn ${i === paginaActual ? 'btn-primary' : 'btn-outline-primary'} me-1" 
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
// AUTOLLENAR ALUMNOS
// ==========================
function cargarAlumnos(){
    if(!grupo.value) return;
    
    let opcion = grupo.options[grupo.selectedIndex];
    let cantidad = opcion.dataset.alumnos || 0;
    alumnos.value = cantidad;
}

// ==========================
// HORAS OCUPADAS
// ==========================
function cargarHorasOcupadas(){
    if(!fecha.value || !lab.value) return;
    
    fetch(`/SistemaApartadosITAP/controllers/obtener_horas_ocupadas.php?fecha=${fecha.value}&lab=${lab.value}`)
        .then(res => res.json())
        .then(horas => {
            document.querySelectorAll(".hora-btn").forEach(btn => {
                let hora = btn.innerText.trim();
                btn.classList.remove("ocupada");
                btn.disabled = false;
                
                // Si la hora está ocupada, deshabilitar
                if(horas.includes(hora)){
                    btn.classList.add("ocupada");
                    btn.disabled = true;
                    
                    // Si estaba seleccionada, deseleccionar
                    if(btn.classList.contains("activa")) {
                        btn.classList.remove("activa");
                        horasSeleccionadas = horasSeleccionadas.filter(h => h !== hora);
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
    }).then(r => {
        if(r.isConfirmed){
            fetch('/SistemaApartadosITAP/controllers/cancelar_reservacion.php', {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            })
            .then(res => res.json())
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

// ==========================
// LIMPIAR FILTROS
// ==========================
function limpiarFiltros() {
    console.log("Limpiando filtros...");
    
    // Limpiar campos de fecha
    const fechaInicio = document.getElementById("fechaInicio");
    const fechaFin = document.getElementById("fechaFin");
    const buscar = document.getElementById("buscar");
    const filtroEstado = document.getElementById("filtroEstado");
    
    if(fechaInicio) fechaInicio.value = "";
    if(fechaFin) fechaFin.value = "";
    if(buscar) buscar.value = "";
    if(filtroEstado) filtroEstado.value = "";
    
    // Resetear a página 1
    paginaActual = 1;
    
    // Recargar la tabla sin filtros
    cargarTabla(1);
}