<?php
require_once  '../bd/conexion.php';

/**
 * Obtiene los síntomas de la base de datos
 */
function obtenerSintomas() {
    $db = conectarDB();
    $query = "SELECT sintoma_id, nombre, parte_cuerpo FROM sintomas";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Detecta síntomas en el texto del usuario
 */
function detectarSintomasEnTexto($texto) {
    $sintomas = obtenerSintomas();
    $detectados = [];
    
    foreach ($sintomas as $sintoma) {
        if (stripos($texto, $sintoma['nombre']) !== false) {
            $detectados[] = $sintoma;
        }
    }
    
    return $detectados;
}

/**
 * Obtiene posibles enfermedades basadas en síntomas
 */
function obtenerEnfermedadesPorSintomas($sintomasIds) {
    $db = conectarDB();
    
    if (empty($sintomasIds)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($sintomasIds), '?'));
    
    $query = "SELECT e.*, COUNT(es.sintoma_id) as coincidencias
              FROM enfermedades e
              JOIN enfermedad_sintoma es ON e.enfermedad_id = es.enfermedad_id
              WHERE es.sintoma_id IN ($placeholders)
              GROUP BY e.enfermedad_id
              ORDER BY coincidencias DESC
              LIMIT 3";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($sintomasIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener enfermedades: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene medicamentos recomendados para enfermedades
 */
function obtenerMedicamentosPorEnfermedades($enfermedadesIds) {
    $db = conectarDB();
    
    if (empty($enfermedadesIds)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($enfermedadesIds), '?'));
    
    $query = "SELECT DISTINCT m.*
              FROM medicamentos m
              JOIN enfermedad_medicamento em ON m.medicamento_id = em.medicamento_id
              WHERE em.enfermedad_id IN ($placeholders)";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($enfermedadesIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener medicamentos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene doctores por especialidad
 */
function obtenerDoctoresPorEspecialidad($especialidad) {
    $db = conectarDB();
    
    // Normalizar la especialidad
    $especialidad = trim($especialidad);
    
    $query = "SELECT 
                d.usuario_id,
                d.nombre,
                d.apellidos as apellido,
                d.especialidad,
                d.telefono,
                u.email
              FROM doctores d
              JOIN usuarios u ON d.usuario_id = u.id
              WHERE d.especialidad = ?
              AND d.activo = TRUE
              ORDER BY d.nombre";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$especialidad]);
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no encontramos doctores, intentamos con Medicina General
        if (empty($doctores) && $especialidad !== 'Medicina General') {
            return obtenerDoctoresPorEspecialidad('Medicina General');
        }
        
        return $doctores;
    } catch (PDOException $e) {
        error_log("Error al obtener doctores: " . $e->getMessage());
        return [];
    }
}

/**
 * Calcula el nivel de prioridad basado en síntomas
 */
function calcularPrioridad($sintomasDetallados) {
    $puntaje = 0;
    
    foreach ($sintomasDetallados as $sintoma) {
        // Puntaje por tiempo de presentación
        switch ($sintoma['tiempo']) {
            case 'ahora': $puntaje += 5; break;
            case '1_dia': $puntaje += 4; break;
            case '3_dias': $puntaje += 3; break;
            case '1_semana': $puntaje += 2; break;
        }
        
        // Puntaje por intensidad
        switch ($sintoma['intensidad']) {
            case 'leve': $puntaje += 1; break;
            case 'moderado': $puntaje += 3; break;
            case 'intenso': $puntaje += 5; break;
        }
    }
    
    // Determinar nivel de prioridad
    if ($puntaje >= 15) return 'maxima';
    if ($puntaje >= 10) return 'rapido';
    return 'moderado';
}

/**
 * Registra una interacción en la base de datos
 */
function registrarInteraccion($usuarioId, $descripcion) {
    $db = conectarDB();
    $query = "INSERT INTO interacciones_chatbot (usuario_id, descripcion_inicial) VALUES (?, ?)";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$usuarioId, $descripcion]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al registrar interacción: " . $e->getMessage());
        return null;
    }
}

/**
 * Registra un diagnóstico en la base de datos
 */
function registrarDiagnostico($interaccionId, $sintomasDetallados, $enfermedades, $medicamentos, $prioridad) {
    $db = conectarDB();
    
    try {
        $query = "INSERT INTO diagnosticos (interaccion_id, puntaje_total, nivel_prioridad) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $puntajeTotal = array_sum(array_column($sintomasDetallados, 'puntaje'));
        $stmt->execute([$interaccionId, $puntajeTotal, $prioridad]);
        $diagnosticoId = $db->lastInsertId();
        
        foreach ($sintomasDetallados as $sintoma) {
            $query = "INSERT INTO diagnostico_sintomas (diagnostico_id, sintoma_id, intensidad, tiempo_presentacion, puntaje_individual) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $diagnosticoId,
                $sintoma['sintoma_id'],
                $sintoma['intensidad'],
                $sintoma['tiempo'],
                $sintoma['puntaje']
            ]);
        }
        
        foreach ($enfermedades as $enfermedad) {
            $query = "INSERT INTO diagnostico_enfermedades (diagnostico_id, enfermedad_id, probabilidad) 
                      VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $probabilidad = min(100, $enfermedad['coincidencias'] * 20); // Máximo 100%
            $stmt->execute([$diagnosticoId, $enfermedad['enfermedad_id'], $probabilidad]);
        }
        
        foreach ($medicamentos as $medicamento) {
            $query = "INSERT INTO diagnostico_medicamentos (diagnostico_id, medicamento_id) 
                      VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$diagnosticoId, $medicamento['medicamento_id']]);
        }
        
        return $diagnosticoId;
    } catch (PDOException $e) {
        error_log("Error al registrar diagnóstico: " . $e->getMessage());
        return null;
    }
}

function mapearEspecialidad($especialidadEnfermedad) {
    $mapeo = [
        'Cardiología' => 'Cardiología',
        'Neurología' => 'Neurología',
        'Medicina General' => 'Medicina General',
        'Gastroenterología' => 'Medicina General',
        'Neumología' => 'Medicina General',
        'Pediatría' => 'Pediatría',
        'Dermatología' => 'Dermatología',
        'Traumatología' => 'Traumatología',
        'Oftalmología' => 'Oftalmología',
        'Oncología' => 'Oncología',
        'Ginecología' => 'Ginecología',
        'Psiquiatría' => 'Psiquiatría',
        'Endocrinología' => 'Endocrinología'
    ];
    
    return $mapeo[trim($especialidadEnfermedad)] ?? 'Medicina General';
}

/**
 * Registra una cita médica
 */
function registrarCita($interaccionId, $usuarioId, $doctorId, $especialidad) {
    $db = conectarDB();
    
    try {
        $prefix = substr(strtoupper($especialidad), 0, 2);
        $query = "SELECT COUNT(*) as count FROM citas_medicas WHERE doctor_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$doctorId]);
        $count = $stmt->fetch()['count'] + 1;
        $ticketCode = $prefix . '-' . $count;
        
        $fechaHora = date('Y-m-d H:i:s'); 
        
        $query = "INSERT INTO citas_medicas (interaccion_id, usuario_id, doctor_id, ticket_code, fecha_hora) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$interaccionId, $usuarioId, $doctorId, $ticketCode, $fechaHora]);
        
        return [
            'ticket' => $ticketCode,
            'fecha' => $fechaHora,
            'doctor_id' => $doctorId
        ];
    } catch (PDOException $e) {
        error_log("Error al registrar cita: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene información del doctor
 */
function obtenerInfoDoctor($doctorId) {
    $db = conectarDB();
    $query = "SELECT d.*, u.nombre, u.apellido 
              FROM doctores d
              JOIN usuarios u ON d.usuario_id = u.id
              WHERE d.usuario_id = ?";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([$doctorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener información del doctor: " . $e->getMessage());
        return null;
    }
}