<?php
require_once '../bd/conexion.php';

function loginUser($email, $password) {
    $conn = conectarDB();
    
    try {
        $stmt = $conn->prepare("
            SELECT id, contraseña, rol_id 
            FROM usuarios 
            WHERE email = ? AND activo = 1
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['contraseña'])) {
            if ($usuario['rol_id'] == 1 || $usuario['rol_id'] == 2) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start([
                        'cookie_lifetime' => 86400,
                        'cookie_secure' => true,
                        'cookie_httponly' => true,
                        'use_strict_mode' => true
                    ]);
                    session_regenerate_id(true);
                }
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['logged_in'] = true;
                
                return $usuario['rol_id']; 
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        return false;
    }
}
?>