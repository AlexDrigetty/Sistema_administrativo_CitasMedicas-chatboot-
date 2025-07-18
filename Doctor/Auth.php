<?php
// funciones/AuthDoctor.php

function doctorAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400,
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'use_strict_mode' => true
        ]);
    }

    // Verificar si el usuario está logueado y es doctor (rol_id = 2)
    if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
        header('Location: ../index.php');
        exit;
    }

    // Verificar si la sesión ha expirado
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $session_timeout = 1800; // 30 minutos en segundos
        if (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout) {
            session_unset();
            session_destroy();
            header('Location: ../index.php?session_expired=1');
            exit;
        }
    }
    $_SESSION['LAST_ACTIVITY'] = time();

    // Regenerar el ID de sesión periódicamente para prevenir fixation
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

// Función para obtener información del doctor
function getDoctorInfo($usuario_id) {
    require_once '../bd/conexion.php';
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("
            SELECT d.*, u.email 
            FROM doctores d
            JOIN usuarios u ON d.usuario_id = u.id
            WHERE d.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener información del doctor: " . $e->getMessage());
        return false;
    }
}

// Función para cerrar sesión
function doctorLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Borrar la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login
    header('Location: ../index.php');
    exit;
}
?>