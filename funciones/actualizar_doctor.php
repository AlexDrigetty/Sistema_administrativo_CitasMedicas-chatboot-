<?php
require_once '../bd/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$required = ['id', 'nombre', 'apellidos', 'especialidad', 'telefono', 'email'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Campo $field es requerido"]);
        exit;
    }
}

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$especialidad = $_POST['especialidad'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$foto = $_POST['foto'] ?? null;
$contrasena = $_POST['contrasena'] ?? null;

// Manejo seguro del campo activo
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1; // Valor por defecto 1 (activo) si no se recibe

try {
    $conn = conectarDB();
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Actualizar datos en la tabla doctores (incluyendo el estado activo)
    $queryDoctor = "UPDATE doctores SET 
                    nombre = :nombre, 
                    apellidos = :apellidos, 
                    especialidad = :especialidad,
                    telefono = :telefono,
                    activo = :activo
                    WHERE usuario_id = :id";
    
    $stmtDoctor = $conn->prepare($queryDoctor);
    $stmtDoctor->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':especialidad' => $especialidad,
        ':telefono' => $telefono,
        ':activo' => $activo,
        ':id' => $id
    ]);
    
    // Actualizar datos en la tabla usuarios (email, estado, foto)
    $queryUsuario = "UPDATE usuarios SET 
                    email = :email,
                    activo = :activo";
    
    $params = [
        ':email' => $email,
        ':activo' => $activo,
        ':id' => $id
    ];
    
    // Si hay foto, agregar a la consulta
    if ($foto) {
        $queryUsuario .= ", foto = :foto";
        $params[':foto'] = $foto;
    }
    
    $queryUsuario .= " WHERE id = :id";
    
    $stmtUsuario = $conn->prepare($queryUsuario);
    $stmtUsuario->execute($params);
    
    if (!empty($contrasena)) {
        $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
        $queryPassword = "UPDATE usuarios SET contraseña = :contrasena WHERE id = :id";
        $stmtPassword = $conn->prepare($queryPassword);
        $stmtPassword->execute([
            ':contrasena' => $hashedPassword,
            ':id' => $id
        ]);
    }
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Doctor actualizado correctamente']);
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el doctor: ' . $e->getMessage()]);
}
?>