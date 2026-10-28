<?php
// No requiere autenticación - es pública
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones del Día - Centro de Cómputo ITAP</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Paleta global del proyecto (--primary, --secondary, --accent, --bg, --text, --gray) -->
    <link rel="stylesheet" href="/SistemaApartadosITAP/css/variablesglobales.css">

    <style>
        /* =========================
           VARIABLES / TEMAS
           Basadas en css/variablesglobales.css
        ========================= */
        body {
            --title: var(--primary);            /* #1d3557 */
            --surface: var(--white);            /* #ffffff */
            --surface-2: #f1f4f8;
            --border: #e2e8f0;
            --text-2: #2a4a7a;
            --text-3: var(--gray);              /* #757474 */
            --accent-ui: var(--secondary);      /* #457b9d */
            --accent-soft: #e8f0f5;
            --chip-active: var(--primary);
            --ok: #155724;
            --ok-soft: #d4edda;
            --warn: #856404;
            --warn-soft: #fff3cd;
            --muted-soft: #e2e3e5;
            --shadow: 0 4px 14px rgba(29, 53, 87, .08);
        }

        /* Modo oscuro: mismos tonos que el resto del sistema */
        body.dark {
            --title: #f8fafc;
            --surface: #1e293b;
            --surface-2: #0f172a;
            --border: #475569;
            --text-2: #cbd5e1;
            --text-3: #94a3b8;
            --accent-ui: #60a5fa;
            --accent-soft: rgba(69, 123, 157, .25);
            --chip-active: var(--secondary);
            --ok: #86efac;
            --ok-soft: rgba(40, 167, 69, .15);
            --warn: #fcd34d;
            --warn-soft: rgba(255, 193, 7, .15);
            --muted-soft: rgba(148, 163, 184, .12);
            --shadow: 0 4px 14px rgba(0, 0, 0, .4);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            transition: background-color .3s ease, color .3s ease;
        }

        .page {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 20px 32px;
        }

        /* =========================
           ENCABEZADO
        ========================= */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .topbar-title {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .title-icon {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            color: #fff;
            background: linear-gradient(135deg, #1d3557, #457b9d);
        }

        .topbar h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            color: var(--title);
        }

        .topbar .fecha {
            color: var(--text-2);
            font-size: .95rem;
            text-transform: capitalize;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reloj {
            font-variant-numeric: tabular-nums;
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--accent-ui);
            padding: 4px 16px;
            border-radius: 12px;
            background: var(--accent-soft);
        }

        .icon-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-2);
            display: grid;
            place-items: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
        }

        .icon-btn:hover {
            color: var(--accent-ui);
            border-color: var(--accent-ui);
        }

        /* =========================
           TARJETAS DE RESUMEN
        ========================= */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: .8rem;
            color: var(--text-2);
            margin-top: 4px;
        }

        .stat.total .stat-icon  { background: var(--accent-soft); color: var(--accent-ui); }
        .stat.curso .stat-icon  { background: var(--ok-soft); color: var(--ok); }
        .stat.proxima .stat-icon { background: var(--warn-soft); color: var(--warn); }
        .stat.labs .stat-icon   { background: var(--muted-soft); color: var(--text-2); }

        /* =========================
           FILTROS
        ========================= */
        .toolbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }

        .chips {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            flex: 1 1 auto;
            min-width: 0;
        }

        .chip {
            border: 1px solid var(--border);
            background: var(--surface-2);
            color: var(--text-2);
            border-radius: 999px;
            padding: 7px 14px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: .2s;
            white-space: nowrap;
        }

        .chip:hover {
            border-color: var(--accent-ui);
            color: var(--accent-ui);
        }

        .chip .count {
            font-size: .72rem;
            min-width: 22px;
            padding: 1px 7px;
            border-radius: 999px;
            background: var(--muted-soft);
            text-align: center;
        }

        .chip.active {
            background: var(--chip-active);
            border-color: var(--chip-active);
            color: #fff;
        }

        .chip.active .count {
            background: rgba(255, 255, 255, .25);
        }

        .search {
            position: relative;
            flex: 0 1 280px;
            min-width: 200px;
        }

        .search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
        }

        .search input {
            width: 100%;
            border: 1px solid var(--border);
            background: var(--surface-2);
            color: var(--text);
            border-radius: 12px;
            padding: 9px 12px 9px 36px;
            outline: none;
            transition: .2s;
        }

        .search input::placeholder {
            color: var(--text-3);
        }

        .search input:focus {
            border-color: var(--accent-ui);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        /* =========================
           TABLA
        ========================= */
        .board {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla thead th {
            background: var(--surface-2);
            color: var(--text-3);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .tabla tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text-2);
            font-size: .92rem;
        }

        .tabla tbody tr:last-child td {
            border-bottom: none;
        }

        .tabla tbody tr {
            transition: background .2s;
        }

        .tabla tbody tr:hover {
            background: var(--surface-2);
        }

        /* Reservación en curso */
        .tabla tbody tr.en-curso {
            background: var(--ok-soft);
            box-shadow: inset 4px 0 0 var(--ok);
        }

        /* Reservación terminada */
        .tabla tbody tr.terminada {
            opacity: .55;
        }

        .hora {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }

        .hora small {
            display: block;
            font-weight: 500;
            color: var(--text-3);
            font-size: .78rem;
        }

        .lab {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lab-num {
            min-width: 42px;
            height: 42px;
            padding: 0 8px;
            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--accent-ui);
            font-weight: 800;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .lab-nombre {
            font-weight: 600;
            color: var(--text);
        }

        .depto {
            display: inline-block;
            font-size: .78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
            background: var(--muted-soft);
            color: var(--text-2);
            white-space: nowrap;
        }

        .vacio {
            color: var(--text-3);
        }

        /* =========================
           BADGES DE ESTADO
        ========================= */
        .estado {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .estado.en-curso { background: var(--ok-soft); color: var(--ok); }
        .estado.proxima  { background: var(--warn-soft); color: var(--warn); }
        .estado.terminada { background: var(--muted-soft); color: var(--text-3); }

        .estado.en-curso::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulso 1.6s infinite;
        }

        @keyframes pulso {
            0%   { box-shadow: 0 0 0 0 currentColor; }
            70%  { box-shadow: 0 0 0 7px transparent; }
            100% { box-shadow: 0 0 0 0 transparent; }
        }

        /* =========================
           ESTADO VACÍO / CARGANDO
        ========================= */
        .mensaje {
            text-align: center;
            padding: 56px 20px;
            color: var(--text-3);
        }

        .mensaje i {
            font-size: 2.4rem;
            display: block;
            margin-bottom: 10px;
        }

        .mensaje strong {
            display: block;
            color: var(--text-2);
            font-size: 1.05rem;
            margin-bottom: 4px;
        }

        /* =========================
           PIE
        ========================= */
        .footer-info {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
            color: var(--text-3);
            font-size: .8rem;
        }

        .live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--ok);
            background: var(--ok-soft);
            padding: 2px 10px;
            border-radius: 999px;
            font-weight: 700;
        }

        .live::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 992px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .search {
                flex: 1 1 100%;
            }
        }

        /* En pantallas pequeñas cada fila se vuelve una tarjeta */
        @media (max-width: 768px) {
            .page {
                padding: 16px 12px 24px;
            }

            .topbar h1 {
                font-size: 1.25rem;
            }

            .title-icon {
                width: 44px;
                height: 44px;
                font-size: 1.25rem;
            }

            .reloj {
                font-size: 1.3rem;
                padding: 4px 12px;
            }

            .stat {
                padding: 12px 14px;
            }

            .stat-value {
                font-size: 1.3rem;
            }

            .chips {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 4px;
            }

            .board {
                background: transparent;
                border: none;
                box-shadow: none;
            }

            .tabla thead {
                display: none;
            }

            .tabla,
            .tabla tbody,
            .tabla tr,
            .tabla td {
                display: block;
                width: 100%;
            }

            .tabla tbody tr {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 12px 14px;
                margin-bottom: 12px;
                box-shadow: var(--shadow);
            }

            .tabla tbody tr:hover {
                background: var(--surface);
            }

            .tabla tbody td {
                border: none;
                padding: 6px 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                text-align: right;
            }

            .tabla tbody td::before {
                content: attr(data-label);
                font-size: .72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .6px;
                color: var(--text-3);
                text-align: left;
                flex-shrink: 0;
            }

            .tabla tbody td.mensaje {
                display: block;
                text-align: center;
            }

            .tabla tbody td.mensaje::before {
                content: none;
            }

            .hora small {
                display: inline;
                margin-left: 6px;
            }
        }
    </style>
</head>
<body>
<script>
    // Mismo tema que el resto del sistema (claro por defecto). Se aplica antes de pintar.
    try {
        if (localStorage.getItem('itap-theme') === 'dark') {
            document.body.classList.add('dark');
        }
    } catch (error) {}
</script>

<div class="page">

    <!-- ========================= -->
    <!-- ENCABEZADO -->
    <!-- ========================= -->
    <header class="topbar">
        <div class="topbar-title">
            <a href="#" class="icon-btn" id="btnRegresar" title="Regresar" aria-label="Regresar" hidden>
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="title-icon"><i class="bi bi-calendar2-week"></i></div>
            <div>
                <h1>Reservaciones del Día</h1>
                <div class="fecha" id="fechaActual"></div>
            </div>
        </div>

        <div class="topbar-actions">
            <div class="reloj" id="horaActual">--:--:--</div>
            <button class="icon-btn" id="btnTema" type="button" aria-label="Cambiar tema">
                <i class="bi bi-sun"></i>
            </button>
        </div>
    </header>

    <!-- ========================= -->
    <!-- RESUMEN -->
    <!-- ========================= -->
    <section class="stats">
        <div class="stat total">
            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Reservaciones hoy</div>
            </div>
        </div>
        <div class="stat curso">
            <div class="stat-icon"><i class="bi bi-broadcast"></i></div>
            <div>
                <div class="stat-value" id="statCurso">0</div>
                <div class="stat-label">En curso</div>
            </div>
        </div>
        <div class="stat proxima">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value" id="statProximas">0</div>
                <div class="stat-label">Próximas</div>
            </div>
        </div>
        <div class="stat labs">
            <div class="stat-icon"><i class="bi bi-pc-display"></i></div>
            <div>
                <div class="stat-value" id="statLabs">0</div>
                <div class="stat-label">Laboratorios ocupados ahora</div>
            </div>
        </div>
    </section>

    <!-- ========================= -->
    <!-- FILTROS -->
    <!-- ========================= -->
    <section class="toolbar">
        <div class="chips" id="chipsDepartamentos" role="tablist" aria-label="Filtrar por departamento">
            <button class="chip active" type="button" data-depto="">
                Todos <span class="count">0</span>
            </button>
        </div>

        <div class="search">
            <i class="bi bi-search"></i>
            <input type="search" id="buscar" placeholder="Buscar laboratorio, docente o grupo">
        </div>
    </section>

    <!-- ========================= -->
    <!-- TABLA -->
    <!-- ========================= -->
    <section class="board">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Laboratorio</th>
                    <th>Departamento</th>
                    <th>Docente</th>
                    <th>Grupo</th>
                    <th>Práctica</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
                <tr>
                    <td colspan="7" class="mensaje">
                        <i class="bi bi-arrow-repeat"></i>
                        <strong>Cargando reservaciones...</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- ========================= -->
    <!-- PIE -->
    <!-- ========================= -->
    <div class="footer-info">
        <span class="live">En vivo</span>
        <span>Actualización automática cada 60 segundos</span>
        <span>·</span>
        <span id="ultimaActualizacion"></span>
    </div>

</div>

<script>
    // =========================
    // ESTADO
    // =========================
    const estado = {
        reservaciones: [],
        departamentos: [],
        depto: new URLSearchParams(location.search).get('depto') || '',
        busqueda: ''
    };

    const THEME_KEY = 'itap-theme';

    // =========================
    // UTILIDADES
    // =========================

    // Fecha local en formato YYYY-MM-DD (toISOString usa UTC y cambia de día por la tarde).
    function fechaLocalISO(fecha = new Date()) {
        const y = fecha.getFullYear();
        const m = String(fecha.getMonth() + 1).padStart(2, '0');
        const d = String(fecha.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function escaparHTML(texto) {
        return String(texto ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // "07:00:00" -> "07:00"
    function horaCorta(hora) {
        return hora ? hora.slice(0, 5) : '--:--';
    }

    // "07:30:00" -> minutos desde medianoche
    function aMinutos(hora) {
        if (!hora) return null;
        const [h, m] = hora.split(':').map(Number);
        return h * 60 + m;
    }

    function duracion(inicio, fin) {
        const total = aMinutos(fin) - aMinutos(inicio);
        if (!(total > 0)) return '';
        const h = Math.floor(total / 60);
        const m = total % 60;
        return h && m ? `${h} h ${m} min` : h ? `${h} h` : `${m} min`;
    }

    // Estado según la hora actual: en-curso, proxima o terminada
    function estadoTiempo(res) {
        const est = (res.Estado || '').toLowerCase();
        if (est.startsWith('finaliz')) return 'terminada';

        const ahora = new Date();
        const minutos = ahora.getHours() * 60 + ahora.getMinutes();
        const ini = aMinutos(res.horaInicio);
        const fin = aMinutos(res.horaFin);

        if (ini === null || fin === null) return 'proxima';
        if (minutos >= fin) return 'terminada';
        if (minutos >= ini) return 'en-curso';
        return 'proxima';
    }

    const ETIQUETAS = {
        'en-curso': 'En curso',
        'proxima': 'Próxima',
        'terminada': 'Terminada'
    };

    // =========================
    // RELOJ
    // =========================
    function actualizarReloj() {
        const ahora = new Date();
        document.getElementById('horaActual').textContent =
            ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('fechaActual').textContent =
            ahora.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    // =========================
    // TEMA
    // =========================
    function aplicarTema(oscuro, guardar) {
        document.body.classList.toggle('dark', oscuro);
        document.querySelector('#btnTema i').className = oscuro ? 'bi bi-sun' : 'bi bi-moon';
        if (guardar) {
            try { localStorage.setItem(THEME_KEY, oscuro ? 'dark' : 'light'); } catch (e) {}
        }
    }

    // =========================
    // FILTROS
    // =========================
    function coincideBusqueda(res) {
        if (!estado.busqueda) return true;
        const texto = [res.laboratorio, res.numLab, res.docente, res.grupo, res.Practica, res.departamento]
            .join(' ')
            .toLowerCase();
        return texto.includes(estado.busqueda);
    }

    function filtrarPorDepto(lista) {
        if (!estado.depto) return lista;
        return lista.filter(r => String(r.IDDepartamento) === estado.depto);
    }

    function renderChips() {
        const cont = document.getElementById('chipsDepartamentos');
        const base = estado.reservaciones.filter(coincideBusqueda);

        const contar = id => base.filter(r => String(r.IDDepartamento) === String(id)).length;

        let html = `
            <button class="chip ${estado.depto === '' ? 'active' : ''}" type="button" data-depto="">
                Todos <span class="count">${base.length}</span>
            </button>`;

        estado.departamentos.forEach(d => {
            const id = String(d.IDDepartamentos);
            html += `
                <button class="chip ${estado.depto === id ? 'active' : ''}" type="button" data-depto="${escaparHTML(id)}">
                    ${escaparHTML(d.nombre)} <span class="count">${contar(id)}</span>
                </button>`;
        });

        cont.innerHTML = html;
    }

    // =========================
    // RENDER
    // =========================
    function render() {
        renderChips();

        const lista = filtrarPorDepto(estado.reservaciones).filter(coincideBusqueda);
        const tbody = document.getElementById('cuerpoTabla');

        // Resumen (respeta el departamento seleccionado)
        const delDepto = filtrarPorDepto(estado.reservaciones);
        const enCurso = delDepto.filter(r => estadoTiempo(r) === 'en-curso');
        document.getElementById('statTotal').textContent = delDepto.length;
        document.getElementById('statCurso').textContent = enCurso.length;
        document.getElementById('statProximas').textContent =
            delDepto.filter(r => estadoTiempo(r) === 'proxima').length;
        document.getElementById('statLabs').textContent =
            new Set(enCurso.map(r => r.IDLab)).size;

        if (lista.length === 0) {
            const hayFiltro = estado.depto || estado.busqueda;
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="mensaje">
                        <i class="bi ${hayFiltro ? 'bi-funnel' : 'bi-calendar2-check'}"></i>
                        <strong>${hayFiltro ? 'Sin resultados' : 'No hay reservaciones para hoy'}</strong>
                        ${hayFiltro ? 'Prueba con otro departamento o búsqueda.' : 'Los laboratorios están disponibles todo el día.'}
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = lista.map(res => {
            const est = estadoTiempo(res);
            const vacio = '<span class="vacio">—</span>';

            return `
                <tr class="${est}">
                    <td data-label="Horario">
                        <span class="hora">
                            ${horaCorta(res.horaInicio)} – ${horaCorta(res.horaFin)}
                            <small>${duracion(res.horaInicio, res.horaFin)}</small>
                        </span>
                    </td>
                    <td data-label="Laboratorio">
                        <div class="lab">
                            <span class="lab-num">${escaparHTML(res.numLab || '?')}</span>
                            <span class="lab-nombre">${escaparHTML(res.laboratorio || 'Sin nombre')}</span>
                        </div>
                    </td>
                    <td data-label="Departamento">
                        <span class="depto">${escaparHTML(res.departamento || 'Sin asignar')}</span>
                    </td>
                    <td data-label="Docente">${res.docente ? escaparHTML(res.docente) : vacio}</td>
                    <td data-label="Grupo">${res.grupo ? escaparHTML(res.grupo) : vacio}</td>
                    <td data-label="Práctica">${res.Practica ? escaparHTML(res.Practica) : vacio}</td>
                    <td data-label="Estado">
                        <span class="estado ${est}">${ETIQUETAS[est]}</span>
                    </td>
                </tr>`;
        }).join('');
    }

    function mostrarError(mensaje) {
        document.getElementById('cuerpoTabla').innerHTML = `
            <tr>
                <td colspan="7" class="mensaje">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>${escaparHTML(mensaje)}</strong>
                    Se intentará de nuevo automáticamente.
                </td>
            </tr>`;
    }

    // =========================
    // CARGA DE DATOS
    // =========================
    function cargarReservacionesDia() {
        fetch(`/SistemaApartadosITAP/controllers/obtener_reservaciones_dia.php?fecha=${fechaLocalISO()}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    mostrarError(data.error);
                    return;
                }

                estado.reservaciones = data.reservaciones || [];
                estado.departamentos = data.departamentos || [];

                // Si el departamento de la URL ya no existe, mostrar todos
                if (estado.depto && !estado.departamentos.some(d => String(d.IDDepartamentos) === estado.depto)) {
                    estado.depto = '';
                }

                document.getElementById('ultimaActualizacion').textContent =
                    'Última actualización: ' + new Date().toLocaleTimeString('es-MX');

                render();
            })
            .catch(err => {
                console.error('Error:', err);
                mostrarError('Error al cargar las reservaciones');
            });
    }

    // =========================
    // EVENTOS
    // =========================
    document.getElementById('chipsDepartamentos').addEventListener('click', e => {
        const chip = e.target.closest('.chip');
        if (!chip) return;

        estado.depto = chip.dataset.depto;

        // Guardar el filtro en la URL (útil para dejar una pantalla fija por departamento)
        const url = new URL(location.href);
        if (estado.depto) url.searchParams.set('depto', estado.depto);
        else url.searchParams.delete('depto');
        history.replaceState(null, '', url);

        render();
    });

    document.getElementById('buscar').addEventListener('input', e => {
        estado.busqueda = e.target.value.trim().toLowerCase();
        render();
    });

    document.getElementById('btnTema').addEventListener('click', () => {
        const oscuro = document.body.classList.contains('dark');
        aplicarTema(!oscuro, true);
    });

    // Botón regresar solo si se llegó desde otra página del sistema
    const btnRegresar = document.getElementById('btnRegresar');
    if (document.referrer && new URL(document.referrer).origin === location.origin) {
        btnRegresar.hidden = false;
        btnRegresar.addEventListener('click', e => {
            e.preventDefault();
            history.back();
        });
    }

    // =========================
    // INICIALIZAR
    // =========================
    aplicarTema(document.body.classList.contains('dark'), false);
    actualizarReloj();
    cargarReservacionesDia();

    setInterval(actualizarReloj, 1000);
    setInterval(cargarReservacionesDia, 60000);
    // Recalcular "en curso / próxima / terminada" sin esperar la recarga
    setInterval(() => estado.reservaciones.length && render(), 30000);
</script>

</body>
</html>
