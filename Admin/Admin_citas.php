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
        
        .card-cita {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card-cita:hover {
            transform: translateY(-5px);
        }
        
        .card-cita .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }
        
        .card-cita .estado-badge {
            float: right;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div class="container py-5">
                <!-- <div class="d-flex justify-content-between align-items-center mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaCitaModal">
                        <i class="fas fa-plus me-2"></i>Nueva Cita
                    </button>
                </div> -->

                <!-- Selector de vistas -->
                <div class="sesiones view-switcher btn-group" role="group">
                    <button type="button" class="btn active" data-view="pendientes-view">
                        <i class="fas fa-clock"></i> Citas Pendientes
                    </button>
                    <button type="button" class="btn" data-view="completadas-view">
                        <i class="fas fa-check-circle"></i> Citas Completadas
                    </button>
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
                                    <th>Síntomas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Citas pendientes se cargarán aquí con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="completadas-view" class="view-content">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tablaCitasCompletadas">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Paciente</th>
                                    <th>Doctor</th>
                                    <th>Síntomas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Citas completadas se cargarán aquí con AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Cita -->
    <div class="modal fade" id="nuevaCitaModal" tabindex="-1" aria-labelledby="nuevaCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="nuevaCitaModalLabel">Agendar Nueva Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevaCita" method="POST" action="../funciones/crear_cita.php">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">DNI del Paciente</label>
                                <input type="text" class="form-control" name="dni_paciente" id="dniPaciente" required pattern="\d{8}" title="8 dígitos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor</label>
                                <select class="form-select" name="doctor_id" id="selectDoctor" required>
                                    <option value="">Seleccionar doctor...</option>
                                    <!-- Opciones se cargarán con AJAX -->
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha" id="fechaCita" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora</label>
                                <input type="time" class="form-control" name="hora" id="horaCita" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Síntomas/Motivo</label>
                            <textarea class="form-control" name="sintomas" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agendar Cita</button>
                    </div>
                </form>
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
                    $('.view-switcher button').click(function() {
                        $('.view-switcher button').removeClass('active');
                        $(this).addClass('active');

                        const view = $(this).data('view');
                        $('.view-content').removeClass('active');
                        $('#' + view).addClass('active');
                    });
                },

                // Cargar doctores para el select
                cargarDoctores: function() {
                    $.ajax({
                        url: '../funciones/obtener_doctores_select.php',
                        type: 'GET',
                        dataType: 'json',
                        success: (data) => {
                            if (data.error) {
                                this.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            let options = '<option value="">Seleccionar doctor...</option>';
                            data.forEach(doctor => {
                                options += `<option value="${doctor.usuario_id}">Dr. ${doctor.nombre} ${doctor.apellidos} - ${doctor.especialidad}</option>`;
                            });
                            $('#selectDoctor').html(options);
                        },
                        error: (xhr, status, error) => {
                            this.mostrarAlerta('Error al cargar los doctores: ' + error, 'danger');
                        }
                    });
                },

                // Cargar citas pendientes
                cargarCitasPendientes: function() {
                    $.ajax({
                        url: '../funciones/obtener_citas.php?estado=pendiente',
                        type: 'GET',
                        dataType: 'json',
                        success: (data) => {
                            if (data.error) {
                                this.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            this.generarTablaCitas(data, '#tablaCitasPendientes tbody');
                        },
                        error: (xhr, status, error) => {
                            this.mostrarAlerta('Error al cargar citas pendientes: ' + error, 'danger');
                        }
                    });
                },

                // Cargar citas completadas
                cargarCitasCompletadas: function() {
                    $.ajax({
                        url: '../funciones/obtener_citas.php?estado=completada',
                        type: 'GET',
                        dataType: 'json',
                        success: (data) => {
                            if (data.error) {
                                this.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            this.generarTablaCitas(data, '#tablaCitasCompletadas tbody');
                        },
                        error: (xhr, status, error) => {
                            this.mostrarAlerta('Error al cargar citas completadas: ' + error, 'danger');
                        }
                    });
                },

                // Generar tabla de citas
                generarTablaCitas: function(citas, selector) {
                    let tableHtml = '';

                    citas.forEach(cita => {
                        const fechaHora = new Date(cita.fecha_hora);
                        const fechaFormateada = fechaHora.toLocaleDateString('es-ES');
                        const horaFormateada = fechaHora.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
                        
                        const estadoBadge = cita.estado === 'pendiente' ? 
                            '<span class="badge bg-warning badge-estado">Pendiente</span>' :
                            '<span class="badge bg-success badge-estado">Completada</span>';

                        tableHtml += `
                            <tr>
                                <td>#${cita.id.toString().padStart(4, '0')}</td>
                                <td>${cita.paciente_nombre} ${cita.paciente_apellido}</td>
                                <td>Dr. ${cita.doctor_nombre} ${cita.doctor_apellidos}</td>
                                <td>${cita.motivo || 'No especificado'}</td>
                                <td>${estadoBadge}</td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-warning cambiar-estado me-1" 
                                        data-id="${cita.id}" 
                                        data-estado="${cita.estado}">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger cancelar-cita" 
                                        data-id="${cita.id}"
                                        ${cita.estado === 'completada' ? 'disabled' : ''}>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $(selector).html(tableHtml);
                },

                // Configurar eventos
                configurarEventos: function() {
                    // Abrir modal para cambiar estado
                    $(document).on('click', '.cambiar-estado', (e) => {
                        const citaId = $(e.currentTarget).data('id');
                        const estadoActual = $(e.currentTarget).data('estado');
                        
                        $('#citaIdEstado').val(citaId);
                        $('#selectNuevoEstado').val(estadoActual);
                        $('#cambiarEstadoModal').modal('show');
                    });

                    // Cancelar cita directamente
                    $(document).on('click', '.cancelar-cita', (e) => {
                        if (confirm('¿Está seguro que desea cancelar esta cita?')) {
                            const citaId = $(e.currentTarget).data('id');
                            this.cambiarEstadoCita(citaId, 'cancelada', 'Cita cancelada correctamente');
                        }
                    });

                    // Manejar el envío del formulario de nueva cita
                    $('#formNuevaCita').on('submit', (e) => {
                        e.preventDefault();
                        this.enviarFormulario($(e.target), 'Cita creada correctamente');
                    });

                    // Manejar el envío del formulario de cambio de estado
                    $('#formCambiarEstado').on('submit', (e) => {
                        e.preventDefault();
                        this.enviarFormulario($(e.target), 'Estado de cita actualizado');
                    });
                },

                // Cambiar estado de una cita
                cambiarEstadoCita: function(citaId, nuevoEstado, mensaje = 'Estado de cita actualizado') {
                    $.ajax({
                        url: '../funciones/cambiar_estado_cita.php',
                        type: 'POST',
                        data: {
                            cita_id: citaId,
                            nuevo_estado: nuevoEstado
                        },
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                this.mostrarAlerta(mensaje, 'success');
                                this.cargarCitasPendientes();
                                this.cargarCitasCompletadas();
                            } else {
                                this.mostrarAlerta(response.message || 'Error al cambiar el estado', 'danger');
                            }
                        },
                        error: () => {
                            this.mostrarAlerta('Error en la comunicación con el servidor', 'danger');
                        }
                    });
                },

                // Enviar formulario y manejar respuesta
                enviarFormulario: function(form, mensajeExito) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                form.closest('.modal').modal('hide');
                                this.mostrarAlerta(mensajeExito, 'success');
                                this.cargarCitasPendientes();
                                this.cargarCitasCompletadas();
                                form[0].reset();
                            } else {
                                this.mostrarAlerta(response.message || 'Error en la operación', 'danger');
                            }
                        },
                        error: () => {
                            this.mostrarAlerta('Error en la comunicación con el servidor', 'danger');
                        }
                    });
                },

                // Inicializar el manager
                init: function() {
                    this.configurarSwitcher();
                    this.cargarDoctores();
                    this.cargarCitasPendientes();
                    this.cargarCitasCompletadas();
                    this.configurarEventos();
                }
            };
            
            citasManager.init();
        });
    </script>
</body>
</html>