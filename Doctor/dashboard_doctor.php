<?php
require_once 'Auth.php';
doctorAuth();

// Obtener información del doctor
$doctorInfo = getDoctorInfo($_SESSION['usuario_id']);

if (!$doctorInfo) {
    header('Location: ../Admin/login_admin.php?error=doctor_info');
    exit;
}

// Configuración de paginación
$por_pagina = 6;
$pagina_pendientes = isset($_GET['pagina_p']) ? max(1, intval($_GET['pagina_p'])) : 1;
$pagina_completadas = isset($_GET['pagina_c']) ? max(1, intval($_GET['pagina_c'])) : 1;

// Obtener citas del doctor
require_once '../bd/conexion.php';
$conn = conectarDB();

try {
    // Citas pendientes y en atención - FORMA CORRECTA
    $sql_pendientes = "
        SELECT SQL_CALC_FOUND_ROWS
            c.cita_id,
            c.ticket_code,
            c.fecha_hora,
            c.estado,
            p.nombre AS paciente_nombre,
            p.apellido AS paciente_apellido,
            d.puntaje_total,
            i.descripcion_inicial AS motivo
        FROM citas_medicas c
        JOIN interacciones_chatbot i ON c.interaccion_id = i.interaccion_id
        JOIN diagnosticos d ON i.interaccion_id = d.interaccion_id
        JOIN perfiles_pacientes p ON c.usuario_id = p.usuario_id
        WHERE c.doctor_id = :doctor_id AND (c.estado = 'pendiente' OR c.estado = 'atendiendo')
        ORDER BY 
            CASE WHEN c.estado = 'atendiendo' THEN 0 ELSE 1 END,
            c.fecha_hora ASC
        LIMIT :offset, :limit
    ";
    
    $stmt_pendientes = $conn->prepare($sql_pendientes);
    $offset_p = ($pagina_pendientes - 1) * $por_pagina;
    $stmt_pendientes->bindValue(':doctor_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt_pendientes->bindValue(':offset', $offset_p, PDO::PARAM_INT);
    $stmt_pendientes->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    $stmt_pendientes->execute();
    
    $citas_pendientes = $stmt_pendientes->fetchAll(PDO::FETCH_ASSOC);
    
    // Total de citas pendientes para paginación
    $total_pendientes = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_paginas_p = ceil($total_pendientes / $por_pagina);

    // Citas completadas - FORMA CORRECTA
    $sql_completadas = "
        SELECT SQL_CALC_FOUND_ROWS
            c.cita_id,
            c.ticket_code,
            c.fecha_hora,
            c.estado,
            p.nombre AS paciente_nombre,
            p.apellido AS paciente_apellido,
            d.puntaje_total,
            i.descripcion_inicial AS motivo
        FROM citas_medicas c
        JOIN interacciones_chatbot i ON c.interaccion_id = i.interaccion_id
        JOIN diagnosticos d ON i.interaccion_id = d.interaccion_id
        JOIN perfiles_pacientes p ON c.usuario_id = p.usuario_id
        WHERE c.doctor_id = :doctor_id AND c.estado = 'completada'
        ORDER BY c.fecha_hora DESC
        LIMIT :offset, :limit
    ";
    
    $stmt_completadas = $conn->prepare($sql_completadas);
    $offset_c = ($pagina_completadas - 1) * $por_pagina;
    $stmt_completadas->bindValue(':doctor_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt_completadas->bindValue(':offset', $offset_c, PDO::PARAM_INT);
    $stmt_completadas->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    $stmt_completadas->execute();
    
    $citas_completadas = $stmt_completadas->fetchAll(PDO::FETCH_ASSOC);
    
    // Total de citas completadas para paginación
    $total_completadas = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    $total_paginas_c = ceil($total_completadas / $por_pagina);

} catch (PDOException $e) {
    error_log("Error al obtener citas del doctor: " . $e->getMessage());
    $citas_pendientes = [];
    $citas_completadas = [];
    $total_paginas_p = 1;
    $total_paginas_c = 1;
    
    // Mostrar error de depuración (solo en desarrollo)
    echo '<div class="alert alert-danger">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica - Dr. <?= htmlspecialchars($doctorInfo['nombre'] . ' ' . $doctorInfo['apellidos']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Clínica Médica</h3>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li>
                        <a href="dashboard_doctor.php" class="active">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <!-- <li>
                        <a href="#">
                            <i class="fas fa-calendar-alt"></i> Calendario
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-user-injured"></i> Pacientes
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-file-medical"></i> Reportes
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                    </li> -->
                </ul>
            </nav>
        </aside>

        <main class="main-content mb-4">
            <header class="header">
                <div class="logo">Panel del Doctor</div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?= substr($doctorInfo['nombre'], 0, 1) . substr($doctorInfo['apellidos'], 0, 1) ?>
                    </div>
                    <a href="../bd/cerrar_sesion_admin.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </header>

            <!-- Contenedor para alertas flotantes -->
            <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"></div>

            <div class="container-fluid mt-4 px-5">
                <div class="doctor-header">
                    <div class="d-flex align-items-center">
                        <div class="doctor-avatar">
                            <?= substr($doctorInfo['nombre'], 0, 1) . substr($doctorInfo['apellidos'], 0, 1) ?>
                        </div>
                        <div class="doctor-info">
                            <h1 class="doctor-nombre">Dr. <?= htmlspecialchars($doctorInfo['nombre'] . ' ' . $doctorInfo['apellidos']) ?></h1>
                            <div class="doctor-especialidad"><?= htmlspecialchars($doctorInfo['especialidad']) ?></div>
                            <div class="doctor-contacto">
                                <i class="fas fa-phone"></i> Teléfono: <?= htmlspecialchars($doctorInfo['telefono']) ?>
                            </div>
                            <div class="doctor-contacto">
                                <i class="fas fa-id-card"></i> N° Colegiatura: <?= htmlspecialchars($doctorInfo['nro_colegiatura']) ?>
                            </div>
                            <div class="doctor-contacto">
                                <i class="fas fa-calendar-alt"></i> Fecha de ingreso: <?= date('d/m/Y', strtotime($doctorInfo['fecha_ingreso'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas para cambiar entre citas pendientes y completadas -->
                <ul class="nav nav-tabs mt-4" id="citasTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                            <i class="fas fa-clock"></i> Citas Pendientes/Atendiendo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completadas-tab" data-bs-toggle="tab" data-bs-target="#completadas" type="button" role="tab">
                            <i class="fas fa-check-circle"></i> Citas Completadas
                        </button>
                    </li>
                </ul>

                <!-- Contenido de las pestañas -->
                <div class="tab-content" id="citasTabContent">
                    <!-- Pestaña de citas pendientes/atendiendo -->
                    <div class="tab-pane fade show active" id="pendientes" role="tabpanel">

                        <?php if (empty($citas_pendientes)): ?>
                            <div class="alert alert-info mt-3">
                                No tiene citas pendientes o en atención en este momento.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive mt-3">
                                <table class="citas-tabla">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Paciente</th>
                                            <th>Apellidos</th>
                                            <th>Fecha/Hora</th>
                                            <th>Motivo</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas_pendientes as $cita): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cita['ticket_code']) ?></td>
                                                <td><?= htmlspecialchars($cita['paciente_nombre']) ?></td>
                                                <td><?= htmlspecialchars($cita['paciente_apellido']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                                <td><?= htmlspecialchars(substr($cita['motivo'], 0, 50) . (strlen($cita['motivo']) > 50 ? '...' : '')) ?></td>
                                                <td>
                                                    <?php
                                                    $prioridad = '';
                                                    $clase = '';
                                                    if ($cita['puntaje_total'] >= 80) {
                                                        $prioridad = 'Alta';
                                                        $clase = 'prioridad-alta';
                                                    } elseif ($cita['puntaje_total'] >= 50) {
                                                        $prioridad = 'Media';
                                                        $clase = 'prioridad-media';
                                                    } else {
                                                        $prioridad = 'Baja';
                                                        $clase = 'prioridad-baja';
                                                    }
                                                    ?>
                                                    <span class="prioridad <?= $clase ?>"><?= $prioridad ?></span>
                                                </td>
                                                <td class="estado-<?= $cita['estado'] ?>"><?= ucfirst($cita['estado']) ?></td>
                                                <td>
                                                    <button class="atender" onclick="location.href='../funciones/atender_cita.php?id=<?= $cita['cita_id'] ?>'">
                                                        <i class="fas fa-user-md"></i> 
                                                        <?= $cita['estado'] == 'atendiendo' ? 'Completar' : 'Atender' ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación para citas pendientes -->
                            <?php if ($total_paginas_p > 1): ?>
                                <nav aria-label="Paginación de citas pendientes">
                                    <ul class="pagination">
                                        <?php if ($pagina_pendientes > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina_p=<?= $pagina_pendientes - 1 ?>&pagina_c=<?= $pagina_completadas ?>">
                                                    &laquo; Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_paginas_p; $i++): ?>
                                            <li class="page-item <?= $i == $pagina_pendientes ? 'active' : '' ?>">
                                                <a class="page-link" href="?pagina_p=<?= $i ?>&pagina_c=<?= $pagina_completadas ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($pagina_pendientes < $total_paginas_p): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina_p=<?= $pagina_pendientes + 1 ?>&pagina_c=<?= $pagina_completadas ?>">
                                                    Siguiente &raquo;
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="completadas" role="tabpanel">

                        <?php if (empty($citas_completadas)): ?>
                            <div class="alert alert-info mt-3">
                                No tiene citas completadas registradas.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive mt-3">
                                <table class="citas-tabla">
                                    <thead>
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Paciente</th>
                                            <th>Apellidos</th>
                                            <th>Fecha/Hora</th>
                                            <th>Motivo</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas_completadas as $cita): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cita['ticket_code']) ?></td>
                                                <td><?= htmlspecialchars($cita['paciente_nombre']) ?></td>
                                                <td><?= htmlspecialchars($cita['paciente_apellido']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                                <td><?= htmlspecialchars(substr($cita['motivo'], 0, 50) . (strlen($cita['motivo']) > 50 ? '...' : '')) ?></td>
                                                <td>
                                                    <?php
                                                    $prioridad = '';
                                                    $clase = '';
                                                    if ($cita['puntaje_total'] >= 80) {
                                                        $prioridad = 'Alta';
                                                        $clase = 'prioridad-alta';
                                                    } elseif ($cita['puntaje_total'] >= 50) {
                                                        $prioridad = 'Media';
                                                        $clase = 'prioridad-media';
                                                    } else {
                                                        $prioridad = 'Baja';
                                                        $clase = 'prioridad-baja';
                                                    }
                                                    ?>
                                                    <span class="prioridad <?= $clase ?>"><?= $prioridad ?></span>
                                                </td>
                                                <td class="estado-completada">Completada</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_paginas_c > 1): ?>
                                <nav aria-label="Paginación de citas completadas">
                                    <ul class="pagination">
                                        <?php if ($pagina_completadas > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina_p=<?= $pagina_pendientes ?>&pagina_c=<?= $pagina_completadas - 1 ?>">
                                                    &laquo; Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_paginas_c; $i++): ?>
                                            <li class="page-item <?= $i == $pagina_completadas ? 'active' : '' ?>">
                                                <a class="page-link" href="?pagina_p=<?= $pagina_pendientes ?>&pagina_c=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($pagina_completadas < $total_paginas_c): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina_p=<?= $pagina_pendientes ?>&pagina_c=<?= $pagina_completadas + 1 ?>">
                                                    Siguiente &raquo;
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar alertas flotantes
        function mostrarAlerta(mensaje, tipo) {
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show alert-floating`;
            alerta.role = 'alert';
            alerta.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.getElementById('alertContainer').appendChild(alerta);
            
            // Eliminar la alerta después de 5 segundos
            setTimeout(() => {
                alerta.remove();
            }, 5000);
        }

        // Mostrar alertas de PHP si existen
        <?php if (!empty($_GET['success']) && $_GET['success'] == 'estado_actualizado'): ?>
            mostrarAlerta('Estado de la cita actualizado correctamente', 'success');
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            const errors = {
                'no_id': 'No se proporcionó ID de cita.',
                'cita_no_encontrada': 'Cita no encontrada.',
                'estado_no_valido': 'Estado de cita no válido para actualización.',
                'db_error': 'Error al actualizar la cita en la base de datos.'
            };
            const errorMsg = errors['<?= $_GET['error'] ?>'] || 'Ocurrió un error desconocido.';
            mostrarAlerta(errorMsg, 'danger');
        <?php endif; ?>
    </script>
</body>

</html>