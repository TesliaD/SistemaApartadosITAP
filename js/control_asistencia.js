const grupoSelect = document.getElementById('grupoAsistencia');
const reservacionSelect = document.getElementById('reservacionAsistencia');
const tablaBody = document.getElementById('tablaAsistenciaBody');
const historial = document.getElementById('historialAsistencia');
const resumen = document.getElementById('grupoResumen');
const ayuda = document.getElementById('ayudaAsistencia');
const btnTodos = document.getElementById('btnTodosPresentes');
const btnGuardar = document.getElementById('btnGuardarAsistencia');
const btnImprimir = document.getElementById('btnImprimirAsistencia');
let alumnosActuales = [];

const escaparHTML = valor => String(valor ?? '').replace(/[&<>'"]/g, caracter => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;' })[caracter]);
const fechaCorta = fecha => fecha ? fecha.split('-').reverse().join('/') : '';

function mostrarVacio(texto) {
    tablaBody.innerHTML = `<tr><td colspan="5" class="estado-vacio">${escaparHTML(texto)}</td></tr>`;
}

async function obtenerJSON(url, opciones = {}) {
    const respuesta = await fetch(url, opciones);
    const data = await respuesta.json();
    if (!respuesta.ok || data.error) throw new Error(data.error || 'No fue posible obtener la información');
    return data;
}

async function cargarGrupos() {
    try {
        const data = await obtenerJSON(`${BASE_URL}/controllers/obtener_control_asistencia.php?accion=grupos`);
        data.grupos.forEach(grupo => {
            const option = document.createElement('option');
            option.value = grupo.IDGrupo;
            option.textContent = `${grupo.carrera || 'Sin carrera'} · ${grupo.grupo || `${grupo.Semestre}°`} · ${grupo.maestro}`;
            grupoSelect.appendChild(option);
        });
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

function pintarResumen(grupo) {
    resumen.classList.remove('d-none');
    resumen.innerHTML = `
        <div class="resumen-item"><small>Grupo</small><strong>${escaparHTML(grupo.grupo || `${grupo.Semestre}° Semestre`)}</strong></div>
        <div class="resumen-item"><small>Carrera</small><strong>${escaparHTML(grupo.carrera || 'Sin carrera')}</strong></div>
        <div class="resumen-item"><small>Maestro asignado</small><strong>${escaparHTML(grupo.maestro || 'Sin asignar')}</strong></div>
        <div class="resumen-item"><small>Alumnos</small><strong>${escaparHTML(grupo.totalAlumnos)}</strong></div>`;
}

function pintarSesiones(sesiones, seleccionada = '') {
    reservacionSelect.disabled = false;
    reservacionSelect.innerHTML = '<option value="">Selecciona una práctica</option>';
    sesiones.forEach(sesion => {
        const option = document.createElement('option');
        option.value = sesion.IDReservacion;
        option.selected = String(sesion.IDReservacion) === String(seleccionada);
        option.textContent = `${fechaCorta(sesion.fecha)} · ${sesion.horaInicio}-${sesion.horaFin} · ${sesion.laboratorio} · ${sesion.Practica || 'Sin práctica'}`;
        reservacionSelect.appendChild(option);
    });
    if (!sesiones.length) reservacionSelect.innerHTML = '<option value="">El grupo no tiene prácticas activas</option>';
}

function pintarAlumnos(alumnos, puedeRegistrar) {
    alumnosActuales = alumnos;
    btnTodos.disabled = !puedeRegistrar || !alumnos.length;
    btnGuardar.disabled = !puedeRegistrar || !alumnos.length;
    if (btnImprimir) btnImprimir.disabled = !puedeRegistrar || !alumnos.length;
    if (!alumnos.length) return mostrarVacio('No hay alumnos registrados en este grupo.');

    tablaBody.innerHTML = alumnos.map((alumno, indice) => {
        const estado = alumno.asistio === null ? 'pendiente' : (Number(alumno.asistio) ? 'presente' : 'falta');
        const texto = estado === 'presente' ? 'Presente' : estado === 'falta' ? 'Falta' : 'Pendiente';
        return `<tr data-alumno="${alumno.IDAlumnos}" data-estado="${estado}">
            <td>${indice + 1}</td><td>${escaparHTML(alumno.NoControl)}</td><td>${escaparHTML(alumno.nombre)}</td><td>${escaparHTML(alumno.plan || '-')}</td>
            <td class="text-center"><button type="button" class="asistencia-switch ${estado}" data-alumno="${alumno.IDAlumnos}">${texto}</button></td>
        </tr>`;
    }).join('');
    tablaBody.querySelectorAll('.asistencia-switch').forEach(boton => boton.addEventListener('click', () => cambiarEstado(boton)));
}

function cambiarEstado(boton) {
    const orden = ['pendiente', 'presente', 'falta'];
    const fila = boton.closest('tr');
    const siguiente = orden[(orden.indexOf(fila.dataset.estado) + 1) % orden.length];
    fila.dataset.estado = siguiente;
    boton.className = `asistencia-switch ${siguiente}`;
    boton.textContent = siguiente === 'presente' ? 'Presente' : siguiente === 'falta' ? 'Falta' : 'Pendiente';
}

function pintarHistorial(registros) {
    if (!registros.length) {
        historial.innerHTML = '<div class="estado-vacio">Aún no existen prácticas para este grupo.</div>';
        return;
    }
    historial.innerHTML = registros.map(registro => `<button type="button" class="historial-item ${String(registro.IDReservacion) === reservacionSelect.value ? 'activo' : ''}" data-reservacion="${registro.IDReservacion}">
        <h6>${fechaCorta(registro.fecha)} · ${registro.horaInicio}-${registro.horaFin}</h6>
        <p>${escaparHTML(registro.laboratorio)} · ${escaparHTML(registro.Practica || 'Sin práctica')}</p>
        <div class="historial-estadisticas"><span class="est-presente">${registro.presentes} presentes</span><span class="est-falta">${registro.faltas} faltas</span><span class="est-pendiente">${registro.registrados} registrados</span></div>
    </button>`).join('');
    historial.querySelectorAll('.historial-item').forEach(item => item.addEventListener('click', () => {
        reservacionSelect.value = item.dataset.reservacion;
        cargarDetalle();
    }));
}

async function cargarDetalle() {
    const grupo = grupoSelect.value;
    if (!grupo) return;
    const reservacion = reservacionSelect.value;
    try {
        mostrarVacio('Cargando alumnos...');
        const data = await obtenerJSON(`${BASE_URL}/controllers/obtener_control_asistencia.php?accion=detalle&grupo=${encodeURIComponent(grupo)}&reservacion=${encodeURIComponent(reservacion)}`);
        pintarResumen(data.grupo);
        const encabezadoReporte = document.getElementById('encabezadoReporteAsistencia');
        if (encabezadoReporte) {
            const sesion = reservacionSelect.options[reservacionSelect.selectedIndex];
            encabezadoReporte.innerHTML = reservacion ? `<h4>CONTROL DE ASISTENCIA A PRÁCTICAS</h4><p><strong>Grupo:</strong> ${escaparHTML(data.grupo.grupo || '')} &nbsp; <strong>Maestro:</strong> ${escaparHTML(data.grupo.maestro || '')}</p><p><strong>Práctica:</strong> ${escaparHTML(sesion?.textContent || '')}</p>` : '';
        }
        pintarSesiones(data.sesiones, reservacion);
        const practicaSeleccionada = Boolean(reservacionSelect.value);
        pintarAlumnos(data.alumnos, practicaSeleccionada);
        pintarHistorial(data.historial);
        ayuda.textContent = practicaSeleccionada ? 'Marca presente, falta o pendiente para cada alumno.' : 'Selecciona una práctica para habilitar el registro.';
    } catch (error) {
        mostrarVacio(error.message);
        Swal.fire('Error', error.message, 'error');
    }
}

grupoSelect.addEventListener('change', () => {
    reservacionSelect.disabled = true;
    reservacionSelect.innerHTML = '<option value="">Cargando prácticas...</option>';
    cargarDetalle();
});
reservacionSelect.addEventListener('change', cargarDetalle);

btnTodos.addEventListener('click', () => tablaBody.querySelectorAll('.asistencia-switch').forEach(boton => {
    const fila = boton.closest('tr'); fila.dataset.estado = 'presente'; boton.className = 'asistencia-switch presente'; boton.textContent = 'Presente';
}));

btnGuardar.addEventListener('click', async () => {
    const reservacion = reservacionSelect.value;
    if (!reservacion) return;
    const asistencias = [...tablaBody.querySelectorAll('tr[data-alumno]')].map(fila => ({ alumno: Number(fila.dataset.alumno), asistio: fila.dataset.estado === 'presente' }));
    try {
        btnGuardar.disabled = true;
        const data = await obtenerJSON(`${BASE_URL}/controllers/guardar_asistencia_practica.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({reservacion, asistencias}) });
        Swal.fire({ icon:'success', title:'Asistencia guardada', text:data.mensaje, timer:1600, showConfirmButton:false });
        cargarDetalle();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
        btnGuardar.disabled = false;
    }
});

cargarGrupos();
if (btnImprimir) {
    btnImprimir.addEventListener('click', () => {
        if (!reservacionSelect.value) return;
        const seleccion = reservacionSelect.options[reservacionSelect.selectedIndex];
        const tituloAnterior = document.title;
        document.title = `Asistencia_${(seleccion?.textContent || 'practica').replace(/[^a-z0-9]+/gi, '_')}`;
        window.print();
        document.title = tituloAnterior;
    });
}