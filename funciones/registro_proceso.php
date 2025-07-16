<?php
require_once '../bd/conexion.php';

try {
    // Datos del formulario
    $dni = $_POST['dni'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;

    // Validaciones básicas
    if (!preg_match('/^\d{8}$/', $dni)) {
        throw new Exception("Error: El DNI debe contener exactamente 8 dígitos");
    }

    if (empty($nombre) || empty($apellido) || empty($correo)) {
        throw new Exception("Error: Nombre, apellido y correo son obligatorios");
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Error: Formato de correo electrónico inválido");
    }

    $rol_id = 3; // Rol paciente
    
    // Encriptar DNI y contraseña (que es el mismo DNI)
    $dni_encriptado = password_hash($dni, PASSWORD_BCRYPT);
    $contrasena_encriptada = password_hash($dni, PASSWORD_BCRYPT);

    // Conexión a la base de datos
    $db = conectarDB();

    // Iniciar transacción
    $db->beginTransaction();

    // Verificar si el email ya existe (el DNI no se puede verificar por estar encriptado)
    $verificar = "SELECT id FROM usuarios WHERE email = :email";
    $stmt_ver = $db->prepare($verificar);
    $stmt_ver->bindParam(':email', $correo);
    $stmt_ver->execute();

    if ($stmt_ver->rowCount() > 0) {
        throw new Exception("Error: El correo electrónico ya está registrado");
    }

    // Insertar el nuevo usuario
    $query = "INSERT INTO usuarios 
              (dni, nombre, apellido, direccion, email, contraseña, rol_id, telefono, fecha_nacimiento) 
              VALUES (:dni, :nombre, :apellido, :direccion, :email, :contrasena, :rol_id, :telefono, :fecha_nacimiento)";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':dni', $dni_encriptado);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':email', $correo);
    $stmt->bindParam(':contrasena', $contrasena_encriptada);
    $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento ?: null, PDO::PARAM_STR);

    if (!$stmt->execute()) {
        throw new Exception("Error al registrar el usuario");
    }

    // Obtener el ID del usuario recién insertado
    $usuario_id = $db->lastInsertId();

    // Insertar en perfiles_pacientes (con DNI ENCRIPTADO)
    $query_paciente = "INSERT INTO perfiles_pacientes 
                      (usuario_id, dni, nombre, apellido, direccion, telefono, fecha_nacimiento)
                      VALUES (:usuario_id, :dni, :nombre, :apellido, :direccion, :telefono, :fecha_nacimiento)";

    $stmt_paciente = $db->prepare($query_paciente);

    $stmt_paciente->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt_paciente->bindParam(':dni', $dni_encriptado); // Ahora con DNI encriptado
    $stmt_paciente->bindParam(':nombre', $nombre);
    $stmt_paciente->bindParam(':apellido', $apellido);
    $stmt_paciente->bindParam(':direccion', $direccion);
    $stmt_paciente->bindParam(':telefono', $telefono);
    $stmt_paciente->bindValue(':fecha_nacimiento', $fecha_nacimiento ?: null, PDO::PARAM_STR);

    if (!$stmt_paciente->execute()) {
        throw new Exception("Error al crear el perfil del paciente");
    }

    // Confirmar la transacción
    $db->commit();

    header("Location: ../Usuario/login.php?registro=exitoso");
    exit;

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    die($e->getMessage());
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    die("Error de base de datos: " . $e->getMessage());
}