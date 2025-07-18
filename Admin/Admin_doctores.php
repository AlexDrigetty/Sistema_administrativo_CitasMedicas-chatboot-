<?php require_once 'Auth.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Doctores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div class="container-fluid mt-4 px-5">
                <div class="sesiones view-switcher btn-group" role="group">
                    <button type="button" class="btn active" data-view="cards-view">
                        <i class="fas fa-th-large"></i> Vista de Tarjetas
                    </button>
                    <button type="button" class="btn" data-view="table-view">
                        <i class="fas fa-table"></i> Vista de Tabla
                    </button>
                </div>

                <div id="cards-view" class="view-content active">
                    <div class="row" id="doctores-cards">
                        <!-- Las tarjetas se cargarán aquí con AJAX -->
                    </div>
                </div>

                <div id="table-view" class="view-content">
                    <div class="table-responsive">
                        <table class="table table-hover" id="doctores-table">
                            <thead >
                                <tr style="text-align: center;">
                                    <th>Foto</th>
                                    <th>Nombre</th>
                                    <th>Apellidos</th>
                                    <th>Email</th>
                                    <th>Especialidad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Contenido de la tabla -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Doctor -->
    <div class="modal fade" id="editarDoctorModal" tabindex="-1" aria-labelledby="editarDoctorModalLabel" aria-hidden="true" style="margin-top: 130px;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #2C3E50;">
                    <h5 class="modal-title" id="editarDoctorModalLabel">Editar Doctor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarDoctor" method="POST" action="../funciones/actualizar_doctor.php">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="doctorIdEditar">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="doctorNombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" class="form-control" name="apellidos" id="doctorApellidos" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Especialidad</label>
                                <input type="text" class="form-control" name="especialidad" id="doctorEspecialidad" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" id="doctorTelefono" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="doctorEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña (dejar vacío para no cambiar)</label>
                                <input type="password" class="form-control" name="contrasena" id="doctorContrasena">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="activo" id="doctorActivo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="boton-cancelar" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="boton-actualizar">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Doctor -->
    <div class="modal fade" id="eliminarDoctorModal" tabindex="-1" aria-labelledby="eliminarDoctorModalLabel" aria-hidden="true" style="margin-top: 130px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #2c3e50;">
                    <h5 class="modal-title" id="eliminarDoctorModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEliminarDoctor" method="POST" action="../funciones/eliminar_doctor.php">
                    <div class="modal-body">
                        <div class="alert" style="background-color: #cdd4dbff;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>¡Advertencia!</strong> Esta acción eliminará permanentemente al doctor y todos sus datos asociados.
                        </div>
                        <p>¿Está seguro que desea eliminar permanentemente este doctor?</p>
                        <input type="hidden" name="id" id="doctorIdEliminar">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="boton-cancelar" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="boton-actualizar">Eliminar</button>
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
            const doctoresManager = {
                generarAvatar: function(nombre, apellido) {
                    const colores = ['#2c3e50', '#8e44ad', '#3498db', '#16a085', '#27ae60', '#f39c12', '#d35400', '#c0392b'];
                    const inicialNombre = nombre ? nombre.charAt(0).toUpperCase() : '';
                    const inicialApellido = apellido ? apellido.split(' ')[0].charAt(0).toUpperCase() : '';
                    const iniciales = inicialNombre + inicialApellido;
                    const hash = Array.from(iniciales).reduce((acc, char) => acc + char.charCodeAt(0), 0);
                    const color = colores[hash % colores.length];

                    return {
                        html: `<div class="initials-avatar" style="background-color: ${color}; width: 100%; height: 100%; font-size: ${iniciales.length > 1 ? '2.5rem' : '3rem'}">${iniciales}</div>`,
                        color: color,
                        iniciales: iniciales
                    };
                },

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

                // Cargar doctores desde el servidor
                cargarDoctores: function() {
                    $.ajax({
                        url: '../funciones/obtener_doctor.php',
                        type: 'GET',
                        dataType: 'json',
                        success: (data) => {
                            if (data.error) {
                                this.mostrarAlerta(data.error, 'danger');
                                return;
                            }

                            this.generarVistaTarjetas(data);
                            this.generarVistaTabla(data);
                        },
                        error: (xhr, status, error) => {
                            this.mostrarAlerta('Error al cargar los doctores: ' + error, 'danger');
                            console.error('Error en la solicitud AJAX:', status, error);
                        }
                    });
                },

                generarVistaTarjetas: function(doctores) {
                    let cardsHtml = '';

                    doctores.forEach(doctor => {
                        const avatar = this.generarAvatar(doctor.nombre, doctor.apellidos);
                        const estadoBadge = doctor.activo ?
                            '<span class="activo">Activo</span>' :
                            '<span class="inactivo">Inactivo</span>';

                        cardsHtml += `
                            <div class="col-md-4 mb-4">
                                <div class="formulario-doctor doctor-card h-100">
                                    <div class="doctor-img-container">
                                        ${doctor.foto ? 
                                            `<img src="${doctor.foto}" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" alt="${doctor.nombre} ${doctor.apellidos}">` : 
                                            `<div class="avatar-container">${avatar.html}</div>`}
                                    </div>
                                    <div class="card-body text-center" style="transform: translateY(-2px);">
                                        <h5 class="card-titulo" style="color: #2C3E50;">${doctor.nombre} ${doctor.apellidos}</h5>
                                        <p class="card-texto" style="color: #2C3E50;">${doctor.especialidad}</p>
                                        ${estadoBadge}
                                    </div>
                                    <div class="card-footer bg-transparent d-flex justify-content-center">
                                        <button class="editar-doctor me-2" data-id="${doctor.id}" 
                                            data-nombre="${doctor.nombre}" 
                                            data-apellidos="${doctor.apellidos}"
                                            data-especialidad="${doctor.especialidad}"
                                            data-telefono="${doctor.telefono}"
                                            data-email="${doctor.email}"
                                            data-activo="${doctor.activo}"
                                            data-foto="${doctor.foto || ''}"> Editar
                                        </button>
                                        <button class="eliminar-doctor" data-id="${doctor.id}">Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#doctores-cards').html(cardsHtml);
                },

                // Generar HTML para la vista de tabla
                generarVistaTabla: function(doctores) {
                    let tableHtml = '';

                    doctores.forEach(doctor => {
                        const avatar = this.generarAvatar(doctor.nombre, doctor.apellidos);
                        const estadoBadge = doctor.activo ?
                            '<span class="activo">Activo</span>' :
                            '<span class="inactivo">Inactivo</span>';

                        tableHtml += `
                            <tr>
                                <td class="align-middle">
                                    ${doctor.foto ? 
                                        `<img src="${doctor.foto}" width="50" height="50" class="rounded-circle" alt="${doctor.nombre} ${doctor.apellidos}">` : 
                                        `<div class="table-avatar" style="background-color: ${avatar.color}">${avatar.iniciales}</div>`}
                                </td>
                                <td class="align-middle">${doctor.nombre}</td>
                                <td class="align-middle">${doctor.apellidos}</td>
                                <td class="align-middle">${doctor.email}</td>
                                <td class="align-middle">${doctor.especialidad}</td>
                                <td class="align-middle">${estadoBadge}</td>
                                <td class="align-middle">
                                    <button class="editar-doctor" data-id="${doctor.id}"
                                        data-nombre="${doctor.nombre}" 
                                        data-apellidos="${doctor.apellidos}"
                                        data-especialidad="${doctor.especialidad}"
                                        data-telefono="${doctor.telefono}"
                                        data-email="${doctor.email}"
                                        data-activo="${doctor.activo}"
                                        data-foto="${doctor.foto || ''}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="eliminar-doctor" data-id="${doctor.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#doctores-table tbody').html(tableHtml);
                },

                // Configurar eventos
                configurarEventos: function() {
                    // Cambiar entre vistas
                    $('.view-switcher button').click(function() {
                        $('.view-switcher button').removeClass('active');
                        $(this).addClass('active');

                        const view = $(this).data('view');
                        $('.view-content').removeClass('active');
                        $('#' + view).addClass('active');
                    });

                    // Abrir modal de edición y cargar datos
                    $(document).on('click', '.editar-doctor', (e) => {
                        const btn = $(e.currentTarget);

                        $('#doctorIdEditar').val(btn.data('id'));
                        $('#doctorNombre').val(btn.data('nombre'));
                        $('#doctorApellidos').val(btn.data('apellidos'));
                        $('#doctorEspecialidad').val(btn.data('especialidad'));
                        $('#doctorTelefono').val(btn.data('telefono'));
                        $('#doctorEmail').val(btn.data('email'));
                        $('#doctorFoto').val(btn.data('foto'));
                        $('#doctorActivo').val(btn.data('activo') ? '1' : '0');

                        $('#editarDoctorModal').modal('show');
                    });

                    // Abrir modal de eliminación
                    $(document).on('click', '.eliminar-doctor', (e) => {
                        const doctorId = $(e.currentTarget).data('id');
                        $('#doctorIdEliminar').val(doctorId);
                        $('#eliminarDoctorModal').modal('show');
                    });

                    // Manejar el envío del formulario de edición
                    $('#formEditarDoctor').on('submit', (e) => {
                        e.preventDefault();
                        this.enviarFormulario($(e.target));
                    });

                    // Manejar el envío del formulario de eliminación
                    $('#formEliminarDoctor').on('submit', (e) => {
                        e.preventDefault();
                        this.enviarFormulario($(e.target));
                    });
                },

                // Enviar formulario y manejar respuesta
                enviarFormulario: function(form) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                form.closest('.modal').modal('hide');
                                this.mostrarAlerta(response.message || 'Operación realizada correctamente', 'success');
                                this.cargarDoctores();
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
                    this.cargarDoctores();
                    this.configurarEventos();
                }
            };
            doctoresManager.init();
        });
    </script>
</body>

</html>