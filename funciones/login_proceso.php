<?php
session_start();
require_once '../bd/conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $dni = $_POST['dni'] ?? '';

    if (empty($dni) || !preg_match('/^\d{8}$/', $dni)) {
        throw new Exception("Por favor ingrese un DNI válido de 8 dígitos");
    }

    $db = conectarDB();

    // 1. Buscar TODOS los usuarios pacientes (rol_id = 3)
    $query = "SELECT u.*, p.dni AS dni_paciente 
              FROM usuarios u 
              JOIN perfiles_pacientes p ON u.id = p.usuario_id 
              WHERE u.rol_id = 3";
    
    $stmt = $db->query($query);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $usuarioEncontrado = null;

    // 2. Buscar coincidencia con password_verify (para DNI encriptado)
    foreach ($usuarios as $usuario) {
        if (password_verify($dni, $usuario['dni'])) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }

    if (!$usuarioEncontrado) {
        throw new Exception("DNI no registrado. Regístrate primero.");
    }

    // 3. Verificar la contraseña (que es el hash del DNI)
    if (!password_verify($dni, $usuarioEncontrado['contraseña'])) {
        throw new Exception("Credenciales incorrectas");
    }

    // Crear sesión con flag para mostrar alerta
    $_SESSION['usuario'] = [
        'id' => $usuarioEncontrado['id'],
        'dni' => $dni, // Guardamos el DNI SIN encriptar en sesión
        'nombre' => $usuarioEncontrado['nombre'],
        'apellido' => $usuarioEncontrado['apellido'],
        'rol_id' => $usuarioEncontrado['rol_id'],
        'autenticado' => true,
        'mostrar_alerta' => true
    ];

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    $response['message'] = "Error en el sistema. Intente nuevamente.";
}

echo json_encode($response);
?>