<?php
require_once '../bd/conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

try {
    $conn = conectarDB();
    
    $query = "SELECT d.usuario_id as id, d.dni, d.nombre, d.apellidos, d.especialidad, 
                     d.fecha_ingreso, d.nro_colegiatura, d.activo,
                     u.email, u.telefono, u.direccion
              FROM doctores d
              JOIN usuarios u ON d.usuario_id = u.id
              WHERE d.usuario_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['id']]);
    
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($doctor);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>