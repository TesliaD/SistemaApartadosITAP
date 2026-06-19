// ==========================
// VARIABLES GLOBALES
// ==========================
let reservacionesCargadas = [];

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", () => {
    console.log("🔵 Reportes Admin - Iniciando...");
    
    const filtroLab = document.getElementById("filtroLab");
    const filtroDocente = document.getElementById("filtroDocente");
    const filtroGrupo = document.getElementById("filtroGrupo");
    const fechaInicio = document.getElementById("fechaInicio");
    const fechaFin = document.getElementById("fechaFin");

    // Evento: Cambio de docente → Cargar grupos
    if(filtroDocente) {
        filtroDocente.addEventListener("change", function() {
            console.log("🔄 Cambió docente, cargando grupos...");
            cargarGrupos(this.value);
            cargarReservaciones(); // Recargar reservaciones
        });
    }

    // Eventos: Cambio en filtros → Recargar reservaciones
    if(filtroLab) {
        filtroLab.addEventListener("change", function() {
            console.log("🔄 Cambió laboratorio, recargando reservaciones...");
            cargarReservaciones();
        });
    }
    
    if(filtroGrupo) {
        filtroGrupo.addEventListener("change", function() {
            console.log("🔄 Cambió grupo, recargando reservaciones...");
            cargarReservaciones();
        });
    }
    
    if(fechaInicio) {
        fechaInicio.addEventListener("change", function() {
            console.log("🔄 Cambió fecha inicio, recargando reservaciones...");
            cargarReservaciones();
        });
    }
    
    if(fechaFin) {
        fechaFin.addEventListener("change", function() {
            console.log("🔄 Cambió fecha fin, recargando reservaciones...");
            cargarReservaciones();
        });
    }

    // Carga inicial
    console.log("🔄 Carga inicial: cargando reservaciones...");
    cargarReservaciones();
});

// ==========================
// CARGAR GRUPOS POR DOCENTE
// ==========================
function cargarGrupos(idDocente) {
    const grupoSelect = document.getElementById("filtroGrupo");
    if(!grupoSelect) return;

    if(!idDocente || idDocente === '') {
        grupoSelect.innerHTML = '<option value="">Todos</option>';
        return;
    }

    grupoSelect.innerHTML = '<option value="">Cargando grupos...</option>';

    fetch(`/SistemaApartadosITAP/controllers/obtener_grupos_docente.php?id=${idDocente}`)
    .then(response => {
        if(!response.ok) throw new Error('Error HTTP: ' + response.status);
        return response.json();
    })
    .then(data => {
        grupoSelect.innerHTML = '<option value="">Todos</option>';
        
        if(data.error) {
            console.error("Error:", data.error);
            grupoSelect.innerHTML = `<option value="">Error: ${data.error}</option>`;
            return;
        }
        
        if(!data || data.length === 0) {
            grupoSelect.innerHTML = '<option value="">No hay grupos para este docente</option>';
            return;
        }
        
        data.forEach(g => {
            const nombreGrupo = g.Nombre || `${g.Semestre}° Semestre`;
            const option = document.createElement('option');
            option.value = g.IDGrupo;
            option.textContent = `${g.Carrera || 'Sin carrera'} - ${nombreGrupo}`;
            grupoSelect.appendChild(option);
        });
        
        console.log(`✅ ${data.length} grupos cargados para el docente`);
    })
    .catch(err => {
        console.error("Error cargando grupos:", err);
        grupoSelect.innerHTML = '<option value="">Error al cargar grupos</option>';
    });
}

// ==========================
// CARGAR RESERVACIONES CON FILTROS
// ==========================
function cargarReservaciones() {
    const lab = document.getElementById("filtroLab")?.value || '';
    const docente = document.getElementById("filtroDocente")?.value || '';
    const grupo = document.getElementById("filtroGrupo")?.value || '';
    const inicio = document.getElementById("fechaInicio")?.value || '';
    const fin = document.getElementById("fechaFin")?.value || '';

    // Construir URL
    let url = `/SistemaApartadosITAP/controllers/obtener_reservaciones_filtro.php?`;
    const params = [];
    
    if(lab) params.push(`lab=${lab}`);
    if(docente) params.push(`docente=${docente}`);
    if(grupo) params.push(`grupo=${grupo}`);
    if(inicio) params.push(`inicio=${inicio}`);
    if(fin) params.push(`fin=${fin}`);
    
    url += params.join('&');
    
    // Si no hay filtros, mostrar reservaciones de los últimos 30 días
    if(params.length === 0) {
        const hoy = new Date();
        const hace30Dias = new Date();
        hace30Dias.setDate(hoy.getDate() - 30);
        url += `inicio=${hace30Dias.toISOString().split('T')[0]}&fin=${hoy.toISOString().split('T')[0]}`;
    }

    console.log("📡 Cargando reservaciones:", url);

    const select = document.getElementById("filtroReservacion");
    if(!select) return;
    
    select.innerHTML = '<option value="">⏳ Cargando reservaciones...</option>';
    select.disabled = true;

    fetch(url)
    .then(response => {
        if(!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        select.innerHTML = '<option value="">Seleccionar reservación</option>';
        select.disabled = false;
        
        if(data.error) {
            console.error("Error:", data.error);
            select.innerHTML = `<option value="">⚠️ ${data.error}</option>`;
            return;
        }
        
        if(!data || data.length === 0) {
            select.innerHTML = '<option value="">📭 No hay reservaciones disponibles</option>';
            reservacionesCargadas = [];
            return;
        }
        
        reservacionesCargadas = data;
        
        data.forEach(r => {
            const option = document.createElement('option');
            option.value = r.IDReservacion;
            const fecha = r.fecha || 'Sin fecha';
            const hora = r.horaInicio && r.horaFin ? `${r.horaInicio} - ${r.horaFin}` : 'Sin hora';
            const labNombre = r.laboratorio || 'Sin laboratorio';
            option.textContent = `${fecha} - ${hora} - ${labNombre}`;
            select.appendChild(option);
        });
        
        console.log(`✅ ${data.length} reservaciones cargadas`);
    })
    .catch(err => {
        console.error("❌ Error cargando reservaciones:", err);
        select.innerHTML = `<option value="">❌ Error al cargar reservaciones</option>`;
        select.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar las reservaciones',
            footer: err.message
        });
    });
}

// ==========================
// GENERAR REPORTE
// ==========================
function generarReporte() {
    const reservacionId = document.getElementById("filtroReservacion").value;
    
    if(!reservacionId) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona una reservación',
            text: 'Debes seleccionar una reservación para generar el reporte'
        });
        return;
    }

    console.log("📄 Generando reporte para reservación ID:", reservacionId);

    Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/SistemaApartadosITAP/controllers/generar_reporte_asistencia.php?id=${reservacionId}`)
    .then(response => {
        if(!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        Swal.close();
        
        if(data.error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
            });
            return;
        }
        
        mostrarReporte(data);
        document.getElementById("reportePreview").style.display = "block";
        
        Swal.fire({
            icon: 'success',
            title: 'Reporte generado',
            text: 'El reporte se ha generado correctamente',
            timer: 2000,
            showConfirmButton: false
        });
    })
    .catch(err => {
        Swal.close();
        console.error("Error generando reporte:", err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo generar el reporte',
            footer: err.message
        });
    });
}

// ==========================
// MOSTRAR REPORTE EN HTML
// ==========================
function mostrarReporte(data) {
    const container = document.getElementById("contenidoReporte");
    
    let html = `
    <div class="reporte-header">
        <h5>INSTITUTO TECNOLÓGICO DE AGUA PRIETA</h5>
        <div class="subtitulo">DEPARTAMENTO DE ${data.departamento || 'SISTEMAS'}</div>
        <h5 style="margin-top:10px;">CONTROL DE ASISTENCIA A PRÁCTICAS</h5>
    </div>

    <table>
        <tr>
            <td width="50%"><strong>Laboratorio:</strong> ${data.laboratorio || 'N/A'}</td>
            <td width="50%"><strong>Carrera:</strong> ${data.carrera || 'N/A'}</td>
        </tr>
        <tr>
            <td><strong>Nombre del Maestro(a):</strong> ${data.docente || 'N/A'}</td>
            <td><strong>Materia:</strong> ${data.materia || 'N/A'}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Nombre de la práctica:</strong> ${data.practica || 'N/A'}</td>
        </tr>
        <tr>
            <td><strong>Grupo:</strong> ${data.grupo || 'N/A'}</td>
            <td><strong>Fecha:</strong> ${data.fecha || 'N/A'} &nbsp;&nbsp; <strong>Hora:</strong> ${data.hora || 'N/A'}</td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Núm. de control</th>
                <th width="60%">Nombre del alumno(a)</th>
                <th width="15%">Firma</th>
            </tr>
        </thead>
        <tbody>
    `;

    if(data.alumnos && data.alumnos.length > 0) {
        data.alumnos.forEach((alumno, index) => {
            html += `
                <tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>${alumno.NoControl || ''}</td>
                    <td>${alumno.nombre || ''}</td>
                    <td style="text-align:center;">&nbsp;</td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="4" style="text-align:center; color:#999;">
                    No hay alumnos registrados para este grupo
                </td>
            </tr>
        `;
    }

    // Completar hasta 30 filas
    const alumnosCount = data.alumnos ? data.alumnos.length : 0;
    for(let i = alumnosCount + 1; i <= 30; i++) {
        html += `
            <tr>
                <td style="text-align:center;">${i}</td>
                <td></td>
                <td></td>
                <td style="text-align:center;">&nbsp;</td>
            </tr>
        `;
    }

    html += `
        </tbody>
    </table>

    <div class="footer-reporte">
        <br>
        <span class="firma-linea"></span><br>
        <strong>Nombre y firma del maestro(a)</strong>
    </div>
    `;

    container.innerHTML = html;
}

// ==========================
// EXPORTAR A EXCEL
// ==========================
function exportarExcel() {
    const contenido = document.getElementById("contenidoReporte");
    if(!contenido || !contenido.innerHTML) {
        Swal.fire({
            icon: 'warning',
            title: 'Primero genera el reporte',
            text: 'Debes generar el reporte antes de exportarlo'
        });
        return;
    }

    const htmlContent = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" 
          xmlns:x="urn:schemas-microsoft-com:office:excel" 
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta charset="UTF-8">
        <style>
            table { border-collapse: collapse; font-size: 11px; }
            td, th { border: 1px solid #000; padding: 4px 6px; }
            th { background: #e9ecef; text-align: center; }
        </style>
    </head>
    <body>
        ${contenido.innerHTML}
    </body>
    </html>
    `;

    const blob = new Blob([htmlContent], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Reporte_Asistencia_${new Date().toISOString().slice(0,10)}.xls`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// ==========================
// IMPRIMIR
// ==========================
function imprimirReporte() {
    const contenido = document.getElementById("contenidoReporte");
    if(!contenido || !contenido.innerHTML) {
        Swal.fire({
            icon: 'warning',
            title: 'Primero genera el reporte',
            text: 'Debes generar el reporte antes de imprimirlo'
        });
        return;
    }

    const ventana = window.open('', '_blank', 'width=800,height=600');
    ventana.document.write(`
    <html>
    <head>
        <title>Reporte de Asistencia</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            td, th { border: 1px solid #000; padding: 4px 6px; }
            th { background: #f1f1f1; text-align: center; }
            .reporte-header { text-align: center; margin-bottom: 20px; }
            .footer-reporte { margin-top: 30px; text-align: center; }
            .firma-linea { display: inline-block; width: 200px; border-top: 1px solid #000; margin-top: 30px; padding-top: 5px; }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        ${contenido.innerHTML}
    </body>
    </html>
    `);
    ventana.document.close();
    setTimeout(() => {
        ventana.print();
    }, 500);
}