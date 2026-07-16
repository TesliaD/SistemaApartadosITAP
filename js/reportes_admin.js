// ==========================
// VARIABLES GLOBALES
// ==========================
let reporteSeleccionado = null;
let modalFechas = null;

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", function() {
    console.log("🔵 Reportes Admin - Iniciando...");
    
    const modalElement = document.getElementById("modalFechas");
    if(modalElement) {
        modalFechas = new bootstrap.Modal(modalElement);
    }

    // Fechas por defecto (mes actual)
    const hoy = new Date();
    const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    
    const fechaInicio = document.getElementById("modalFechaInicio");
    const fechaFin = document.getElementById("modalFechaFin");
    
    if(fechaInicio) fechaInicio.value = primerDiaMes.toISOString().split('T')[0];
    if(fechaFin) fechaFin.value = hoy.toISOString().split('T')[0];
});

// ==========================
// SELECCIONAR TIPO DE REPORTE
// ==========================
function seleccionarReporte(elemento) {
    document.querySelectorAll('.reporte-card').forEach(card => {
        card.classList.remove('seleccionado');
    });
    
    elemento.classList.add('seleccionado');
    reporteSeleccionado = elemento.dataset.reporte;
    
    document.getElementById("btnConfigurarReporte").disabled = false;
    
    const nombreReporte = elemento.querySelector('h6').textContent;
    console.log(`✅ Reporte seleccionado: ${nombreReporte}`);
}

// ==========================
// ABRIR MODAL DE FECHAS
// ==========================
function abrirModalFechas() {
    if(!reporteSeleccionado) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona un reporte',
            text: 'Primero debes seleccionar el tipo de reporte que deseas generar'
        });
        return;
    }
    
    const cardSeleccionada = document.querySelector(`.reporte-card[data-reporte="${reporteSeleccionado}"]`);
    const nombreReporte = cardSeleccionada ? cardSeleccionada.querySelector('h6').textContent : reporteSeleccionado;
    
    document.getElementById("tipoReporteSeleccionado").textContent = nombreReporte;
    
    // Generar filtros según el tipo de reporte
    generarFiltrosEspecificos(reporteSeleccionado);
    
    modalFechas.show();
}

// ==========================
// GENERAR FILTROS ESPECÍFICOS
// ==========================
function generarFiltrosEspecificos(tipo) {
    const container = document.getElementById("filtrosAdicionales");
    
    let html = '<div class="row g-3">';
    
    switch(tipo) {
        // ==========================
        // REPORTE 1: POR DEPARTAMENTO
        // ==========================
        case 'reporte_departamento':
            html += `
                <div class="col-md-6">
                    <label class="form-label fw-bold">Departamento</label>
                    <select id="modalFiltroDepartamento" class="form-select">
                        <option value="">Todos los departamentos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Laboratorio</label>
                    <select id="modalFiltroLab" class="form-select">
                        <option value="">Todos los laboratorios</option>
                    </select>
                </div>
            `;
            break;

        // ==========================
        // REPORTE 2: APARTADOS POR MAESTRO
        // ==========================
        case 'apartados_maestro':
            html += `
                <div class="col-md-6">
                    <label class="form-label fw-bold">Docente</label>
                    <select id="modalFiltroDocente" class="form-select">
                        <option value="">Todos los docentes</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Grupo</label>
                    <select id="modalFiltroGrupo" class="form-select">
                        <option value="">Todos los grupos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Laboratorio</label>
                    <select id="modalFiltroLab" class="form-select">
                        <option value="">Todos los laboratorios</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Estado</label>
                    <select id="modalFiltroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activa">Activas</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>
            `;
            break;

        // ==========================
        // REPORTE 3: REPORTE GENERAL DE RESERVACIONES
        // ==========================
        case 'reporte_general':
            html += `
                <div class="col-md-6">
                    <label class="form-label fw-bold">Departamento</label>
                    <select id="modalFiltroDepartamento" class="form-select">
                        <option value="">Todos los departamentos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Laboratorio</label>
                    <select id="modalFiltroLab" class="form-select">
                        <option value="">Todos los laboratorios</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Docente</label>
                    <select id="modalFiltroDocente" class="form-select">
                        <option value="">Todos los docentes</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Grupo</label>
                    <select id="modalFiltroGrupo" class="form-select">
                        <option value="">Todos los grupos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Carrera</label>
                    <select id="modalFiltroCarrera" class="form-select">
                        <option value="">Todas las carreras</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Estado</label>
                    <select id="modalFiltroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activa">Activas</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>
            `;
            break;
    }
    
    html += '</div>';
    container.innerHTML = html;
    
    // Cargar los datos de los selects
    cargarDatosSelects(tipo);
}

// ==========================
// CARGAR DATOS DE LOS SELECTS (FUNCIÓN QUE FALTABA)
// ==========================
function cargarDatosSelects(tipo) {
    console.log("🔄 Cargando datos de selects...");
    
    // Cargar Departamentos
    fetch('/SistemaApartadosITAP/controllers/obtener_departamentos.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modalFiltroDepartamento');
            if(select) {
                select.innerHTML = '<option value="">Todos los departamentos</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.IDDepartamentos;
                    option.textContent = item.nombre;
                    select.appendChild(option);
                });
                console.log("✅ Departamentos cargados:", data.length);
            }
        })
        .catch(err => console.error('Error cargando departamentos:', err));

    // Cargar Laboratorios
    fetch('/SistemaApartadosITAP/controllers/obtener_laboratorios.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modalFiltroLab');
            if(select) {
                select.innerHTML = '<option value="">Todos los laboratorios</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.IDLab;
                    option.textContent = item.Nombre;
                    select.appendChild(option);
                });
                console.log("✅ Laboratorios cargados:", data.length);
            }
        })
        .catch(err => console.error('Error cargando laboratorios:', err));

    // Cargar Docentes
    fetch('/SistemaApartadosITAP/controllers/obtener_docentes.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modalFiltroDocente');
            if(select) {
                select.innerHTML = '<option value="">Todos los docentes</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.IDUsuarios;
                    option.textContent = item.Nombre;
                    select.appendChild(option);
                });
                console.log("✅ Docentes cargados:", data.length);
            }
        })
        .catch(err => console.error('Error cargando docentes:', err));

    // Cargar Grupos
    fetch('/SistemaApartadosITAP/controllers/obtener_grupos.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('modalFiltroGrupo');
            if(select) {
                select.innerHTML = '<option value="">Todos los grupos</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.IDGrupo;
                    option.textContent = (item.Carrera || '') + ' - ' + (item.Nombre || item.Semestre + '°');
                    select.appendChild(option);
                });
                console.log("✅ Grupos cargados:", data.length);
            }
        })
        .catch(err => console.error('Error cargando grupos:', err));

    // Cargar Carreras (solo para reporte_general)
    if(tipo === 'reporte_general') {
        fetch('/SistemaApartadosITAP/controllers/obtener_carreras.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('modalFiltroCarrera');
                if(select) {
                    select.innerHTML = '<option value="">Todas las carreras</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.IDCarrera;
                        option.textContent = item.Nombre;
                        select.appendChild(option);
                    });
                    console.log("✅ Carreras cargadas:", data.length);
                }
            })
            .catch(err => console.error('Error cargando carreras:', err));
    }
}

// ==========================
// OBTENER FILTROS DEL MODAL
// ==========================
function obtenerFiltrosModal() {
    const filtros = {};
    
    const departamento = document.getElementById('modalFiltroDepartamento');
    if(departamento && departamento.value) filtros.departamento = departamento.value;
    
    const lab = document.getElementById('modalFiltroLab');
    if(lab && lab.value) filtros.laboratorio = lab.value;
    
    const docente = document.getElementById('modalFiltroDocente');
    if(docente && docente.value) filtros.docente = docente.value;
    
    const grupo = document.getElementById('modalFiltroGrupo');
    if(grupo && grupo.value) filtros.grupo = grupo.value;
    
    const estado = document.getElementById('modalFiltroEstado');
    if(estado && estado.value) filtros.estado = estado.value;
    
    const carrera = document.getElementById('modalFiltroCarrera');
    if(carrera && carrera.value) filtros.carrera = carrera.value;
    
    console.log("🔍 Filtros obtenidos:", filtros);
    return filtros;
}

// ==========================
// GENERAR REPORTE DESDE MODAL
// ==========================
function generarReporteDesdeModal() {
    const fechaInicio = document.getElementById("modalFechaInicio").value;
    const fechaFin = document.getElementById("modalFechaFin").value;
    
    if(!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Selecciona ambas fechas para generar el reporte'
        });
        return;
    }
    
    if(fechaInicio > fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas inválidas',
            text: 'La fecha de inicio no puede ser mayor a la fecha de fin'
        });
        return;
    }
    
    modalFechas.hide();
    
    Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    const datos = {
        tipo: reporteSeleccionado,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        filtros: obtenerFiltrosModal()
    };
    
    console.log("📊 Datos enviados:", datos);
    
    fetch('/SistemaApartadosITAP/controllers/generar_reporte_fechas.php', {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if(data.error) {
            Swal.fire({ icon: 'error', title: 'Error', text: data.error });
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
        console.error("Error:", err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar el reporte' });
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
        <div class="subtitulo">CENTRO DE CÓMPUTO</div>
        <h5 style="margin-top:10px;">${data.titulo || 'REPORTE'}</h5>
        <p style="font-size:12px; margin-top:5px;">
            Período: ${data.fechaInicio || ''} - ${data.fechaFin || ''}
        </p>
    </div>
    `;

    if(data.html) {
        html += data.html;
    } else {
        html += `<p class="text-center text-muted">No hay datos para mostrar</p>`;
    }

    html += `
    <div class="footer-reporte">
        <br>
        <span class="firma-linea"></span><br>
        <strong>Responsable del Centro de Cómputo</strong>
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
        Swal.fire({ icon: 'warning', title: 'Primero genera el reporte' });
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
    a.download = `Reporte_${new Date().toISOString().slice(0,10)}.xls`;
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
        Swal.fire({ icon: 'warning', title: 'Primero genera el reporte' });
        return;
    }

    const ventana = window.open('', '_blank', 'width=800,height=600');
    ventana.document.write(`
    <html>
    <head>
        <title>Reporte</title>
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