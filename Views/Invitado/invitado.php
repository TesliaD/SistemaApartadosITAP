<?php

include("../../includes/auth_invitados.php");
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar_invitados.php");

?>

<style>

body {
    padding-top: 70px;
}

.navbar-fixed-top,
.navbar-sticky-top {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
}

.reservaciones-header {
    background: linear-gradient(
        135deg,
        var(--primary)
    );

    color: white;

    border-radius: 12px;

    padding: 25px;

    margin-bottom: 20px;
}

.reservaciones-header h2 {
    color: #00d4ff;
}

.filtro-card {
    border: none;
    border-radius: 12px;
}

.tabla-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.table thead {
    background: #0a0e17;
    color: #00d4ff;
}

.estado-badge {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
}

</style>


<div class="container mt-4">

    <!-- ================================================= -->
    <!-- ENCABEZADO -->
    <!-- ================================================= -->

    <div class="reservaciones-header">

        <div class="d-flex align-items-center">

            <div>

                <h2 class="mb-1">
                    <i class="bi bi-calendar-check"></i>

                    Reservaciones
                </h2>

                <p class="mb-0 text-light">

                    Consulta general de las reservaciones
                    del Centro de Cómputo ITAP.

                </p>

            </div>

        </div>

    </div>


    <!-- ================================================= -->
    <!-- FILTROS -->
    <!-- ================================================= -->

    <div class="card shadow-sm filtro-card p-3 mb-4">

        <h5 class="fw-bold mb-3">

            <i class="bi bi-funnel"></i>

            Filtrar reservaciones

        </h5>


        <div class="row g-3">


            <!-- FECHA INICIO -->

            <div class="col-md-3">

                <label
                    for="fechaInicio"
                    class="form-label fw-bold"
                >
                    Fecha inicio
                </label>

                <input
                    type="date"
                    id="fechaInicio"
                    class="form-control"
                >

            </div>


            <!-- FECHA FIN -->

            <div class="col-md-3">

                <label
                    for="fechaFin"
                    class="form-label fw-bold"
                >
                    Fecha fin
                </label>

                <input
                    type="date"
                    id="fechaFin"
                    class="form-control"
                >

            </div>


            <!-- DEPARTAMENTO -->

            <div class="col-md-3">

                <label
                    for="filtroDepartamento"
                    class="form-label fw-bold"
                >
                    Departamento
                </label>

                <select
                    id="filtroDepartamento"
                    class="form-select"
                >

                    <option value="">
                        Todos los departamentos
                    </option>

                </select>

            </div>


            <!-- LABORATORIO -->

            <div class="col-md-3">

                <label
                    for="filtroLaboratorio"
                    class="form-label fw-bold"
                >
                    Laboratorio
                </label>

                <select
                    id="filtroLaboratorio"
                    class="form-select"
                >

                    <option value="">
                        Todos los laboratorios
                    </option>

                </select>

            </div>


            <!-- BÚSQUEDA -->

            <div class="col-md-6">

                <label
                    for="buscar"
                    class="form-label fw-bold"
                >
                    Buscar
                </label>

                <input
                    type="text"
                    id="buscar"
                    class="form-control"
                    placeholder="Docente, grupo, práctica, software..."
                >

            </div>


            <!-- ESTADO -->

            <div class="col-md-3">

                <label
                    for="filtroEstado"
                    class="form-label fw-bold"
                >
                    Estado
                </label>

                <select
                    id="filtroEstado"
                    class="form-select"
                >

                    <option value="">
                        Todos los estados
                    </option>

                    <option value="activa">
                        Activas
                    </option>

                    <option value="cancelada">
                        Canceladas
                    </option>

                    <option value="finalizada">
                        Finalizadas
                    </option>

                </select>

            </div>


            <!-- BOTONES -->

            <div class="col-md-3">

                <label class="form-label fw-bold">
                    &nbsp;
                </label>

                <div class="d-flex gap-2">

                    <button
                        class="btn btn-primary flex-grow-1"
                        onclick="cargarTabla(1)"
                    >

                        <i class="bi bi-search"></i>

                        Filtrar

                    </button>


                    <button
                        class="btn btn-secondary"
                        onclick="limpiarFiltros()"
                        title="Limpiar filtros"
                    >

                        <i class="bi bi-x-circle"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- ================================================= -->
    <!-- TABLA -->
    <!-- ================================================= -->

    <div class="card shadow tabla-card">

        <div class="card-header bg-dark text-white">

            <i class="bi bi-list-ul"></i>

            Reservaciones registradas

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-hover align-middle"
                    id="tablaReservaciones"
                >

                    <thead class="text-center">

                        <tr>

                            <th>Fecha</th>

                            <th>Horario</th>

                            <th>Laboratorio</th>

                            <th>Departamento</th>

                            <th>Docente</th>

                            <th>Grupo</th>

                            <th>Práctica</th>

                            <th>Software</th>

                            <th>Estado</th>

                        </tr>

                    </thead>


                    <tbody class="text-center">

                        <tr>

                            <td
                                colspan="9"
                                class="py-4"
                            >

                                <div
                                    class="spinner-border text-primary"
                                ></div>

                                <div class="mt-2">

                                    Cargando reservaciones...

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- PAGINACIÓN -->

            <div
                id="paginacion"
                class="mt-3 text-center"
            ></div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script src="../../js/reservaciones_invitados.js"></script>

<script src="../../js/logout.js"></script>