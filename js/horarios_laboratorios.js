let horariosSeleccionados = [];
let laboratorioActual = null;

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", function() {
    const labSelect = document.getElementById("labSeleccionado");
    const horariosBtns = document.querySelectorAll(".hora-horario");
    const btnGuardar = document.getElementById("btnGuardarHorarios");

    // ==========================
    // CARGAR HORARIOS AL SELECCIONAR LABORATORIO
    // ==========================
    if(labSelect) {
        labSelect.addEventListener("change", function() {
            const labId = this.value;
            laboratorioActual = labId;
            
            if(!labId) {
                // Resetear todo
                horariosSeleccionados = [];
                document.querySelectorAll(".hora-horario").forEach(btn => {
                    btn.classList.remove("activa", "deshabilitada");
                });
                document.getElementById("tablaHorarios").querySelector("tbody").innerHTML = 
                    `<tr><td colspan="5" class="text-center py-3">Selecciona un laboratorio para ver sus horarios</td></tr>`;
                return;
            }

            // Cargar horarios guardados para este laboratorio
            cargarHorariosLaboratorio(labId);
        });
    }

    // ==========================
    // SELECCIONAR/DESELECCIONAR HORARIO
    // ==========================
    horariosBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            if(!laboratorioActual) {
                Swal.fire("Error", "Primero selecciona un laboratorio", "warning");
                return;
            }

            const hora = this.dataset.hora;
            
            // Si está deshabilitada, no hacer nada
            if(this.classList.contains("deshabilitada")) {
                // Habilitarla (quitar deshabilitada y poner activa)
                this.classList.remove("deshabilitada");
                this.classList.add("activa");
                if(!horariosSeleccionados.includes(hora)) {
                    horariosSeleccionados.push(hora);
                }
                return;
            }

            // Si está activa, desactivarla
            if(this.classList.contains("activa")) {
                this.classList.remove("activa");
                this.classList.add("deshabilitada");
                horariosSeleccionados = horariosSeleccionados.filter(h => h !== hora);
                return;
            }

            // Si no tiene estado, activarla
            if(!this.classList.contains("activa") && !this.classList.contains("deshabilitada")) {
                this.classList.add("activa");
                if(!horariosSeleccionados.includes(hora)) {
                    horariosSeleccionados.push(hora);
                }
            }
        });
    });

    // ==========================
    // GUARDAR HORARIOS
    // ==========================
    if(btnGuardar) {
        btnGuardar.addEventListener("click", function() {
            if(!laboratorioActual) {
                Swal.fire("Error", "Selecciona un laboratorio", "warning");
                return;
            }

            // Obtener el día seleccionado
            const diaSelect = document.getElementById("diaAplicar");
            const dia = diaSelect ? diaSelect.value : 'todos';

            // Obtener las horas activas
            const horasActivas = [];
            document.querySelectorAll(".hora-horario.activa").forEach(btn => {
                horasActivas.push(btn.dataset.hora);
            });

            if(horasActivas.length === 0) {
                Swal.fire("Error", "Selecciona al menos un horario habilitado", "warning");
                return;
            }

            // Preparar datos
            const datos = {
                idLab: laboratorioActual,
                dia: dia,
                horas: horasActivas
            };

            Swal.fire({
                title: 'Guardando horarios...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('/SistemaApartadosITAP/controllers/guardar_horarios_laboratorio.php', {
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

                Swal.fire("Éxito", data.mensaje || "Horarios guardados correctamente", "success");
                
                // Recargar horarios del laboratorio
                cargarHorariosLaboratorio(laboratorioActual);
            })
            .catch(err => {
                Swal.close();
                console.error("Error:", err);
                Swal.fire("Error", "Error al guardar horarios", "error");
            });
        });
    }
});

// ==========================
// CARGAR HORARIOS DEL LABORATORIO (CORREGIDO)
// ==========================
function cargarHorariosLaboratorio(idLab) {
    const tbody = document.querySelector("#tablaHorarios tbody");
    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">⏳ Cargando horarios...</td></tr>`;

    fetch(`/SistemaApartadosITAP/controllers/obtener_horas_ocupadas.php?id=${idLab}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Primero como texto para depurar
        })
        .then(text => {
            console.log("Respuesta cruda del servidor:", text);

            // Intentar parsear JSON
            try {
                const data = JSON.parse(text);
                procesarHorarios(data);
            } catch (e) {
                console.error("Error parseando JSON:", e);
                console.error("Texto que causó el error (primeros 500 chars):", text.substring(0, 500));
                Swal.fire({
                    icon: 'error',
                    title: 'Error de formato',
                    text: 'El servidor no devolvió un JSON válido. Revisa la consola para más detalles.'
                });
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger">❌ Error al cargar horarios</td></tr>`;
            }
        })
        .catch(err => {
            console.error("Error en fetch:", err);
            Swal.fire("Error", "No se pudieron cargar los horarios: " + err.message, "error");
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger">❌ Error al cargar horarios</td></tr>`;
        });
}

// ==========================
// PROCESAR HORARIOS
// ==========================
function procesarHorarios(data) {
    // Resetear botones
    document.querySelectorAll(".hora-horario").forEach(btn => {
        btn.classList.remove("activa", "deshabilitada");
    });

    if (data.error) {
        Swal.fire("Error", data.error, "error");
        return;
    }

    // Si hay horarios habilitados, marcarlos como activos
    if (data.horarios && data.horarios.length > 0) {
        const horasActivas = data.horarios;
        horasActivas.forEach(hora => {
            document.querySelectorAll(".hora-horario").forEach(btn => {
                if (btn.dataset.hora === hora) {
                    btn.classList.add("activa");
                }
            });
        });
        horariosSeleccionados = [...horasActivas];
    }

    // Actualizar tabla de horarios guardados
    const tbody = document.querySelector("#tablaHorarios tbody");
    if (data.todos && data.todos.length > 0) {
        let html = '';
        data.todos.forEach(item => {
            const estadoBadge = item.habilitado === 1 ?
                '<span class="badge bg-success">Habilitado</span>' :
                '<span class="badge bg-danger">Deshabilitado</span>';
            html += `
                <tr>
                    <td>${item.laboratorio}</td>
                    <td>${item.dia}</td>
                    <td>${item.hora}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminarHorario(${item.IDHorario})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">📭 No hay horarios configurados para este laboratorio</td></tr>`;
    }
}

// Separar la lógica de procesamiento para claridad
function procesarHorarios(data) {
    // Resetear botones
    document.querySelectorAll(".hora-horario").forEach(btn => {
        btn.classList.remove("activa", "deshabilitada");
    });

    if(data.error) {
        Swal.fire("Error", data.error, "error");
        return;
    }

    // Si hay horarios habilitados, marcarlos como activos
    if(data.horarios && data.horarios.length > 0) {
        const horasActivas = data.horarios;
        horasActivas.forEach(hora => {
            document.querySelectorAll(".hora-horario").forEach(btn => {
                if(btn.dataset.hora === hora) {
                    btn.classList.add("activa");
                }
            });
        });
        horariosSeleccionados = [...horasActivas];
    }

    // Actualizar tabla de horarios guardados
    const tbody = document.querySelector("#tablaHorarios tbody");
    if(data.todos && data.todos.length > 0) {
        let html = '';
        data.todos.forEach(item => {
            const estadoBadge = item.habilitado === 1 ? 
                '<span class="badge bg-success">Habilitado</span>' : 
                '<span class="badge bg-danger">Deshabilitado</span>';
            html += `
                <tr>
                    <td>${item.laboratorio}</td>
                    <td>${item.dia}</td>
                    <td>${item.hora}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminarHorario(${item.IDHorario})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3">No hay horarios configurados para este laboratorio</td></tr>`;
    }
}
// ==========================
// ELIMINAR HORARIO
// ==========================
function eliminarHorario(id) {
    Swal.fire({
        title: "¿Eliminar horario?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(result => {
        if(result.isConfirmed) {
            fetch('/SistemaApartadosITAP/controllers/eliminar_horario_laboratorio.php', {
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
                Swal.fire("Eliminado", data.mensaje, "success");
                // Recargar horarios
                const labSelect = document.getElementById("labSeleccionado");
                if(labSelect && labSelect.value) {
                    cargarHorariosLaboratorio(labSelect.value);
                }
            })
            .catch(err => {
                console.error("Error:", err);
                Swal.fire("Error", "No se pudo eliminar el horario", "error");
            });
        }
    });
}