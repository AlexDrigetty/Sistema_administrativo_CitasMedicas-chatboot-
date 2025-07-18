<?php
require_once  '../bd/conexion.php';

/**
 * Obtiene estadísticas comparativas para las tarjetas
 */
function getEstadisticasComparativas() {
    $conn = conectarDB();
    
    // Estadísticas de pacientes (rol_id = 3)
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN fecha_registro >= CURDATE() THEN 1 ELSE 0 END) as hoy,
                SUM(CASE WHEN fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND fecha_registro < CURDATE() THEN 1 ELSE 0 END) as ultima_semana
              FROM usuarios
              WHERE rol_id = 3";
    $stmt = $conn->query($query);
    $pacientes = $stmt->fetch();
    
    // Estadísticas de doctores
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN fecha_ingreso >= CURDATE() THEN 1 ELSE 0 END) as hoy,
                SUM(CASE WHEN fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND fecha_ingreso < CURDATE() THEN 1 ELSE 0 END) as ultima_semana
              FROM doctores";
    $stmt = $conn->query($query);
    $doctores = $stmt->fetch();
    
    // Estadísticas de citas pendientes
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN fecha_hora >= CURDATE() AND fecha_hora < CURDATE() + INTERVAL 1 DAY THEN 1 ELSE 0 END) as hoy,
                SUM(CASE WHEN fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND fecha_hora < CURDATE() THEN 1 ELSE 0 END) as ultima_semana
              FROM citas_medicas
              WHERE estado = 'pendiente'";
    $stmt = $conn->query($query);
    $citas_pendientes = $stmt->fetch();
    
    // Estadísticas de citas completadas
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN fecha_hora >= CURDATE() AND fecha_hora < CURDATE() + INTERVAL 1 DAY THEN 1 ELSE 0 END) as hoy,
                SUM(CASE WHEN fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND fecha_hora < CURDATE() THEN 1 ELSE 0 END) as ultima_semana
              FROM citas_medicas
              WHERE estado = 'completada'";
    $stmt = $conn->query($query);
    $citas_completadas = $stmt->fetch();
    
    return [
        'pacientes' => [
            'total' => $pacientes['total'],
            'hoy' => $pacientes['hoy'],
            'variacion' => $pacientes['ultima_semana'] > 0 ? 
                          round(($pacientes['hoy'] - ($pacientes['ultima_semana']/7)) / ($pacientes['ultima_semana']/7) * 100, 1) : 0
        ],
        'doctores' => [
            'total' => $doctores['total'],
            'hoy' => $doctores['hoy'],
            'variacion' => $doctores['ultima_semana'] > 0 ? 
                          round(($doctores['hoy'] - ($doctores['ultima_semana']/7)) / ($doctores['ultima_semana']/7) * 100, 1) : 0
        ],
        'citas_pendientes' => [
            'total' => $citas_pendientes['total'],
            'hoy' => $citas_pendientes['hoy'],
            'variacion' => $citas_pendientes['ultima_semana'] > 0 ? 
                          round(($citas_pendientes['hoy'] - ($citas_pendientes['ultima_semana']/7)) / ($citas_pendientes['ultima_semana']/7) * 100, 1) : 0
        ],
        'citas_completadas' => [
            'total' => $citas_completadas['total'],
            'hoy' => $citas_completadas['hoy'],
            'variacion' => $citas_completadas['ultima_semana'] > 0 ? 
                          round(($citas_completadas['hoy'] - ($citas_completadas['ultima_semana']/7)) / ($citas_completadas['ultima_semana']/7) * 100, 1) : 0
        ]
    ];
}

/**
 * Obtiene datos para el gráfico de actividad reciente (7 días) - VERSIÓN CORREGIDA
 */
function getActividadReciente() {
    $conn = conectarDB();
    
    // Consulta corregida para ONLY_FULL_GROUP_BY
    $query = "SELECT 
                fechas.fecha,
                COUNT(i.interaccion_id) as interacciones,
                (
                    SELECT COUNT(*) 
                    FROM citas_medicas 
                    WHERE DATE(fecha_hora) = fechas.fecha
                ) as citas
              FROM (
                  SELECT DISTINCT DATE(fecha_interaccion) as fecha
                  FROM interacciones_chatbot
                  WHERE fecha_interaccion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              ) fechas
              LEFT JOIN interacciones_chatbot i ON DATE(i.fecha_interaccion) = fechas.fecha
              GROUP BY fechas.fecha
              ORDER BY fechas.fecha";
    
    $stmt = $conn->query($query);
    $datos = $stmt->fetchAll();
    
    // Formatear para Chart.js
    $labels = [];
    $interacciones = [];
    $citas = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($fecha));
        
        $encontrado = false;
        foreach ($datos as $dato) {
            if ($dato['fecha'] == $fecha) {
                $interacciones[] = (int)$dato['interacciones'];
                $citas[] = (int)$dato['citas'];
                $encontrado = true;
                break;
            }
        }
        
        if (!$encontrado) {
            $interacciones[] = 0;
            $citas[] = 0;
        }
    }
    
    return [
        'labels' => $labels,
        'interacciones' => $interacciones,
        'citas' => $citas
    ];
}

/**
 * Obtiene las citas pendientes más recientes (12 primeras)
 */
function getCitasPendientes() {
    $conn = conectarDB();
    
    $query = "SELECT 
                c.ticket_code,
                CONCAT(p.nombre, ' ', p.apellido) as paciente,
                CONCAT(d.nombre, ' ', d.apellidos) as doctor,
                d.especialidad,
                c.estado,
                c.fecha_hora
              FROM citas_medicas c
              JOIN perfiles_pacientes p ON c.usuario_id = p.usuario_id
              JOIN doctores d ON c.doctor_id = d.usuario_id
              WHERE c.estado = 'pendiente'
              ORDER BY c.fecha_hora DESC
              LIMIT 12";
    
    $stmt = $conn->query($query);
    return $stmt->fetchAll();
}

/**
 * Obtiene los últimos pacientes registrados (rol_id = 3)
 */
function getPacientesRecientes() {
    $conn = conectarDB();
    
    $query = "SELECT 
                u.id,
                CONCAT(u.nombre, ' ', u.apellido) as nombre_completo,
                u.email,
                u.fecha_registro
              FROM usuarios u
              WHERE u.rol_id = 3
              ORDER BY u.fecha_registro DESC
              LIMIT 12";
    
    $stmt = $conn->query($query);
    return $stmt->fetchAll();
}
?>