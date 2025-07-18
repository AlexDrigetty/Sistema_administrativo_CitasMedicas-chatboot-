<?php require_once 'Auth.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Gestión de Citas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div class="container-fluid mt-4 px-5">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div class="sesiones view-switcher btn-group" role="group">
                        <button type="button" class="btn active" data-view="pendientes-view">
                            <i class="fas fa-clock"></i> Citas Pendientes
                        </button>
                        <button type="button" class="btn" data-view="completadas-view">
                            <i class="fas fa-check-circle"></i> Citas Completadas
                        </button>
                    </div>

                    <div class="filters-row">
                        <div class="filter-group">
                            <select class="form-select" id="filtroDoctor">
                                <option value="0">Todos los doctores</option>
                                <!-- Las opciones se cargarán con JavaScript -->
                            </select>
                        </div>

                        <div class="filter-group">
                            <select class="form-select" id="filtroEspecialidad" >
                                <option value="0">Todas las especialidades</option>
                                <!-- Las opciones se cargarán con JavaScript -->
                            </select>
                        </div>

                        <div class="filter-group">
                            <button type="button" id="btnLimpiarFiltros" class="btn  h-100">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenido de las vistas -->
                <div id="pendientes-view" class="view-content active">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCitasPendientes">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Paciente</th>
                                    <th>Doctor</th>
                                    <th>Especialidad</th>
                                    <th>Motivo</th>
                                    <th>Fecha/Hora</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Citas pendientes se cargarán aquí con AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container"></div>
                </div>

                <div id="completadas-view" class="view-content">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCitasCompletadas">
                            <thead >
                                <tr>
                                    <th>Ticket</th>
                                    <th>Paciente</th>
                                    <th>Doctor</th>
                                    <th>Especialidad</th>
                                    <th>Motivo</th>
                                    <th>Fecha/Hora</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Citas completadas se cargarán aquí con AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const citasManager = {
                // Mostrar alerta
                mostrarAlerta: function(mensaje, tipo) {
                    const alerta = `
                        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                            ${mensaje}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#alertContainer').html(alerta);
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 5000);
                },

                configurarSwitcher: function() {
                    const self = this;
                    $('.view-switcher button').click(function() {
                        $('.view-switcher button').removeClass('active');
                        $(this).addClass('active');

                        const view = $(this).data('view');
                        $('.view-content').removeClass('active');
                        $('#' + view).addClass('active');

                        // Recargar datos al cambiar de vista
                        const doctorId = $('#filtroDoctor').val() || 0;
                        const especialidad = $('#filtroEspecialidad').val() || 0;

                        if (view === 'pendientes-view') {
                            self.cargarCitasPendientes(1, doctorId, especialidad);
                        } else {
                            self.cargarCitasCompletadas(1, doctorId, especialidad);
                        }
                    });
                },

                cargarFiltros: function() {
                    const self = this;
                    $.ajax({
                        url: '../funciones/obtener_doctores.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.error) {
                                self.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            let doctorOptions = '<option value="0">Todos los doctores</option>';
                            let especialidades = new Set();

                            data.forEach(doctor => {
                                doctorOptions += `<option value="${doctor.usuario_id}">Dr. ${doctor.nombre} ${doctor.apellido}</option>`;
                                especialidades.add(doctor.especialidad);
                            });

                            $('#filtroDoctor').html(doctorOptions);

                            // Cargar especialidades
                            let especialidadOptions = '<option value="0">Todas las especialidades</option>';
                            especialidades.forEach(especialidad => {
                                especialidadOptions += `<option value="${especialidad}">${especialidad}</option>`;
                            });

                            $('#filtroEspecialidad').html(especialidadOptions);
                        },
                        error: function(xhr, status, error) {
                            self.mostrarAlerta('Error al cargar los filtros: ' + error, 'danger');
                        }
                    });
                },

                cargarCitasPendientes: function(pagina = 1, doctorId = 0, especialidad = 0) {
                    const self = this;
                    let url = `../funciones/obtener_citas.php?estado=pendiente&pagina=${pagina}`;

                    if (doctorId > 0) {
                        url += `&doctor_id=${doctorId}`;
                    }

                    if (especialidad !== '0') {
                        url += `&especialidad=${especialidad}`;
                    }

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.error) {
                                self.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            self.generarTablaCitas(data.data, '#tablaCitasPendientes tbody');
                            self.generarPaginacion(data.pagination, 'pendientes-view');
                        },
                        error: function(xhr, status, error) {
                            self.mostrarAlerta('Error al cargar citas pendientes: ' + error, 'danger');
                        }
                    });
                },

                // Cargar citas completadas
                cargarCitasCompletadas: function(pagina = 1, doctorId = 0, especialidad = 0) {
                    const self = this;
                    let url = `../funciones/obtener_citas.php?estado=completada&pagina=${pagina}`;

                    if (doctorId > 0) {
                        url += `&doctor_id=${doctorId}`;
                    }

                    if (especialidad !== '0') {
                        url += `&especialidad=${especialidad}`;
                    }

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.error) {
                                self.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            self.generarTablaCitas(data.data, '#tablaCitasCompletadas tbody');
                            self.generarPaginacion(data.pagination, 'completadas-view');
                        },
                        error: function(xhr, status, error) {
                            self.mostrarAlerta('Error al cargar citas completadas: ' + error, 'danger');
                        }
                    });
                },

                // Generar tabla de citas
                generarTablaCitas: function(citas, selector) {
                    let tableHtml = '';

                    if (citas.length === 0) {
                        tableHtml = `
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p>No se encontraron citas</p>
                                </td>
                            </tr>
                        `;
                    } else {
                        citas.forEach(cita => {
                            const fechaHora = new Date(cita.fecha_hora);
                            const fechaFormateada = fechaHora.toLocaleDateString('es-ES');
                            const horaFormateada = fechaHora.toLocaleTimeString('es-ES', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            let estadoBadge;
                            switch (cita.estado) {
                                case 'pendiente':
                                    estadoBadge = '<span class="estado-pendiente">Pendiente</span>';
                                    break;
                                case 'atendiendo':
                                    estadoBadge = '<span class="estado-atendiendo">Atendiendo</span>';
                                    break;
                                case 'completada':
                                    estadoBadge = '<span class="estado-completado">Completada</span>';
                                    break;
                                case 'cancelada':
                                    estadoBadge = '<span class="estado-cancelada">Cancelada</span>';
                                    break;
                                default:
                                    estadoBadge = '<span class="estado-desconocido">Desconocido</span>';
                            }

                            let prioridadClass = 'priority-na';
                            let prioridadText = 'N/A';
                            let puntaje = cita.puntaje_total || 'N/A';

                            if (cita.puntaje_total !== undefined && cita.puntaje_total !== null) {
                                if (cita.puntaje_total >= 20 || cita.nivel_prioridad === 'maxima') {
                                    prioridadClass = 'prioridad-alta';
                                    prioridadText = 'Máxima';
                                } else if (cita.puntaje_total >= 10 || cita.nivel_prioridad === 'rapido') {
                                    prioridadClass = 'prioridad-media';
                                    prioridadText = 'Alta';
                                } else {
                                    prioridadClass = 'prioridad-baja';
                                    prioridadText = 'Baja';
                                }
                            } else if (cita.nivel_prioridad) {
                                switch (cita.nivel_prioridad) {
                                    case 'maxima':
                                        prioridadClass = 'prioridad-alta';
                                        prioridadText = 'Máxima';
                                        break;
                                    case 'rapido':
                                        prioridadClass = 'prioridad-media';
                                        prioridadText = 'Alta';
                                        break;
                                    case 'moderado':
                                        prioridadClass = 'prioridad-baja';
                                        prioridadText = 'Moderada';
                                        break;
                                }
                            }

                            tableHtml += `
                                <tr>
                                    <td>${cita.ticket_code}</td>
                                    <td>${cita.paciente_nombre} ${cita.paciente_apellido}</td>
                                    <td>Dr. ${cita.doctor_nombre} ${cita.doctor_apellido}</td>
                                    <td>${cita.especialidad}</td>
                                    <td>${cita.motivo || 'No especificado'}</td>
                                    <td>${fechaFormateada} ${horaFormateada}</td>
                                    <td>${estadoBadge}</td>
                                    <td>
                                        <span class="prioridad ${prioridadClass}">
                                            ${prioridadText}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $(selector).html(tableHtml);
                },

                // Generar paginación
                generarPaginacion: function(pagination, viewId) {
                    // Eliminar paginación existente
                    $(`#${viewId} .pagination-container`).remove();

                    if (pagination.total_paginas <= 1) return;

                    let paginacionHtml = `
                        <div class="pagination-container">
                            <nav aria-label="Paginación">
                                <ul class="pagination">
                    `;

                    // Botón anterior
                    if (pagination.pagina_actual > 1) {
                        paginacionHtml += `
                            <li class="page-item">
                                <a class="page-link page-nav" href="#" data-pagina="${pagination.pagina_actual - 1}" data-view="${viewId}">
                                    &laquo; Anterior
                                </a>
                            </li>
                        `;
                    }

                    // Números de página
                    const paginasMostradas = 5; // Número máximo de páginas a mostrar
                    let inicio = Math.max(1, pagination.pagina_actual - Math.floor(paginasMostradas / 2));
                    let fin = Math.min(pagination.total_paginas, inicio + paginasMostradas - 1);

                    if (fin - inicio + 1 < paginasMostradas) {
                        inicio = Math.max(1, fin - paginasMostradas + 1);
                    }

                    if (inicio > 1) {
                        paginacionHtml += `
                            <li class="page-item">
                                <a class="page-link" href="#" data-pagina="1" data-view="${viewId}">1</a>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }

                    for (let i = inicio; i <= fin; i++) {
                        paginacionHtml += `
                            <li class="page-item ${i === pagination.pagina_actual ? 'active' : ''}">
                                <a class="page-link" href="#" data-pagina="${i}" data-view="${viewId}">
                                    ${i}
                                </a>
                            </li>
                        `;
                    }

                    if (fin < pagination.total_paginas) {
                        paginacionHtml += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#" data-pagina="${pagination.total_paginas}" data-view="${viewId}">
                                    ${pagination.total_paginas}
                                </a>
                            </li>
                        `;
                    }

                    // Botón siguiente
                    if (pagination.pagina_actual < pagination.total_paginas) {
                        paginacionHtml += `
                            <li class="page-item">
                                <a class="page-link page-nav" href="#" data-pagina="${pagination.pagina_actual + 1}" data-view="${viewId}">
                                    Siguiente &raquo;
                                </a>
                            </li>
                        `;
                    }

                    paginacionHtml += `
                                </ul>
                            </nav>
                        </div>
                    `;

                    $(`#${viewId}`).append(paginacionHtml);
                },

                // Configurar eventos
                configurarEventos: function() {
                    const self = this;

                    // Evento para cambiar de página
                    $(document).on('click', '.page-link[data-pagina]', function(e) {
                        e.preventDefault();
                        const pagina = $(this).data('pagina');
                        const viewId = $(this).data('view');
                        const doctorId = $('#filtroDoctor').val() || 0;
                        const especialidad = $('#filtroEspecialidad').val() || 0;

                        if (viewId === 'pendientes-view') {
                            self.cargarCitasPendientes(pagina, doctorId, especialidad);
                        } else {
                            self.cargarCitasCompletadas(pagina, doctorId, especialidad);
                        }

                        // Scroll suave hacia arriba
                        $('html, body').animate({
                            scrollTop: 0
                        }, 200);
                    });

                    // Evento para filtrar por doctor o especialidad
                    $(document).on('change', '#filtroDoctor, #filtroEspecialidad', function() {
                        const doctorId = $('#filtroDoctor').val() || 0;
                        const especialidad = $('#filtroEspecialidad').val() || 0;
                        const view = $('.view-content.active').attr('id');

                        if (view === 'pendientes-view') {
                            self.cargarCitasPendientes(1, doctorId, especialidad);
                        } else {
                            self.cargarCitasCompletadas(1, doctorId, especialidad);
                        }
                    });

                    // Evento para limpiar filtros
                    $('#btnLimpiarFiltros').click(function() {
                        $('#filtroDoctor').val('0');
                        $('#filtroEspecialidad').val('0');
                        const view = $('.view-content.active').attr('id');

                        if (view === 'pendientes-view') {
                            self.cargarCitasPendientes(1, 0, 0);
                        } else {
                            self.cargarCitasCompletadas(1, 0, 0);
                        }
                    });
                },

                // Inicializar el manager
                init: function() {
                    this.configurarSwitcher();
                    this.cargarFiltros();
                    this.cargarCitasPendientes();
                    this.configurarEventos();
                }
            };

            citasManager.init();
        });
    </script>
</body>

</html>