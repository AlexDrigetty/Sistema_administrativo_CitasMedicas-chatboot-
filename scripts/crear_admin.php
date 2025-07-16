<?php
// insertar_admin.php
require_once '../bd/conexion.php';

// Función para encriptar la contraseña
function encriptarContraseña($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Datos del administrador
$adminData = [
    'dni' => '00000000', // DNI genérico para admin
    'nombre' => 'Administrador',
    'apellido' => 'Principal',
    'email' => 'admin@test.com',
    'password' => 'admin123', 
    'telefono' => '999888777',
    'direccion' => 'Dirección administrativa',
    'fecha_nacimiento' => '1980-01-01'
];

try {
    $conn = conectarDB();
    $conn->beginTransaction();

    // 1. Insertar en la tabla usuarios
    $stmtUsuario = $conn->prepare("
        INSERT INTO usuarios (
            dni, 
            nombre, 
            apellido, 
            direccion, 
            fecha_nacimiento, 
            telefono, 
            email, 
            contraseña, 
            rol_id, 
            activo
        ) VALUES (
            :dni, 
            :nombre, 
            :apellido, 
            :direccion, 
            :fecha_nacimiento, 
            :telefono, 
            :email, 
            :contrasena, 
            (SELECT id FROM roles WHERE nombre = 'administrador'), 
            1
        )
    ");

    $stmtUsuario->execute([
        ':dni' => $adminData['dni'],
        ':nombre' => $adminData['nombre'],
        ':apellido' => $adminData['apellido'],
        ':direccion' => $adminData['direccion'],
        ':fecha_nacimiento' => $adminData['fecha_nacimiento'],
        ':telefono' => $adminData['telefono'],
        ':email' => $adminData['email'],
        ':contrasena' => encriptarContraseña($adminData['password'])
    ]);

    $usuarioId = $conn->lastInsertId();

    // 2. Insertar en la tabla administradores
    $stmtAdmin = $conn->prepare("
        INSERT INTO administradores (
            usuario_id, 
            nombre, 
            apellido, 
            telefono
        ) VALUES (
            :usuario_id, 
            :nombre, 
            :apellido, 
            :telefono
        )
    ");

    $stmtAdmin->execute([
        ':usuario_id' => $usuarioId,
        ':nombre' => $adminData['nombre'],
        ':apellido' => $adminData['apellido'],
        ':telefono' => $adminData['telefono']
    ]);

    $conn->commit();

    echo "Administrador creado exitosamente!<br>";
    echo "ID de usuario: " . $usuarioId . "<br>";
    echo "Email: " . $adminData['email'] . "<br>";
    echo "Contraseña temporal: " . $adminData['password'] . "<br>";
    echo "<strong>IMPORTANTE:</strong> Cambia esta contraseña después del primer inicio de sesión.";

} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error al crear el administrador: " . $e->getMessage();
}
?>