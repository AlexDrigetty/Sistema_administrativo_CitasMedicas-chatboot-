<?php
session_start();
require_once '../funciones/chatboot_historial.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_cita'])) {
    $citaId = $_POST['cita_id'] ?? 0;
    $exito = cancelarCita($citaId, $_SESSION['usuario']['id']);

    if ($exito) {
        $_SESSION['mensaje_exito'] = "La cita ha sido cancelada correctamente.";
    } else {
        $_SESSION['mensaje_error'] = "No se pudo cancelar la cita. Puede que ya haya sido confirmada o cancelada previamente.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$citas = obtenerCitasParaTabla($_SESSION['usuario']['id']);

// Procesar filtros
$filtro_especialidad = $_GET['especialidad'] ?? '';
$filtro_doctor = $_GET['doctor'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Aplicar filtros si existen
if ($filtro_especialidad || $filtro_doctor || $filtro_estado) {
    $citas = array_filter($citas, function ($cita) use ($filtro_especialidad, $filtro_doctor, $filtro_estado) {
        $cumple_especialidad = empty($filtro_especialidad) ||
            stripos($cita['especialidad'], $filtro_especialidad) !== false;
        $cumple_doctor = empty($filtro_doctor) ||
            stripos($cita['doctor'], $filtro_doctor) !== false;
        $cumple_estado = empty($filtro_estado) ||
            stripos($cita['estado'], $filtro_estado) !== false;
        return $cumple_especialidad && $cumple_doctor && $cumple_estado;
    });
}

// Obtener listas únicas para los filtros
$especialidades = array_unique(array_column($citas, 'especialidad'));
$doctores = array_unique(array_column($citas, 'doctor'));
$estados = array_unique(array_column($citas, 'estado'));

// Configurar paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 7;
$total_citas = count($citas);
$total_paginas = ceil($total_citas / $por_pagina);
$pagina_actual = max(1, min($pagina_actual, $total_paginas));
$indice_inicio = ($pagina_actual - 1) * $por_pagina;
$citas_paginadas = array_slice($citas, $indice_inicio, $por_pagina);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Chatbot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/chatboot.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <main class="container-fluid">
        <?php include "../chatBoot/slider.php" ?>

        <!-- Contenedor para alertas flotantes -->
        <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1100; min-width: 300px;"></div>

        <div class="chatboot w-100">
            <div class="chat-header">
                <h4 class="mb-0">PriorizaNow a tu Disposición</h4>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span class="user-greeting">Hola, <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Usuario'); ?></span>
                <?php endif; ?>
            </div>

            <div class="filtrados mb-3">
                <form method="get" class="row mb-0" >
                    <div class="filtro col-md-4">
                        <select class="form-select" id="especialidad" name="especialidad">
                            <option value="">Todas las especialidades</option>
                            <?php foreach ($especialidades as $especialidad): ?>
                                <option value="<?php echo htmlspecialchars($especialidad); ?>" <?php echo $filtro_especialidad == $especialidad ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($especialidad); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro col-md-3">
                        <select class="form-select" id="doctor" name="doctor">
                            <option value="">Todos los doctores</option>
                            <?php foreach ($doctores as $doctor): ?>
                                <option value="<?php echo htmlspecialchars($doctor); ?>" <?php echo $filtro_doctor == $doctor ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($doctor); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filtro col-md-3">
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados </option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo htmlspecialchars($estado); ?>" <?php echo $filtro_estado == $estado ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($estado)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-filtro  w-100 me-1">
                            <i class="fas fa-filter "></i>
                        </button>
                        <a href="?" class="btn-limpiar  w-100">
                            <i class="fa-solid fa-eraser"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <?php if (empty($citas)): ?>
                    <div class="empty-state text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <h4>No tienes citas médicas registradas</h4>
                        <p>Cuando reserves citas a través del chat, aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Doctor</th>
                                    <th>Especialidad</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citas_paginadas as $cita): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cita['ticket']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['doctor']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['especialidad']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha'])); ?></td>
                                        <td>
                                            <?php $badgeClass = [
                                                'pendiente' => 'estado-pendiente',
                                                'confirmada' => 'estado-confirmada',
                                                'completada' => 'estado-completada',
                                                'cancelada' => 'estado-cancelada'
                                            ][$cita['estado']] ?? 'bg-secondary'; ?>
                                            <span class="badge badge-estado <?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($cita['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-action ver-detalle"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detalleModal"
                                                data-cita-id="<?php echo $cita['cita_id']; ?>">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </button>

                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <button class="btn btn-sm  btn-action cancelar-cita"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelarModal"
                                                    data-cita-id="<?php echo $cita['cita_id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination-container">
                            <nav aria-label="Paginación">
                                <ul class="pagination">
                                    <?php if ($pagina_actual > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link page-nav" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])); ?>" aria-label="Anterior">
                                                &laquo; Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $paginasMostradas = 5; 
                                    $inicio = max(1, $pagina_actual - floor($paginasMostradas / 2));
                                    $fin = min($total_paginas, $inicio + $paginasMostradas - 1);

                                    if ($fin - $inicio + 1 < $paginasMostradas) {
                                        $inicio = max(1, $fin - $paginasMostradas + 1);
                                    }

                                    if ($inicio > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>">1</a>
                                        </li>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($fin < $total_paginas): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>">
                                                <?php echo $total_paginas; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($pagina_actual < $total_paginas): ?>
                                        <li class="page-item">
                                            <a class="page-link page-nav" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])); ?>" aria-label="Siguiente">
                                                Siguiente &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
    </main>

    <div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2c3e50;">
                    <h5 class="modal-title text-white">Detalles de la Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detalleModalBody">
                    Cargando información...
                </div>
                <div class="modal-footer">
                    <button type="button" class="boton-cancelar" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cancelación -->
    <div class="modal fade" id="cancelarModal" tabindex="-1" aria-hidden="true" style="margin-top: 150px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2c3e50;">
                    <h5 class="modal-title text-white">Confirme la Cancelación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="formCancelarCita">
                    <div class="modal-body">
                        <input type="hidden" name="cita_id" id="citaIdCancelar">
                        <p>¿Estás seguro que deseas cancelar esta cita médica?</p>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="boton-cancelar" data-bs-dismiss="modal">cancelar</button>
                        <button type="submit" name="cancelar_cita" class="boton-actualizar">confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Mostrar alertas flotantes
            function mostrarAlerta(mensaje, tipo) {
                const alerta = `
                    <div class="alert alert-${tipo} alert-dismissible fade show alert-floating" role="alert">
                        ${mensaje}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('#alertContainer').html(alerta);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            // Mostrar alertas de sesión si existen
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                mostrarAlerta('<?php echo $_SESSION['mensaje_exito']; ?>', 'success');
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
                mostrarAlerta('<?php echo $_SESSION['mensaje_error']; ?>', 'danger');
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            // Configurar modal de detalles
            $('.ver-detalle').click(function() {
                const citaId = $(this).data('cita-id');
                const modalBody = $('#detalleModalBody');

                // Mostrar carga
                modalBody.html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles de la cita...</p>
                    </div>
                `);

                // Obtener detalles via AJAX
                $.ajax({
                    url: `../funciones/chatboot_detalles.php?cita_id=${citaId}`,
                    type: 'GET',
                    success: function(html) {
                        modalBody.html(html);
                    },
                    error: function() {
                        modalBody.html(`
                            <div class="alert alert-danger">
                                Ocurrió un error al cargar los detalles. Por favor intenta nuevamente.
                            </div>
                        `);
                    }
                });
            });

            // Configurar modal de cancelación
            $('.cancelar-cita').click(function() {
                const citaId = $(this).data('cita-id');
                $('#citaIdCancelar').val(citaId);
            });

            // Resetear filtros
            $('.btn-limpiar').click(function() {
                $('#especialidad, #doctor, #estado').val('');
            });
        });
    </script>
</body>

</html>