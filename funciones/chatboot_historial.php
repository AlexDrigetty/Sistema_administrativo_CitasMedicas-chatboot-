<?php
require_once __DIR__ . '/../bd/conexion.php';

/**
 * Obtiene el historial de citas de un paciente para mostrar en tabla
 */
function obtenerCitasParaTabla($usuarioId) {
    $pdo = conectarDB();
    
    $query = "SELECT 
                cm.cita_id,
                cm.ticket_code as ticket,
                CONCAT(d.nombre, ' ', d.apellidos) as doctor,
                d.especialidad,
                cm.fecha_hora as fecha,
                cm.estado
              FROM citas_medicas cm
              JOIN doctores d ON cm.doctor_id = d.usuario_id
              WHERE cm.usuario_id = :usuarioId
              ORDER BY cm.fecha_hora DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Obtiene detalles completos de una cita para el modal
 */
function obtenerDetallesCitaModal($citaId, $usuarioId) {
    $pdo = conectarDB();
    
    $query = "SELECT 
                cm.ticket_code as ticket,
                CONCAT(d.nombre, ' ', d.apellidos) as doctor,
                d.especialidad,
                d.nro_colegiatura,
                d.telefono,
                cm.fecha_hora as fecha,
                cm.estado,
                ic.descripcion_inicial as sintomas,
                GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') as diagnosticos,
                GROUP_CONCAT(DISTINCT m.nombre SEPARATOR ', ') as medicamentos
              FROM citas_medicas cm
              JOIN doctores d ON cm.doctor_id = d.usuario_id
              JOIN interacciones_chatbot ic ON cm.interaccion_id = ic.interaccion_id
              LEFT JOIN diagnosticos diag ON ic.interaccion_id = diag.interaccion_id
              LEFT JOIN diagnostico_enfermedades de ON diag.diagnostico_id = de.diagnostico_id
              LEFT JOIN enfermedades e ON de.enfermedad_id = e.enfermedad_id
              LEFT JOIN diagnostico_medicamentos dm ON diag.diagnostico_id = dm.diagnostico_id
              LEFT JOIN medicamentos m ON dm.medicamento_id = m.medicamento_id
              WHERE cm.cita_id = :citaId AND cm.usuario_id = :usuarioId
              GROUP BY cm.cita_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':citaId', $citaId, PDO::PARAM_INT);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Cancela una cita médica
 */
function cancelarCita($citaId, $usuarioId) {
    $pdo = conectarDB();
    
    $query = "UPDATE citas_medicas 
              SET estado = 'cancelada' 
              WHERE cita_id = :citaId AND usuario_id = :usuarioId AND estado = 'pendiente'";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':citaId', $citaId, PDO::PARAM_INT);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    
    return ($stmt->rowCount() > 0);
}