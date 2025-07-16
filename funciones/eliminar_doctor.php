<?php
require_once '../bd/conexion.php';
session_start();

// Verificar si es una solicitud POST y si el usuario tiene permisos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

// Verificar que se recibió el ID
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = $_POST['id'];

try {
    $conn = conectarDB();
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // 1. Eliminar registros relacionados en otras tablas primero
    // (Asegúrate de agregar aquí cualquier tabla que tenga FK a doctores o usuarios)
    
    // 2. Eliminar el doctor
    $queryDoctor = "DELETE FROM doctores WHERE usuario_id = :id";
    $stmtDoctor = $conn->prepare($queryDoctor);
    $stmtDoctor->execute([':id' => $id]);
    
    // 3. Eliminar el usuario
    $queryUsuario = "DELETE FROM usuarios WHERE id = :id";
    $stmtUsuario = $conn->prepare($queryUsuario);
    $stmtUsuario->execute([':id' => $id]);
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Doctor eliminado permanentemente']);
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el doctor: ' . $e->getMessage()]);
}
?>