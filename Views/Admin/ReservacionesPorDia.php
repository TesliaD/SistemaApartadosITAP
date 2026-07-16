<?php
// No requiere autenticación - es pública
include("../../includes/conexion.php");
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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a0e17;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        /* =========================
           HEADER
        ========================= */
        .header-public {
            text-align: center;
            padding: 20px 0 25px 0;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 25px;
        }
        .header-public h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #00d4ff;
            text-shadow: 0 0 30px rgba(0, 212, 255, 0.15);
        }
        .header-public h1 i {
            margin-right: 15px;
        }
        .header-public .fecha-actual {
            font-size: 1.2rem;
            color: #8899aa;
            margin-top: 5px;
        }
        .header-public .fecha-actual span {
            color: #fff;
            font-weight: 600;
        }
        .header-public .subtitulo {
            font-size: 0.9rem;
            color: #556677;
            margin-top: 8px;
        }
        .header-public .subtitulo i {
            margin-right: 6px;
        }

        /* =========================
           TABLA
        ========================= */
        .tabla-container {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .tabla-container .table {
            margin: 0;
            color: #e0e8f0;
            font-size: 0.95rem;
        }

        .tabla-container .table thead th {
            background: rgba(0, 212, 255, 0.08);
            color: #00d4ff;
            border-bottom: 2px solid rgba(0, 212, 255, 0.15);
            padding: 14px 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            position: sticky;
            top: 0;
            z-index: 10;
            text-align: center;
        }

        .tabla-container .table tbody td {
            padding: 12px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            text-align: center;
        }

        .tabla-container .table tbody tr {
            transition: background 0.2s ease;
        }
        .tabla-container .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .tabla-container .table tbody tr.sin-reservaciones td {
            padding: 40px 0;
            color: #445566;
            font-size: 1.1rem;
        }
        .tabla-container .table tbody tr.sin-reservaciones i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
            color: #334455;
        }

        /* =========================
           BADGES
        ========================= */
        .badge-estado {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-estado.activa {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
            border: 1px solid rgba(46, 213, 115, 0.2);
        }
        .badge-estado.cancelada {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.2);
        }
        .badge-estado.finalizada {
            background: rgba(87, 96, 111, 0.3);
            color: #8899aa;
            border: 1px solid rgba(87, 96, 111, 0.2);
        }

        .badge-departamento {
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.06);
            color: #8899aa;
            display: inline-block;
        }

        /* =========================
           AUTO-REFRESH
        ========================= */
        .auto-refresh-info {
            text-align: center;
            margin-top: 25px;
            padding: 12px 0;
            color: #445566;
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
        }
        .auto-refresh-info i {
            margin-right: 8px;
        }
        .auto-refresh-info .badge-refresh {
            background: rgba(0, 212, 255, 0.1);
            color: #00d4ff;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
        }

        /* =========================
           LABEL DE LABORATORIO EN TABLA
        ========================= */
        .lab-numero {
            font-weight: 700;
            color: #00d4ff;
            background: rgba(0, 212, 255, 0.08);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .lab-nombre {
            color: #aabbcc;
            font-size: 0.85rem;
        }

        /* =========================
           HORA DESTACADA
        ========================= */
        .hora-destacada {
            font-weight: 600;
            color: #fff;
            font-size: 0.9rem;
        }
        .hora-destacada i {
            color: #00d4ff;
            margin-right: 4px;
            font-size: 0.8rem;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 768px) {
            .header-public h1 {
                font-size: 1.5rem;
            }
            .header-public .fecha-actual {
                font-size: 1rem;
            }
            .tabla-container .table {
                font-size: 0.8rem;
            }
            .tabla-container .table thead th,
            .tabla-container .table tbody td {
                padding: 8px 6px;
            }
            .badge-estado {
                font-size: 0.6rem;
                padding: 2px 8px;
            }
            .lab-numero {
                font-size: 0.75rem;
                padding: 1px 8px;
            }
        }

        @media (max-width: 576px) {
            .tabla-container .table thead th {
                font-size: 0.6rem;
            }
            .tabla-container .table tbody td {
                font-size: 0.7rem;
                padding: 6px 4px;
            }
        }

        /* Scroll */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0e17;
        }
        ::-webkit-scrollbar-thumb {
            background: #1a2a3a;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #2a3a4a;
        }
    </style>
</head>
<body>

    <!-- ========================= -->
    <!-- HEADER PÚBLICO -->
    <!-- ========================= -->
    <div class="header-public">
        <h1>
            <i class="bi bi-calendar-check"></i> Reservaciones del Día
        </h1>
        <div class="fecha-actual">
            📅 <span id="fechaActual"></span> &nbsp;|&nbsp; ⏰ <span id="horaActual"></span>
        </div>
        <div class="subtitulo">
            <i class="bi bi-info-circle"></i> 
            Reservaciones activas de todos los laboratorios del campus
        </div>
    </div>

    <!-- ========================= -->
    <!-- CONTENEDOR PRINCIPAL -->
    <!-- ========================= -->
    <div class="container-fluid">
        <div class="tabla-container">
            <div class="table-responsive">
                <table class="table" id="tablaReservaciones">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Laboratorio</th>
                            <th style="min-width: 120px;">Departamento</th>
                            <th style="min-width: 130px;">Horario</th>
                            <th style="min-width: 150px;">Docente</th>
                            <th style="min-width: 120px;">Grupo</th>
                            <th style="min-width: 140px;">Práctica</th>
                            <th style="min-width: 100px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <!-- Se llena con JavaScript -->
                        <tr class="sin-reservaciones">
                            <td colspan="7">
                                <i class="bi bi-arrow-repeat"></i>
                                Cargando reservaciones...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- AUTO-REFRESH -->
    <!-- ========================= -->
    <div class="auto-refresh-info">
        <i class="bi bi-arrow-repeat"></i> Actualización automática cada 60 segundos &nbsp;|&nbsp; 
        <span id="ultimaActualizacion"></span>
        <span class="badge-refresh ms-2">En vivo</span>
    </div>

    <!-- ========================= -->
    <!-- SCRIPTS -->
    <!-- ========================= -->
    <script>
        // =========================
        // FUNCIÓN PARA FORMATEAR FECHAS
        // =========================
        function formatearFecha(fechaStr) {
            const fecha = new Date(fechaStr + 'T00:00:00');
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return fecha.toLocaleDateString('es-MX', opciones);
        }

        // =========================
        // FUNCIÓN PARA CAPITALIZAR
        // =========================
        function capitalizar(texto) {
            if(!texto) return '';
            return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
        }

        // =========================
        // FUNCIÓN PARA OBTENER RESERVACIONES DEL DÍA
        // =========================
        function cargarReservacionesDia() {
            const hoy = new Date();
            const fechaHoy = hoy.toISOString().split('T')[0];

            // Actualizar fecha y hora en el header
            document.getElementById('fechaActual').textContent = formatearFecha(fechaHoy);
            document.getElementById('horaActual').textContent = hoy.toLocaleTimeString('es-MX', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });

            fetch(`/SistemaApartadosITAP/controllers/obtener_reservaciones_dia.php?fecha=${fechaHoy}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('cuerpoTabla');
                    
                    // Actualizar hora de última actualización
                    document.getElementById('ultimaActualizacion').textContent = 
                        'Última actualización: ' + new Date().toLocaleTimeString('es-MX');

                    if(data.error) {
                        tbody.innerHTML = `
                            <tr class="sin-reservaciones">
                                <td colspan="7">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    ${data.error}
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    if(!data || data.length === 0) {
                        tbody.innerHTML = `
                            <tr class="sin-reservaciones">
                                <td colspan="7">
                                    <i class="bi bi-calendar-check" style="color:#2ed573;"></i>
                                    No hay reservaciones programadas para hoy
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    // Generar filas
                    let html = '';
                    data.forEach(res => {
                        // Clase para el badge de estado
                        const estadoClase = res.Estado || 'activa';
                        
                        // Mostrar práctica o "-" si no tiene
                        const practica = res.Practica ? res.Practica : '—';
                        
                        html += `
                            <tr>
                                <td>
                                    <span class="lab-numero">Lab ${res.numLab || 'N/A'}</span>
                                    <br>
                                    <span class="lab-nombre">${res.laboratorio || 'N/A'}</span>
                                </td>
                                <td>
                                    <span class="badge-departamento">${res.departamento || 'Sin asignar'}</span>
                                </td>
                                <td>
                                    <span class="hora-destacada">
                                        <i class="bi bi-clock"></i> ${res.horaInicio || ''} - ${res.horaFin || ''}
                                    </span>
                                </td>
                                <td>${res.docente || 'N/A'}</td>
                                <td>${res.grupo || '—'}</td>
                                <td>${practica}</td>
                                <td>
                                    <span class="badge-estado ${estadoClase}">
                                        ${capitalizar(estadoClase)}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = html;
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('cuerpoTabla').innerHTML = `
                        <tr class="sin-reservaciones">
                            <td colspan="7">
                                <i class="bi bi-exclamation-triangle"></i>
                                Error al cargar las reservaciones
                            </td>
                        </tr>
                    `;
                });
        }

        // =========================
        // INICIALIZAR
        // =========================
        document.addEventListener('DOMContentLoaded', function() {
            cargarReservacionesDia();

            // Auto-refresh cada 60 segundos
            setInterval(cargarReservacionesDia, 60000);
        });
    </script>

</body>
</html>