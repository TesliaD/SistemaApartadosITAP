<?php 
include("../../includes/auth.php"); 
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
?>

<style>
    /* =========================
       ESTILOS GENERALES
    ========================= */
    body {
        background: #f0f4f8;
        padding-top: 70px;
    }

    .badge-admin {
        background: #4f46e5;
        color: white;
        font-size: 0.7rem;
        padding: 4px 14px;
        border-radius: 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* =========================
       CARD MÁS GRANDE
    ========================= */
    .card-modern {
        background: #ffffff;
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        padding: 0;
        max-width: 1200px;
        margin: 0 auto;
    }

    .card-modern:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
    }

    .card-modern .card-header-modern {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 20px 30px;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-modern .card-header-modern h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.4rem;
        letter-spacing: 0.5px;
    }

    .card-modern .card-header-modern h4 i {
        margin-right: 14px;
        color: #818cf8;
    }

    .card-modern .card-header-modern .badge-admin {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 4px 18px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .card-modern .card-body-modern {
        padding: 30px 35px;
    }

    /* =========================
       ETIQUETAS
    ========================= */
    .form-label-modern {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .form-label-modern .required {
        color: #ef4444;
        margin-left: 2px;
    }

    /* =========================
       CAMPOS
    ========================= */
    .form-control-modern,
    .form-select-modern {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #f8fafc;
        height: 50px;
    }

    .form-control-modern:focus,
    .form-select-modern:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        background: #ffffff;
    }

    .form-control-modern:disabled,
    .form-control-modern[readonly] {
        background: #f1f4f8;
        cursor: not-allowed;
    }

    textarea.form-control-modern {
        height: auto;
        min-height: 80px;
        resize: vertical;
    }

    /* =========================
       BOTONES DE HORAS
    ========================= */
    .hora-btn-modern {
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        color: #1e293b;
        border-radius: 10px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        min-width: 100px;
        text-align: center;
        cursor: pointer;
        user-select: none;
    }

    .hora-btn-modern:hover:not(.ocupada):not(.activa) {
        background: #eef2ff;
        border-color: #818cf8;
        transform: translateY(-2px);
    }

    .hora-btn-modern.activa {
        background: #4f46e5;
        border-color: #4f46e5;
        color: white;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35);
    }

    .hora-btn-modern.ocupada {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #991b1b;
        cursor: not-allowed;
        opacity: 0.5;
        text-decoration: line-through;
    }

    .hora-btn-modern.ocupada::after {
        content: " ❌";
        font-size: 0.7rem;
    }

    /* =========================
       BOTÓN GUARDAR
    ========================= */
    .btn-guardar-modern {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        border: none;
        border-radius: 14px;
        padding: 14px 35px;
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
        width: 100%;
    }

    .btn-guardar-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(79, 70, 229, 0.4);
        background: linear-gradient(135deg, #4338ca, #3730a3);
    }

    .btn-guardar-modern:active {
        transform: translateY(0px);
    }

    .btn-guardar-modern i {
        margin-right: 12px;
    }

    /* =========================
       DIVISOR
    ========================= */
    .divider-modern {
        border: none;
        border-top: 2px dashed #e2e8f0;
        margin: 25px 0;
    }

    /* =========================
       INFO LABEL
    ========================= */
    .info-label {
        color: #64748b;
        font-size: 0.75rem;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-label i {
        font-size: 0.9rem;
        color: #818cf8;
    }

    /* =========================
       RESPONSIVE
    ========================= */
    @media (max-width: 768px) {
        .card-modern .card-body-modern {
            padding: 20px;
        }
        .hora-btn-modern {
            font-size: 0.7rem;
            padding: 6px 12px;
            min-width: 75px;
        }
        .card-modern .card-header-modern h4 {
            font-size: 1rem;
        }
        .card-modern .card-header-modern .badge-admin {
            font-size: 0.6rem;
            padding: 2px 10px;
        }
        .form-control-modern,
        .form-select-modern {
            padding: 10px 14px;
            font-size: 0.9rem;
            height: 44px;
        }
    }

    @media (max-width: 576px) {
        .card-modern .card-body-modern {
            padding: 15px;
        }
        .hora-btn-modern {
            font-size: 0.65rem;
            padding: 4px 8px;
            min-width: 60px;
        }
    }
</style>

<div class="container mt-4">

    <div class="card-modern">

        <!-- HEADER -->
        <div class="card-header-modern">
            <h4>
                <i class="bi bi-calendar-check"></i> Nueva Reservación
            </h4>
            <span class="badge-admin">
                <i class="bi bi-shield-lock"></i> Administrador
            </span>
        </div>

        <!-- BODY -->
        <div class="card-body-modern">

            <div class="row g-4">

                <!-- FECHA -->
                <div class="col-md-3">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar3"></i> Fecha <span class="required">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha" 
                        class="form-control-modern form-control"
                        min="<?= date('Y-m-d', strtotime('+1 days')) ?>">
                    <div class="info-label">
                        <i class="bi bi-info-circle"></i> Mínimo 1 día de anticipación
                    </div>
                </div>

                <!-- LAB -->
                <div class="col-md-9">
                    <label class="form-label-modern">
                        <i class="bi bi-laptop"></i> Laboratorio <span class="required">*</span>
                    </label>
                    <select id="lab" class="form-select-modern form-select">
                        <option value="">Seleccionar laboratorio</option>
                        <?php
                        $labs = $conn->query("
                            SELECT l.IDLab, l.Nombre, l.numLab, d.nombre AS depto 
                            FROM laboratorios l
                            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
                            WHERE l.activo = 1 
                            ORDER BY l.Nombre
                        ");
                        while($lab = $labs->fetch_assoc()):
                        ?>
                        <option value="<?= $lab['IDLab'] ?>">
                            <?= $lab['Nombre'] ?> (Lab <?= $lab['numLab'] ?>) - <?= $lab['depto'] ?? 'Sin departamento' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <hr class="divider-modern">

            <div class="row g-4">

                <!-- DOCENTE (TODOS LOS MAESTROS) -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-person-badge"></i> Docente <span class="required">*</span>
                    </label>
                    <select id="docente" class="form-select-modern form-select">
                        <option value="">Seleccionar docente</option>
                        <?php
                        $docentes = $conn->query("
                            SELECT IDUsuarios, CONCAT(nombre, ' ', apellidos) AS Nombre 
                            FROM usuarios 
                            WHERE rol = 'maestro' AND activo = 1
                            ORDER BY nombre
                        ");
                        while($d = $docentes->fetch_assoc()):
                        ?>
                        <option value="<?= $d['IDUsuarios'] ?>">
                            <?= $d['Nombre'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="info-label">
                        <i class="bi bi-info-circle"></i> Selecciona el docente que usará el laboratorio
                    </div>
                </div>

                <!-- GRUPO -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-people"></i> Grupo <span class="required">*</span>
                    </label>
                    <select id="grupo" class="form-select-modern form-select">
                        <option value="">Primero selecciona un docente</option>
                    </select>
                </div>

                <!-- ALUMNOS -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-person"></i> Alumnos
                    </label>
                    <input 
                        type="number" 
                        id="alumnos" 
                        class="form-control-modern form-control" 
                        readonly
                        placeholder="Selecciona un grupo">
                    <div class="info-label">
                        <i class="bi bi-arrow-right"></i> Se autocompleta al seleccionar grupo
                    </div>
                </div>

            </div>

            <div class="row g-4 mt-2">

                <!-- SOFTWARE -->
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-code-square"></i> Software
                    </label>
                    <input 
                        type="text" 
                        id="software" 
                        class="form-control-modern form-control" 
                        placeholder="Ej: Visual Studio, Cisco Packet Tracer, etc.">
                </div>

                <!-- PRÁCTICA -->
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-file-text"></i> Práctica
                    </label>
                    <input 
                        type="text" 
                        id="practica" 
                        class="form-control-modern form-control" 
                        placeholder="Nombre de la práctica">
                </div>

            </div>

            <hr class="divider-modern">

            <!-- SELECCIÓN DE HORAS -->
            <div class="row">
                <div class="col-12">
                    <label class="form-label-modern">
                        <i class="bi bi-clock-history"></i> Selecciona Horas <span class="required">*</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="contenedorHoras">
                        <?php
                        $horas = [
                            "07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00",
                            "11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00",
                            "15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00",
                            "19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"
                        ];
                        foreach($horas as $h):
                        ?>
                        <button type="button" class="hora-btn-modern hora-btn">
                            <?= $h ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="info-label mt-2">
                        <i class="bi bi-info-circle"></i> 
                        Haz clic para seleccionar múltiples horas consecutivas
                    </div>
                </div>
            </div>

            <hr class="divider-modern">

            <!-- BOTONES -->
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <button class="btn-guardar-modern" id="btnGuardar">
                        <i class="bi bi-check-circle"></i> Apartar Laboratorio
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/reservaciones.js"></script>
<script src="../../js/logout.js"></script>