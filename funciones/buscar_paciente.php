<?php
header('Content-Type: application/json');
require_once '../bd/conexion.php';

$dni = $_GET['dni'] ?? '';

if (empty($dni)) {
    echo json_encode(['error' => 'DNI no proporcionado']);
    exit;
}

try {
    $db = conectarDB();
    
    $query = "SELECT nombre, apellido, direccion, telefono, email 
              FROM pacientes 
              WHERE dni = :dni 
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
    $stmt->execute();

    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        echo json_encode([
            'success' => true,
            'data' => [
                'nombre' => $paciente['nombre'],
                'apellido' => $paciente['apellido'],
                'direccion' => $paciente['direccion'],
                'telefono' => $paciente['telefono'],
                'email' => $paciente['email']
            ]
        ]);
    } else {
        echo json_encode(['error' => 'Paciente no encontrado']);
    }
    
} catch (PDOException $e) {
    error_log("Error en buscar_paciente: " . $e->getMessage());
    echo json_encode(['error' => 'Error al buscar paciente']);
}
?>