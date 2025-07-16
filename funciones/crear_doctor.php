<?php
require_once '../bd/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$required = ['dni', 'nombre', 'apellidos', 'telefono', 'email', 'especialidad', 'contrasena'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
        exit;
    }
}

// Validar DNI (8 dígitos)
if (!preg_match('/^[0-9]{8}$/', $_POST['dni'])) {
    echo json_encode(['success' => false, 'message' => 'El DNI debe tener exactamente 8 dígitos numéricos']);
    exit;
}

// Validar contraseña
if (strlen($_POST['contrasena']) < 8) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

$dni = $_POST['dni'];
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$especialidad = $_POST['especialidad'];
$contrasena = $_POST['contrasena'];

try {
    $conn = conectarDB();
    $conn->beginTransaction();

    // Encriptar el DNI usando BCRYPT (produce un hash de 60 caracteres)
    $dniEncriptado = password_hash($dni, PASSWORD_BCRYPT);

    // Verificar si el DNI ya existe comparando hashes
    $stmtCheck = $conn->prepare("SELECT dni FROM usuarios");
    $stmtCheck->execute();
    $dnisExistentes = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($dnisExistentes as $dniExistente) {
        if (password_verify($dni, $dniExistente)) {
            throw new PDOException('El DNI ya está registrado', 23000);
        }
    }

    // 1. Crear usuario primero
    $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
    $queryUsuario = "INSERT INTO usuarios (dni, nombre, apellido, telefono, email, contraseña, rol_id, activo) 
                    VALUES (:dni, :nombre, :apellido, :telefono, :email, :contrasena, 
                    (SELECT id FROM roles WHERE nombre = 'doctor'), 1)";
    
    $stmtUsuario = $conn->prepare($queryUsuario);
    $stmtUsuario->execute([
        ':dni' => $dniEncriptado,
        ':nombre' => $nombre,
        ':apellido' => $apellidos,
        ':telefono' => $telefono,
        ':email' => $email,
        ':contrasena' => $hashedPassword
    ]);
    
    $usuarioId = $conn->lastInsertId();

    // 2. Crear registro en doctores con fecha_ingreso automática
    $queryDoctor = "INSERT INTO doctores 
                   (usuario_id, dni, nombre, apellidos, telefono, especialidad, fecha_ingreso, activo)
                   VALUES 
                   (:usuario_id, :dni, :nombre, :apellidos, :telefono, :especialidad, CURDATE(), 1)";
    
    $stmtDoctor = $conn->prepare($queryDoctor);
    $stmtDoctor->execute([
        ':usuario_id' => $usuarioId,
        ':dni' => $dniEncriptado, // Mismo DNI encriptado que en usuarios
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':telefono' => $telefono,
        ':especialidad' => $especialidad
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Doctor creado correctamente']);

} catch (PDOException $e) {
    $conn->rollBack();
    
    if ($e->getCode() == 23000) {
        if (strpos($e->getMessage(), 'email')) {
            echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        } elseif (strpos($e->getMessage(), 'dni')) {
            echo json_encode(['success' => false, 'message' => 'El DNI ya está registrado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error de duplicado: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear doctor: ' . $e->getMessage()]);
    }
}
?>