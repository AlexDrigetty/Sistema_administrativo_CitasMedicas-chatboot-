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
    <style>
        .sesiones {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end; 
        }
        
        .sesiones .btn {
            width: 200px;
            background-color: #dfdfdf;
            padding: 10px 20px;
            transition: all 0.2s ease-in-out;
            border: none; 
            flex: none;
        }

        .sesiones .btn:hover {
            background-color: #ced0d2;
        }

        .sesiones .btn.active {
            background-color: #2c3e50;
            color: white;
            transition: all 0.3s ease-in-out;
        }

        .view-content {
            display: none;
        }
        
        .view-content.active {
            display: block;
        }
        
        .badge-estado {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* Estilos para la paginación */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
        }
        
        .page-item .page-link {
            color: #2C3E50;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.9rem;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .page-item .page-link:hover {
            background-color: #e9ecef;
            color: #2C3E50;
        }
        
        .page-item.active .page-link {
            background-color: #2C3E50;
            border-color: #2C3E50;
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        
        .page-nav {
            background-color: #2C3E50;
            color: white !important;
        }
        
        .page-nav:hover {
            background-color: #1a252f !important;
            color: white !important;
        }
        
        .filters-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .priority-badge {
            font-size: 0.9rem;
            padding: 0.5em 0.75em;
            border-radius: 0.5rem;
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        
        .priority-high {
            background-color: #dc3545;
            color: white;
        }
        
        .priority-medium {
            background-color: #fd7e14;
            color: white;
        }
        
        .priority-low {
            background-color: #28a745;
            color: white;
        }
        
        .priority-na {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div class="container py-5">
                <!-- Selector de vistas y filtros en una línea -->
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
                            <label for="filtroDoctor" class="form-label">Filtrar por doctor:</label>
                            <select class="form-select" id="filtroDoctor">
                                <option value="0">Todos los doctores</option>
                                <!-- Las opciones se cargarán con JavaScript -->
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filtroEspecialidad" class="form-label">Filtrar por especialidad:</label>
                            <select class="form-select" id="filtroEspecialidad">
                                <option value="0">Todas las especialidades</option>
                                <!-- Las opciones se cargarán con JavaScript -->
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="button" id="btnLimpiarFiltros" class="btn btn-outline-secondary h-100">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenido de las vistas -->
                <div id="pendientes-view" class="view-content active">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCitasPendientes">
                            <thead class="table-light">
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
                            <thead class="table-light">
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
                    
                    <div class="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cambiar Estado -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar Estado de Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCambiarEstado" method="POST" action="../funciones/cambiar_estado_cita.php">
                    <div class="modal-body">
                        <input type="hidden" name="cita_id" id="citaIdEstado">
                        
                        <div class="mb-3">
                            <label class="form-label">Nuevo estado</label>
                            <select class="form-select" name="nuevo_estado" id="selectNuevoEstado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notas (opcional)</label>
                            <textarea class="form-control" name="notas" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Actualizar Estado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="alertContainer" class="custom-alert"></div>

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

                // Cambiar entre vistas
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

                // Cargar filtro de doctores y especialidades
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

                            // Cargar doctores
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

                // Cargar citas pendientes
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
                            const horaFormateada = fechaHora.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
                            
                            let estadoBadge;
                            switch(cita.estado) {
                                case 'pendiente':
                                    estadoBadge = '<span class="badge bg-warning badge-estado">Pendiente</span>';
                                    break;
                                case 'confirmada':
                                    estadoBadge = '<span class="badge bg-info badge-estado">Confirmada</span>';
                                    break;
                                case 'completada':
                                    estadoBadge = '<span class="badge bg-success badge-estado">Completada</span>';
                                    break;
                                case 'cancelada':
                                    estadoBadge = '<span class="badge bg-danger badge-estado">Cancelada</span>';
                                    break;
                                default:
                                    estadoBadge = '<span class="badge bg-secondary badge-estado">Desconocido</span>';
                            }
                            
                            // Determinar la clase de prioridad según el puntaje o nivel_prioridad
                            let prioridadClass = 'priority-na';
                            let prioridadText = 'N/A';
                            let puntaje = cita.puntaje_total || 'N/A';
                            
                            if (cita.puntaje_total !== undefined && cita.puntaje_total !== null) {
                                if (cita.puntaje_total >= 20 || cita.nivel_prioridad === 'maxima') {
                                    prioridadClass = 'priority-high';
                                    prioridadText = 'Máxima';
                                } else if (cita.puntaje_total >= 10 || cita.nivel_prioridad === 'rapido') {
                                    prioridadClass = 'priority-medium';
                                    prioridadText = 'Alta';
                                } else {
                                    prioridadClass = 'priority-low';
                                    prioridadText = 'Moderada';
                                }
                            } else if (cita.nivel_prioridad) {
                                switch(cita.nivel_prioridad) {
                                    case 'maxima':
                                        prioridadClass = 'priority-high';
                                        prioridadText = 'Máxima';
                                        break;
                                    case 'rapido':
                                        prioridadClass = 'priority-medium';
                                        prioridadText = 'Alta';
                                        break;
                                    case 'moderado':
                                        prioridadClass = 'priority-low';
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
                                        <span class="priority-badge ${prioridadClass}">
                                            ${prioridadText} (${puntaje})
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
                    
                    // Abrir modal para cambiar estado
                    $(document).on('click', '.cambiar-estado', function(e) {
                        const citaId = $(e.currentTarget).data('id');
                        const estadoActual = $(e.currentTarget).data('estado');
                        
                        $('#citaIdEstado').val(citaId);
                        $('#selectNuevoEstado').val(estadoActual);
                        $('#cambiarEstadoModal').modal('show');
                    });

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