<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 día
        'cookie_secure' => true,    // Solo en HTTPS
        'cookie_httponly' => true,  // Acceso solo por HTTP
        'use_strict_mode' => true   // Mayor seguridad
    ]);
}

// Verificar si es administrador (rol_id = 1)
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] !== 1) {
    header("Location: login_admin.php");
    exit;
}
?>