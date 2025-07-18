<?php
require_once 'Auth.php';
require_once  '../funciones/panel_resumen.php';

$estadisticas = getEstadisticasComparativas();
$actividad = getActividadReciente();
$citasPendientes = getCitasPendientes();
$pacientesRecientes = getPacientesRecientes();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Panel Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'slider.php'; ?>

        <div class="main-content">
            <?php include 'Admin_header.php'; ?>

            <div class="container-fluid mt-4 px-5">
                <div class="box mb-4">
                    <!-- Tarjeta de Pacientes -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-titulo">Total Pacientes</h5>
                                    <h2 class="card-texto"><?= number_format($estadisticas['pacientes']['total']) ?></h2>
                                </div>
                                <div class="text-end">
                                    <small>Hoy: <?= $estadisticas['pacientes']['hoy'] ?></small>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <span class="me-2 small"><?= abs($estadisticas['pacientes']['variacion']) ?>%</span>
                                        <?php if ($estadisticas['pacientes']['variacion'] > 0): ?>
                                            <i class="bi bi-arrow-up-circle fs-5 text-success"></i>
                                        <?php elseif ($estadisticas['pacientes']['variacion'] < 0): ?>
                                            <i class="bi bi-arrow-down-circle fs-5 text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle fs-5 text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Doctores -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-titulo">Total Doctores</h5>
                                    <h2 class="card-texto"><?= number_format($estadisticas['doctores']['total']) ?></h2>
                                </div>
                                <div class="text-end">
                                    <small>Hoy: <?= $estadisticas['doctores']['hoy'] ?></small>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <span class="me-2 small"><?= abs($estadisticas['doctores']['variacion']) ?>%</span>
                                        <?php if ($estadisticas['doctores']['variacion'] > 0): ?>
                                            <i class="bi bi-arrow-up-circle fs-5 text-success"></i>
                                        <?php elseif ($estadisticas['doctores']['variacion'] < 0): ?>
                                            <i class="bi bi-arrow-down-circle fs-5 text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle fs-5 text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Citas Pendientes -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-titulo">Citas Pendientes</h5>
                                    <h2 class="card-texto"><?= number_format($estadisticas['citas_pendientes']['total']) ?></h2>
                                </div>
                                <div class="text-end">
                                    <small>Hoy: <?= $estadisticas['citas_pendientes']['hoy'] ?></small>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <span class="me-2 small"><?= abs($estadisticas['citas_pendientes']['variacion']) ?>%</span>
                                        <?php if ($estadisticas['citas_pendientes']['variacion'] > 0): ?>
                                            <i class="bi bi-arrow-up-circle fs-5 text-success"></i>
                                        <?php elseif ($estadisticas['citas_pendientes']['variacion'] < 0): ?>
                                            <i class="bi bi-arrow-down-circle fs-5 text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle fs-5 text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta de Citas Completadas -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-titulo">Citas Completadas</h5>
                                    <h2 class="card-texto"><?= number_format($estadisticas['citas_completadas']['total']) ?></h2>
                                </div>
                                <div class="text-end d-flex flex-column align-items-center justify-content-space-between">
                                    <small>Hoy: <?= $estadisticas['citas_completadas']['hoy'] ?></small>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <span class="me-2 small"><?= abs($estadisticas['citas_completadas']['variacion']) ?>%</span>
                                        <?php if ($estadisticas['citas_completadas']['variacion'] > 0): ?>
                                            <i class="bi bi-arrow-up-circle fs-5 text-success"></i>
                                        <?php elseif ($estadisticas['citas_completadas']['variacion'] < 0): ?>
                                            <i class="bi bi-arrow-down-circle fs-5 text-danger"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle fs-5 text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de Información -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="citas-tabla table-hover mb-0" style="color: black;">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Paciente</th>
                                            <th>Doctor</th>
                                            <th>Especialidad</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citasPendientes as $cita): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cita['ticket_code']) ?></td>
                                                <td><?= htmlspecialchars($cita['paciente']) ?></td>
                                                <td><?= htmlspecialchars($cita['doctor']) ?></td>
                                                <td><?= htmlspecialchars($cita['especialidad']) ?></td>
                                                <td>
                                                    <span class="estado-pendiente"><?= ucfirst($cita['estado']) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($citasPendientes)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">No hay citas pendientes</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Pacientes Recientes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="citas-tabla table-hover mb-0" style="color: black;">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Fecha Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pacientesRecientes as $index => $paciente): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= htmlspecialchars($paciente['nombre_completo']) ?></td>
                                                    <td><?= htmlspecialchars($paciente['email']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($paciente['fecha_registro'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($pacientesRecientes)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">No hay pacientes registrados</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Actividad Reciente -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Actividad Reciente (7 días)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="activityChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>

    <script>
        // Gráfico de actividad
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($actividad['labels']) ?>,
                datasets: [{
                        label: 'Interacciones del Chatbot',
                        data: <?= json_encode($actividad['interacciones']) ?>,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: 'Citas Reservadas',
                        data: <?= json_encode($actividad['citas']) ?>,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        tension: 0.1,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>