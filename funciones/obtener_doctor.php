<?php
require_once '../bd/conexion.php';
header('Content-Type: application/json');

try {
    $conn = conectarDB();
    
    $query = "SELECT 
                d.usuario_id as id,
                u.nombre as nombre,
                u.apellido as apellidos,
                d.especialidad as especialidad,
                d.telefono as telefono,
                u.email as email,
                d.activo as activo,
                d.nro_colegiatura as colegiatura
              FROM doctores d
              JOIN usuarios u ON d.usuario_id = u.id
              ORDER BY u.apellido, u.nombre";
    
    $stmt = $conn->query($query);
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($doctores);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al cargar los doctores: ' . $e->getMessage()
    ]);
}