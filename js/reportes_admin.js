// ==========================
// VARIABLES GLOBALES
// ==========================
let reporteSeleccionado = null;
let modalFechas = null;
let filtrosGeneradosPara = null; // para no reiniciar los filtros al reabrir el modal
let ultimoReporte = null;        // datos del último reporte generado

// ==========================
// UTILIDADES DE FECHA
// ==========================

// "YYYY-MM-DD" en hora local (toISOString usa UTC y puede cambiar de día)
function fechaLocal(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

// "2026-11-03" -> "03/11/2026"
function fechaCorta(str) {
    if(!str) return '';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

// Rangos rápidos del modal
function rangoPreset(preset) {
    const hoy = new Date();
    const y = hoy.getFullYear();
    const m = hoy.getMonth();

    switch(preset) {
        case 'semana': {
            const inicio = new Date(hoy);
            const dia = (hoy.getDay() + 6) % 7; // lunes = 0
            inicio.setDate(hoy.getDate() - dia);
            return [inicio, hoy];
        }
        case 'mes':
            return [new Date(y, m, 1), hoy];
        case 'mesAnterior':
            return [new Date(y, m - 1, 1), new Date(y, m, 0)];
        case 'semestre':
            // Ene-Jun / Ago-Dic
            return m < 6 ? [new Date(y, 0, 1), new Date(y, 5, 30)] : [new Date(y, 7, 1), new Date(y, 11, 31)];
        case 'anio':
            return [new Date(y, 0, 1), new Date(y, 11, 31)];
    }
    return null;
}

function aplicarPreset(preset) {
    const rango = rangoPreset(preset);
    if(!rango) return;

    document.getElementById("modalFechaInicio").value = fechaLocal(rango[0]);
    document.getElementById("modalFechaFin").value = fechaLocal(rango[1]);

    document.querySelectorAll('.rpt-preset').forEach(b => {
        b.classList.toggle('activo', b.dataset.preset === preset);
    });
}

// Indicador de pasos (1 tipo, 2 filtros, 3 resultado)
function marcarPaso(actual) {
    [1, 2, 3].forEach(n => {
        const paso = document.getElementById(`paso${n}`);
        if(!paso) return;
        paso.classList.toggle('activo', n === actual);
        paso.classList.toggle('hecho', n < actual);
    });
}

// ==========================
// DOM READY
// ==========================
document.addEventListener("DOMContentLoaded", function() {
    const modalElement = document.getElementById("modalFechas");
    if(modalElement) {
        modalFechas = new bootstrap.Modal(modalElement);

        // Si se cierra el modal sin generar, el indicador vuelve al paso correcto
        modalElement.addEventListener('hidden.bs.modal', () => {
            marcarPaso(ultimoReporte ? 3 : 1);
        });
    }

    // Fechas por defecto: mes actual
    aplicarPreset('mes');

    document.querySelectorAll('.rpt-preset').forEach(btn => {
        btn.addEventListener('click', () => aplicarPreset(btn.dataset.preset));
    });

    // Si se editan las fechas a mano, se quita el rango rápido marcado
    ['modalFechaInicio', 'modalFechaFin'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            document.querySelectorAll('.rpt-preset').forEach(b => b.classList.remove('activo'));
        });
    });

    // Tarjetas accesibles con teclado
    document.querySelectorAll('.reporte-card').forEach(card => {
        card.addEventListener('keydown', e => {
            if(e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                seleccionarReporte(card);
            }
        });
    });
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

    const ayuda = document.getElementById("ayudaConfigurar");
    if(ayuda) {
        ayuda.innerHTML = `<i class="bi bi-check-circle"></i> Seleccionado: <b>${elemento.dataset.nombre}</b>`;
    }
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
    const nombreReporte = cardSeleccionada ? cardSeleccionada.dataset.nombre : reporteSeleccionado;

    document.getElementById("tipoReporteSeleccionado").textContent = nombreReporte;

    // Generar filtros según el tipo de reporte (solo si cambió el tipo,
    // así "Cambiar filtros" conserva lo que ya se había elegido)
    if(filtrosGeneradosPara !== reporteSeleccionado) {
        generarFiltrosEspecificos(reporteSeleccionado);
        filtrosGeneradosPara = reporteSeleccionado;
    }

    marcarPaso(2);
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
            <label class="form-label fw-bold">Departamento</label>
            <select id="modalFiltroDepartamento" class="form-select">
                <option value="">Todos los departamentos</option>
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
    fetch('../../controllers/obtener_departamentos.php')
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
    fetch('../../controllers/obtener_laboratorios.php')
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
    fetch('../../controllers/obtener_docentes.php')
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
    fetch('../../controllers/obtener_grupos.php')
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
        fetch('../../controllers/obtener_carreras.php')
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
    
    fetch('../../controllers/generar_reporte_fechas.php', {
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

        const preview = document.getElementById("reportePreview");
        preview.style.display = "block";
        preview.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
function escaparHTML(texto) {
    return String(texto ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function mostrarReporte(data) {
    ultimoReporte = data;
    const container = document.getElementById("contenidoReporte");

    const periodo = `${fechaCorta(data.fechaInicio)} al ${fechaCorta(data.fechaFin)}`;

    // Encabezado del documento (usa div/b, ver css/reporte_documento.css)
    let html = `
    <table class="rpt-encabezado">
        <tr>
            <td class="rpt-banda">
                <div class="rpt-institucion">INSTITUTO TECNOLÓGICO DE AGUA PRIETA</div>
                <div class="rpt-area">Centro de Cómputo · Sistema de Apartados</div>
            </td>
        </tr>
        <tr>
            <td class="rpt-titulo">${escaparHTML(data.titulo || 'REPORTE')}</td>
        </tr>
    </table>

    <table class="rpt-meta">
        <tr>
            <td><b>Período</b>${escaparHTML(periodo)}</td>
            ${data.departamento ? `<td><b>Departamento</b>${escaparHTML(data.departamento)}</td>` : ''}
            <td><b>Generado</b>${escaparHTML(data.generadoEl || '')}</td>
            <td><b>Elaboró</b>${escaparHTML(data.generadoPor || '')}</td>
        </tr>
    </table>
    `;

    html += data.html || `<div class="rpt-sin-datos">No hay datos para mostrar</div>`;

    html += `
    <table class="rpt-firmas">
        <tr>
            <td><div class="rpt-linea-firma">Elaboró</div>${escaparHTML(data.generadoPor || '')}</td>
            <td><div class="rpt-linea-firma">Responsable del Centro de Cómputo</div>&nbsp;</td>
        </tr>
    </table>
    <div class="rpt-pie">Documento generado por el Sistema de Apartados del Centro de Cómputo · ${escaparHTML(data.generadoEl || '')}</div>
    `;

    container.innerHTML = html;

    const resumen = document.getElementById("resumenPreview");
    if(resumen) {
        resumen.textContent = `${data.titulo || 'Reporte'} · ${periodo}`;
    }

    marcarPaso(3);
}

// ==========================
// CSS DEL DOCUMENTO (para imprimir y Excel)
// ==========================
let cssReporteCache = null;

function obtenerCssReporte() {
    if(cssReporteCache !== null) return Promise.resolve(cssReporteCache);

    const url = typeof URL_CSS_REPORTE !== 'undefined' ? URL_CSS_REPORTE : '../../css/reporte_documento.css';

    return fetch(url)
        .then(r => r.ok ? r.text() : '')
        .catch(() => '')
        .then(css => (cssReporteCache = css));
}

function nombreArchivoReporte() {
    const tipo = (ultimoReporte?.titulo || 'Reporte')
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_|_$/g, '');
    const inicio = ultimoReporte?.fechaInicio || '';
    const fin = ultimoReporte?.fechaFin || '';
    return `${tipo}_${inicio}_a_${fin}`;
}

function hayReporte() {
    const contenido = document.getElementById("contenidoReporte");
    if(!contenido || !contenido.innerHTML.trim()) {
        Swal.fire({ icon: 'warning', title: 'Primero genera el reporte' });
        return null;
    }
    return contenido;
}

// ==========================
// EXPORTAR A EXCEL
// ==========================
function exportarExcel() {
    const contenido = hayReporte();
    if(!contenido) return;

    obtenerCssReporte().then(css => {
        const htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office"
              xmlns:x="urn:schemas-microsoft-com:office:excel"
              xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <style>${css}</style>
        </head>
        <body>
            <div class="rpt-doc">${contenido.innerHTML}</div>
        </body>
        </html>`;

        const blob = new Blob(['\ufeff', htmlContent], { type: 'application/vnd.ms-excel;charset=utf-8' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${nombreArchivoReporte()}.xls`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
}

// ==========================
// IMPRIMIR
// ==========================
function imprimirReporte() {
    const contenido = hayReporte();
    if(!contenido) return;

    // La ventana se abre antes del fetch para que el navegador no la bloquee
    const ventana = window.open('', '_blank', 'width=1100,height=750');
    if(!ventana) {
        Swal.fire({ icon: 'warning', title: 'Permite las ventanas emergentes para imprimir' });
        return;
    }

    obtenerCssReporte().then(css => {
        ventana.document.write(`
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${escaparHTML(nombreArchivoReporte())}</title>
            <style>
                body { margin: 0; padding: 16px; background: #fff; }
                ${css}
            </style>
        </head>
        <body>
            <div class="rpt-doc">${contenido.innerHTML}</div>
        </body>
        </html>`);
        ventana.document.close();
        ventana.focus();
        setTimeout(() => ventana.print(), 400);
    });
}
