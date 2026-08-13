// =========================
// VARIABLES
// =========================
let reservacionConfirmada = false;
let datosReservacion = null;

// =========================
// DOM READY - EVENTOS PARA VALIDAR RESERVACIÓN
// =========================
document.addEventListener("DOMContentLoaded", function() {
    const grupo = document.getElementById("filtroGrupo");
    const fecha = document.getElementById("fechaReporte");
    const hora = document.getElementById("horaReporte");

    if(grupo) grupo.addEventListener("change", verificarReservacion);
    if(fecha) fecha.addEventListener("change", verificarReservacion);
    if(hora) hora.addEventListener("change", verificarReservacion);
});

// =========================
// VERIFICAR RESERVACIÓN
// =========================
function verificarReservacion() {
    const grupoId = document.getElementById("filtroGrupo").value;
    const fecha = document.getElementById("fechaReporte").value;
    const hora = document.getElementById("horaReporte").value;

    const okDiv = document.getElementById("reservacionOk");
    const errorDiv = document.getElementById("reservacionError");
    const btnGenerar = document.getElementById("btnGenerar");
    const btnExcel = document.getElementById("btnExcel");
    const btnImprimir = document.getElementById("btnImprimir");

    // Ocultar mensajes previos
    okDiv.style.display = "none";
    errorDiv.style.display = "none";

    // Limpiar campos auto-llenados
    document.getElementById("laboratorioReporte").value = "";
    document.getElementById("practicaReporte").value = "";
    document.getElementById("softwareReporte").value = "";

    // Deshabilitar botones
    btnGenerar.disabled = true;
    btnExcel.disabled = true;
    btnImprimir.disabled = true;
    reservacionConfirmada = false;

    if(!grupoId || !fecha || !hora) {
        return;
    }

    // Extraer hora inicio (primer valor del rango)
    const horaInicio = hora.split(' - ')[0];

    // Consultar reservación
    fetch(`/SistemaApartadosITAP/controllers/verificar_reservacion_maestro.php?grupo=${grupoId}&fecha=${fecha}&hora=${horaInicio}`)
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                errorDiv.style.display = "block";
                document.getElementById("mensajeError").textContent = data.error;
                return;
            }

            if(data.existe) {
                // Reservación confirmada
                okDiv.style.display = "block";
                document.getElementById("mensajeOk").textContent = 
                    `Reservación confirmada: ${data.laboratorio} | ${data.practica || 'Sin práctica'}`;
                
                // Auto-llenar campos
                document.getElementById("laboratorioReporte").value = data.laboratorio || 'N/A';
                document.getElementById("practicaReporte").value = data.practica || '';
                document.getElementById("softwareReporte").value = data.software || '';

                // Guardar datos para generar reporte
                datosReservacion = data;
                reservacionConfirmada = true;

                // Habilitar botones
                btnGenerar.disabled = false;
                btnExcel.disabled = false;
                btnImprimir.disabled = false;

            } else {
                errorDiv.style.display = "block";
                document.getElementById("mensajeError").textContent = 
                    `No hay reservación para el grupo ${data.grupo || ''} en esta fecha y hora.`;
            }
        })
        .catch(err => {
            console.error("Error:", err);
            errorDiv.style.display = "block";
            document.getElementById("mensajeError").textContent = "Error al verificar reservación";
        });
}

// =========================
// GENERAR REPORTE
// =========================
function generarReporte() {
    if(!reservacionConfirmada || !datosReservacion) {
        Swal.fire("Error", "Primero verifica que exista una reservación", "warning");
        return;
    }

    const grupoId = document.getElementById("filtroGrupo").value;
    const fecha = document.getElementById("fechaReporte").value;
    const hora = document.getElementById("horaReporte").value;
    const practica = document.getElementById("practicaReporte").value;
    const software = document.getElementById("softwareReporte").value;

    Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const datos = {
        grupoId: grupoId,
        fecha: fecha,
        hora: hora,
        practica: practica,
        software: software,
        laboratorio: datosReservacion.laboratorio || '',
        reservacionId: datosReservacion.IDReservacion || null
    };

    fetch('/SistemaApartadosITAP/controllers/generar_reporte_asistencia_maestro.php', {
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

        mostrarReporte(data);
        document.getElementById("reportePreview").style.display = "block";

        Swal.fire({
            icon: 'success',
            title: 'Reporte generado',
            text: 'El reporte se ha generado correctamente',
            timer: 1500,
            showConfirmButton: false
        });
    })
    .catch(err => {
        Swal.close();
        console.error("Error:", err);
        Swal.fire("Error", "No se pudo generar el reporte", "error");
    });
}

// =========================
// MOSTRAR REPORTE
// =========================
function mostrarReporte(data) {
    const container = document.getElementById("contenidoReporte");

    let html = `
        <div class="reporte-header">
            <h5>INSTITUTO TECNOLÓGICO DE AGUA PRIETA</h5>
            <div class="subtitulo">Departamento De ${data.departamento}</div>
            <h5 style="margin-top:12px; font-size:1rem;">CONTROL DE ASISTENCIA A PRÁCTICAS</h5>
        </div>

        <table>
            <tr>
                <td width="50%"><strong>Laboratorio:</strong> ${data.laboratorio || 'N/A'}</td>
                <td width="50%"><strong>Carrera:</strong> ${data.carrera || 'N/A'}</td>
            </tr>
            <tr>
                <td><strong>Nombre del Maestro(a):</strong> ${data.docente || 'N/A'}</td>
                <td><strong>Materia:</strong> ${data.software || 'N/A'}</td>
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

// =========================
// EXPORTAR A EXCEL
// =========================
function exportarExcel() {
    const contenido = document.getElementById("contenidoReporte");
    if(!contenido || !contenido.innerHTML) {
        Swal.fire("Error", "Primero genera el reporte", "warning");
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

// =========================
// IMPRIMIR
// =========================
function imprimirReporte() {
    const contenido = document.getElementById("contenidoReporte");
    if(!contenido || !contenido.innerHTML) {
        Swal.fire("Error", "Primero genera el reporte", "warning");
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
    setTimeout(() => ventana.print(), 500);
}