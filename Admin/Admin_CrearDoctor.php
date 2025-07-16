<?php require_once 'Auth.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Crear Doctores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div id="alertContainer" class="custom-alert"></div>

            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-header text-white" style="background-color:#2C3E50">
                                <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Registrar Nuevo Doctor</h5>
                            </div>
                            <div class="card-body">
                                <form id="formCrearDoctor" method="POST" action="../funciones/crear_doctor.php">
                                    <div class="box-form mb-3">
                                        <label class="form-label">DNI</label>
                                        <input type="text" class="form-control" name="dni" required
                                            pattern="[0-9]{8}" title="Ingrese 8 dígitos numéricos">
                                    </div>



                                    <div class="row">
                                        <div class="box-form col-md-6 mb-3">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" class="form-control" name="nombre" required>
                                        </div>
                                        <div class="box-form col-md-6 mb-3">
                                            <label class="form-label">Apellidos</label>
                                            <input type="text" class="form-control" name="apellidos" required>
                                        </div>
                                    </div>

                                    <div class="box-form mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" name="telefono" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Especialidad</label>
                                            <select class="form-select" name="especialidad" required>
                                                <option value="">Seleccione especialidad</option>
                                                <?php
                                                require_once '../bd/conexion.php';
                                                $conn = conectarDB();

                                                $query = "SELECT DISTINCT especialidad FROM doctores ORDER BY especialidad";
                                                $stmt = $conn->prepare($query);
                                                $stmt->execute();

                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $row['especialidad'] . '">' . $row['especialidad'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="box-form mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" name="contrasena" required
                                            minlength="8" title="Mínimo 8 caracteres">
                                    </div>
                                    <div class="box-form mb-3">
                                        <label class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" name="confirmar_contrasena" required>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn" style="background-color: #2C3E50; color: white;">
                                            <i class="fas fa-save me-2"></i>Registrar Doctor
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card-body" style="padding: 0;">
                            <div class="table-responsive">
                                <table class="table  table-hover" id="tablaDoctores" style="text-align: center;">
                                    <thead>
                                        <tr>
                                            <th>Foto</th>
                                            <th>Nombre</th>
                                            <th>Apellidos</th>
                                            <th>Especialidad</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function mostrarAlerta(mensaje, tipo = 'success') {
                const alerta = `
                <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    <strong>${tipo === 'success' ? 'Éxito!' : 'Error!'}</strong> ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                $('#alertContainer').html(alerta);

                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            function cargarDoctores() {
                $.ajax({
                    url: '../funciones/obtener_doctores.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            mostrarAlerta(data.error, 'danger');
                            return;
                        }

                        let tableHtml = '';
                        data.forEach(doctor => {
                            const inicialNombre = doctor.nombre ? doctor.nombre.charAt(0).toUpperCase() : '';
                            const inicialApellido = doctor.apellidos ? doctor.apellidos.split(' ')[0].charAt(0).toUpperCase() : '';
                            const iniciales = inicialNombre + inicialApellido;
                            const colores = ['#2c3e50', '#8e44ad', '#3498db', '#16a085', '#27ae60', '#f39c12', '#d35400', '#c0392b'];
                            const hash = Array.from(iniciales).reduce((acc, char) => acc + char.charCodeAt(0), 0);
                            const color = colores[hash % colores.length];

                            const avatarHtml = doctor.foto ?
                                `<img src="${doctor.foto}" width="50" height="50" class="rounded-circle" alt="${doctor.nombre} ${doctor.apellidos}">` :
                                `<div class="avatar-iniciales" style="background-color: ${color}">${iniciales}</div>`;

                            const estadoBadge = doctor.activo ?
                                '<span class="badge bg-success">Activo</span>' :
                                '<span class="badge bg-secondary">Inactivo</span>';

                            tableHtml += `
                            <tr>
                                <td>${avatarHtml}</td>
                                <td>${doctor.nombre}</td>
                                <td>${doctor.apellidos}</td>
                                <td>${doctor.especialidad}</td>
                                <td>${estadoBadge}</td>
                            </tr>
                        `;
                        });

                        $('#tablaDoctores tbody').html(tableHtml);
                    },
                    error: function(xhr, status, error) {
                        mostrarAlerta('Error al cargar los doctores: ' + error, 'danger');
                    }
                });
            }

            cargarDoctores();

            $('#btnRefrescar').click(function() {
                cargarDoctores();
                mostrarAlerta('Lista de doctores actualizada', 'info');
            });

            $('#formCrearDoctor').on('submit', function(e) {
                const contrasena = $('input[name="contrasena"]').val();
                const confirmar = $('input[name="confirmar_contrasena"]').val();

                if (contrasena !== confirmar) {
                    e.preventDefault();
                    mostrarAlerta('Las contraseñas no coinciden', 'danger');
                    return false;
                }
                return true;
            });

            $('#formCrearDoctor').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlerta(response.message || 'Doctor creado correctamente');
                            $('#formCrearDoctor')[0].reset();
                            cargarDoctores();
                        } else {
                            mostrarAlerta(response.message || 'Error al crear doctor', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        mostrarAlerta('Error al comunicarse con el servidor: ' + error, 'danger');
                    }
                });
            });
        });
    </script>
</body>

</html>