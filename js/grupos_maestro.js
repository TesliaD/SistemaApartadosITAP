console.log("JS de grupos cargado");

let editando = false;
let idGrupoEditando = null;

document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM listo");
    cargarGrupos();
    
    const btnGuardar = document.getElementById("btnGuardarGrupo");
    if(btnGuardar) {
        btnGuardar.addEventListener("click", guardarGrupo);
        console.log("Evento guardar asignado");
    }
    
    const btnCancelar = document.getElementById("btnCancelar");
    if(btnCancelar) {
        btnCancelar.addEventListener("click", cancelarEdicion);
    }
    
    const formUpload = document.getElementById("formUploadAlumnos");
    if(formUpload) {
        formUpload.addEventListener("submit", subirAlumnos);
    }
});

function cargarGrupos() {
    console.log("Cargando grupos...");
    
    fetch('/SistemaApartadosITAP/controllers/obtener_grupos_maestro.php')
        .then(response => response.json())
        .then(data => {
            console.log("Datos recibidos:", data);
            
            if(data.error) {
                Swal.fire("Error", data.error, "error");
                return;
            }
            
            let html = "";
            if(data.length === 0) {
                html = '<tr><td colspan="8" class="text-center">No tienes grupos registrados</td></tr>';
            } else {
                data.forEach(grupo => {
                    const nombreGrupo = grupo.Nombre || grupo.Semestre + '° Semestre';
                    const tituloGrupo = `${grupo.Carrera} - ${nombreGrupo}`;
                    
                    html += `
                        <tr>
                            <td>${grupo.Carrera || 'N/A'}</td>
                            <td>${grupo.Semestre || '-'}°</td>
                            <td>${nombreGrupo}</td>
                            <td>${grupo.tipoGrupo || 'regular'}</td>
                            <td>${grupo.cantidadAlumnos || 0}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editarGrupo(${grupo.IDGrupo})">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarGrupo(${grupo.IDGrupo})">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                             </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="abrirModalAlumnos(${grupo.IDGrupo}, '${tituloGrupo}')">
                                    <i class="bi bi-upload"></i> Subir Alumnos
                                </button>
                             </td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="verListaAlumnos(${grupo.IDGrupo}, '${tituloGrupo}')">
                                    <i class="bi bi-eye"></i> Ver Lista
                                </button>
                             </td>
                         </tr>
                    `;
                });
            }
            
            const tbody = document.querySelector("#tablaGrupos tbody");
            if(tbody) tbody.innerHTML = html;
        })
        .catch(err => {
            console.error("Error:", err);
            Swal.fire("Error", "No se pudieron cargar los grupos", "error");
        });
}

function guardarGrupo() {
    console.log("Guardando grupo...");
    
    const carrera = document.getElementById("carrera").value;
    const semestre = document.getElementById("semestre").value;
    const cantidadAlumnos = document.getElementById("cantidadAlumnos").value;
    const nombreGrupo = document.getElementById("nombreGrupo").value;
    const tipoGrupo = document.getElementById("tipoGrupo").value;
    
    if(!carrera || !semestre || !cantidadAlumnos) {
        Swal.fire("Error", "Completa los campos obligatorios", "error");
        return;
    }
    
    const datos = {
        IDGrupo: idGrupoEditando,
        IDCarrera: parseInt(carrera),
        Semestre: parseInt(semestre),
        cantidadAlumnos: parseInt(cantidadAlumnos),
        Nombre: nombreGrupo,
        tipoGrupo: tipoGrupo
    };
    
    console.log("Enviando:", datos);
    
    fetch('/SistemaApartadosITAP/controllers/guardar_grupo_maestro.php', {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Respuesta:", data);
        if(data.error) {
            Swal.fire("Error", data.error, "error");
            return;
        }
        Swal.fire("Éxito", data.mensaje, "success");
        cancelarEdicion();
        cargarGrupos();
    })
    .catch(err => {
        console.error("Error:", err);
        Swal.fire("Error", "Error al guardar", "error");
    });
}

function editarGrupo(id) {
    console.log("Editando grupo:", id);
    
    fetch(`/SistemaApartadosITAP/controllers/obtener_grupo.php?id=${id}`)
        .then(response => response.json())
        .then(grupo => {
            console.log("Grupo a editar:", grupo);
            
            document.getElementById("carrera").value = grupo.IDCarrera;
            document.getElementById("semestre").value = grupo.Semestre;
            document.getElementById("cantidadAlumnos").value = grupo.cantidadAlumnos;
            document.getElementById("nombreGrupo").value = grupo.Nombre || '';
            document.getElementById("tipoGrupo").value = grupo.tipoGrupo || 'regular';
            
            editando = true;
            idGrupoEditando = id;
            
            document.getElementById("btnGuardarGrupo").innerHTML = '<i class="bi bi-pencil"></i> Actualizar';
            document.getElementById("btnCancelar").style.display = "inline-block";
        })
        .catch(err => {
            console.error("Error:", err);
            Swal.fire("Error", "No se pudo cargar el grupo", "error");
        });
}

function cancelarEdicion() {
    document.getElementById("carrera").value = "";
    document.getElementById("semestre").value = "";
    document.getElementById("cantidadAlumnos").value = "";
    document.getElementById("nombreGrupo").value = "";
    document.getElementById("tipoGrupo").value = "regular";
    
    editando = false;
    idGrupoEditando = null;
    
    document.getElementById("btnGuardarGrupo").innerHTML = '<i class="bi bi-save"></i> Guardar Grupo';
    document.getElementById("btnCancelar").style.display = "none";
}

function eliminarGrupo(id) {
    let nombreGrupo = "este grupo";
    
    const buttons = document.querySelectorAll(`[onclick="eliminarGrupo(${id})"]`);
    if(buttons.length > 0) {
        const row = buttons[0].closest('tr');
        if(row && row.cells[2]) {
            nombreGrupo = row.cells[2].innerText || 'este grupo';
        }
    }
    
    Swal.fire({
        title: "¿Eliminar grupo?",
        html: `¿Estás seguro de eliminar <strong>${nombreGrupo}</strong>?<br><br>
               <span class="text-danger">⚠️ Esta acción eliminará permanentemente:<br>
               • El grupo<br>
               • Todos los alumnos asociados a este grupo</span>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar todo",
        cancelButtonText: "Cancelar"
    }).then(result => {
        if(result.isConfirmed) {
            Swal.fire({
                title: "Eliminando...",
                text: "Por favor espera",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/SistemaApartadosITAP/controllers/eliminar_grupo_maestro.php', {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    Swal.fire("Error", data.error, "error");
                    return;
                }
                Swal.fire("Eliminado", data.mensaje, "success");
                cargarGrupos();
            })
            .catch(err => {
                console.error("Error:", err);
                Swal.fire("Error", "No se pudo eliminar el grupo", "error");
            });
        }
    });
}

function abrirModalAlumnos(grupoId, grupoNombre) {
    document.getElementById("grupoIdUpload").value = grupoId;
    const modal = new bootstrap.Modal(document.getElementById("modalAlumnos"));
    modal.show();
}

function subirAlumnos(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById("formUploadAlumnos"));
    const resultadoDiv = document.getElementById("resultadoUpload");
    
    resultadoDiv.innerHTML = '<div class="alert alert-info">Procesando archivo...</div>';
    
    fetch('/SistemaApartadosITAP/controllers/subir_alumnos_excel.php', {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log("Respuesta subida:", data);
        
        if(data.error) {
            resultadoDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
            return;
        }
        
        let mensajeHtml = `
            <div class="alert alert-success">
                <strong>¡Proceso completado!</strong><br>
                ✅ Alumnos procesados: ${data.procesados}<br>
                ⚠️ Duplicados ignorados: ${data.duplicados || 0}<br>
                ❌ Errores: ${data.errores || 0}<br>
                📊 Total: ${data.total || data.procesados}
        `;
        
        if(data.mensajes && data.mensajes.length > 0) {
            mensajeHtml += `<hr><strong>Detalles:</strong><ul>`;
            data.mensajes.slice(0, 10).forEach(msg => {
                mensajeHtml += `<li>${msg}</li>`;
            });
            if(data.mensajes.length > 10) {
                mensajeHtml += `<li><em>...y ${data.mensajes.length - 10} más</em></li>`;
            }
            mensajeHtml += `</ul>`;
        }
        
        mensajeHtml += `</div>`;
        resultadoDiv.innerHTML = mensajeHtml;
        
        if(data.procesados > 0 || data.duplicados > 0) {
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById("modalAlumnos"));
                if(modal) modal.hide();
                cargarGrupos(); // Recargar la tabla para actualizar el contador
            }, 3000);
        }
    })
    .catch(err => {
        console.error("Error:", err);
        resultadoDiv.innerHTML = `<div class="alert alert-danger">Error al procesar archivo: ${err.message}</div>`;
    });
}

// Función para ver la lista de alumnos
function verListaAlumnos(grupoId, grupoNombre) {
    console.log("Ver alumnos del grupo:", grupoId, grupoNombre);
    
    // Actualizar el título del modal
    document.getElementById("tituloGrupoAlumnos").innerText = grupoNombre;
    
    // Mostrar loading en la tabla
    const tbody = document.getElementById("tablaAlumnosBody");
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-muted">
                <i class="bi bi-hourglass-split"></i> Cargando alumnos...
            </td>
        </tr>
    `;
    
    // Abrir el modal
    const modal = new bootstrap.Modal(document.getElementById("modalVerAlumnos"));
    modal.show();
    
    // Cargar los alumnos
    fetch(`/SistemaApartadosITAP/controllers/obtener_alumnos_por_grupo.php?id=${grupoId}`)
        .then(response => response.json())
        .then(data => {
            console.log("Alumnos recibidos:", data);
            
            if(data.error) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error: ${data.error}
                        </td>
                    </tr>
                `;
                return;
            }
            
            if(data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-warning">
                            <i class="bi bi-emoji-frown"></i> No hay alumnos registrados en este grupo
                        </td>
                    </tr>
                `;
                return;
            }
            
            let html = "";
            data.forEach((alumno, index) => {
                html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${alumno.NoControl || 'N/A'}</td>
                        <td>${alumno.nombre || 'N/A'}</td>
                        <td>${alumno.plan || 'No registrado'}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        })
        .catch(err => {
            console.error("Error:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error al cargar los alumnos
                    </td>
                </tr>
            `;
        });
}