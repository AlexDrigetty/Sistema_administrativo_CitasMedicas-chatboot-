<?php
session_start();
require_once '../funciones/chatboot_historial.php';

if (!isset($_SESSION['usuario']) || !isset($_GET['cita_id'])) {
    die("Acceso no autorizado");
}

$detalles = obtenerDetallesCitaModal($_GET['cita_id'], $_SESSION['usuario']['id']);

if (!$detalles) {
    echo '<div class="alert alert-danger">No se encontraron detalles para esta cita</div>';
    exit;
}

// Función para mostrar valor o mensaje si está vacío
function mostrarValor($valor, $mensajeVacio = 'No especificado') {
    return !empty($valor) ? htmlspecialchars($valor) : '<span class="text-muted">'.$mensajeVacio.'</span>';
}
?>

<div class="row">
    <div class="col-md-12">
        <h5>Información de la Cita</h5>
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item d-flex justify-content-between">
                <span class="fw-bold">Ticket:</span>
                <span><?php echo mostrarValor($detalles['ticket']); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="fw-bold">Estado:</span>
                <span class="badge <?php echo [
                    'pendiente' => 'estado-pendiente',
                    'confirmada' => 'estado-confirmada',
                    'completada' => 'estado-completada',
                    'cancelada' => 'estado-cancelada'
                ][$detalles['estado']] ?? 'bg-secondary'; ?>">
                    <?php echo ucfirst($detalles['estado']); ?>
                </span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="fw-bold">Fecha:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($detalles['fecha'])); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="fw-bold">Doctor:</span>
                <span><?php echo mostrarValor($detalles['doctor']); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span class="fw-bold">Especialidad:</span>
                <span><?php echo mostrarValor($detalles['especialidad']); ?></span>
            </li>
        </ul>
    </div>
    
    
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5>Detalles Médicos</h5>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-titulo">Síntomas reportados</h6>
                <p class="card-texto"><?php echo mostrarValor($detalles['sintomas'], 'No se registraron síntomas'); ?></p>
            </div>
        </div>
        
        <?php if (!empty($detalles['diagnosticos'])): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-titulo">Diagnósticos</h6>
                    <p class="card-texto"><?php echo htmlspecialchars($detalles['diagnosticos']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($detalles['medicamentos'])): ?>
            <div class="card">
                <div class="card-body">
                    <h6 class="card-titulo">Medicamentos recomendados</h6>
                    <p class="card-texto"><?php echo htmlspecialchars($detalles['medicamentos']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>