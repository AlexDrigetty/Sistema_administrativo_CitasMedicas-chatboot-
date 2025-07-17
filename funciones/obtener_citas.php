<?php
require_once '../bd/conexion.php';
header('Content-Type: application/json');

try {
    $conn = conectarDB();
    
    // Parámetros de paginación y filtros
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $por_pagina = 10;
    $offset = ($pagina - 1) * $por_pagina;
    
    $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
    $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    $especialidad = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';
    
    // Consulta base para obtener citas
    $query = "SELECT SQL_CALC_FOUND_ROWS 
                cm.cita_id, cm.ticket_code, cm.fecha_hora, cm.estado,
                u_paciente.nombre as paciente_nombre, u_paciente.apellido as paciente_apellido,
                u_doctor.nombre as doctor_nombre, u_doctor.apellido as doctor_apellido,
                d.especialidad, ic.descripcion_inicial as motivo,
                diag.puntaje_total, diag.nivel_prioridad
              FROM citas_medicas cm
              JOIN usuarios u_paciente ON cm.usuario_id = u_paciente.id
              JOIN doctores d ON cm.doctor_id = d.usuario_id
              JOIN usuarios u_doctor ON d.usuario_id = u_doctor.id
              JOIN interacciones_chatbot ic ON cm.interaccion_id = ic.interaccion_id
              LEFT JOIN diagnosticos diag ON ic.interaccion_id = diag.interaccion_id
              WHERE cm.estado IN ('pendiente', 'completada')";
    
    $params = [];
    
    // Filtro por estado
    if (!empty($estado)) {
        $query .= " AND cm.estado = :estado";
        $params[':estado'] = $estado;
    }
    
    // Filtro por doctor
    if ($doctor_id > 0) {
        $query .= " AND cm.doctor_id = :doctor_id";
        $params[':doctor_id'] = $doctor_id;
    }
    
    // Filtro por especialidad
    if (!empty($especialidad) && $especialidad !== '0') {
        $query .= " AND d.especialidad = :especialidad";
        $params[':especialidad'] = $especialidad;
    }
    
    // Ordenar por fecha más reciente primero
    $query .= " ORDER BY cm.fecha_hora DESC LIMIT :offset, :por_pagina";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':por_pagina', $por_pagina, PDO::PARAM_INT);
    
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener el total de registros
    $stmt = $conn->query("SELECT FOUND_ROWS() as total");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_paginas = ceil($total / $por_pagina);
    
    // Respuesta con datos y metadatos de paginación
    echo json_encode([
        'success' => true,
        'data' => $citas,
        'pagination' => [
            'total' => $total,
            'pagina_actual' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => $total_paginas
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}