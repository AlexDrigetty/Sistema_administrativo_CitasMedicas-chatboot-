<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar evento de logout (opcional)
require_once 'conexion.php';
if (isset($_SESSION['usuario_id'])) {
    try {
        $conn = conectarDB();
        $stmt = $conn->prepare("
            INSERT INTO logs_acceso (usuario_id, tipo_evento) 
            VALUES (?, 'LOGOUT')
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
    } catch (PDOException $e) {
        error_log("Error al registrar logout: " . $e->getMessage());
    }
}

// Limpiar datos de sesión
$_SESSION = array();

// Eliminar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruir sesión
session_destroy();

// Redirigir al login
header('Location: ../Admin/login_admin.php');
exit;
?>