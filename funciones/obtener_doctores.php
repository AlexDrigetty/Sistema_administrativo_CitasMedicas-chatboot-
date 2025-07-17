<?php
require_once '../bd/conexion.php';
header('Content-Type: application/json');

try {
    $conn = conectarDB();
    
    $query = "SELECT d.usuario_id, u.nombre, u.apellido, d.especialidad 
              FROM doctores d
              JOIN usuarios u ON d.usuario_id = u.id
              WHERE d.activo = TRUE
              ORDER BY u.apellido, u.nombre";
    
    $stmt = $conn->query($query);
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($doctores);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al cargar los doctores: ' . $e->getMessage()
    ]);
}