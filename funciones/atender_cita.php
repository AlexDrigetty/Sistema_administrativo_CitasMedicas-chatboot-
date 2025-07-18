<?php
require_once '../Doctor/Auth.php';
doctorAuth();

// Verificar que se haya proporcionado un ID de cita
if (!isset($_GET['id'])) {
    header('Location: ../Doctor/dashboard_doctor.php?error=no_id');
    exit;
}

$cita_id = $_GET['id'];

// Conectar a la base de datos
require_once '../bd/conexion.php';
$conn = conectarDB();

try {
    // Primero, obtener el estado actual de la cita
    $stmt = $conn->prepare("SELECT estado FROM citas_medicas WHERE cita_id = ? AND doctor_id = ?");
    $stmt->execute([$cita_id, $_SESSION['usuario_id']]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cita) {
        header('Location: dashboard_doctor.php?error=cita_no_encontrada');
        exit;
    }
    
    // Determinar el nuevo estado basado en el estado actual
    $nuevo_estado = '';
    if ($cita['estado'] == 'pendiente') {
        $nuevo_estado = 'atendiendo';
    } elseif ($cita['estado'] == 'atendiendo') {
        $nuevo_estado = 'completada';
    } else {
        header('Location: ../Doctor/dashboard_doctor.php?error=estado_no_valido');
        exit;
    }
    
    // Actualizar el estado de la cita
    $stmt = $conn->prepare("UPDATE citas_medicas SET estado = ? WHERE cita_id = ?");
    $stmt->execute([$nuevo_estado, $cita_id]);
    
    // Redirigir de vuelta al dashboard con mensaje de éxito
    header('Location: ../Doctor/dashboard_doctor.php?success=estado_actualizado');
    exit;
    
} catch (PDOException $e) {
    error_log("Error al actualizar estado de cita: " . $e->getMessage());
    header('Location: ../Doctor/dashboard_doctor.php?error=db_error');
    exit;
}
?>