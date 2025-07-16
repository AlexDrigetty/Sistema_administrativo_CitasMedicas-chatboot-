<?php
session_start();

// Guardar mensaje antes de destruir la sesión
$_SESSION['mensaje'] = [
    'tipo' => 'success',
    'texto' => 'Se ha cerrado sesión correctamente'
];

$_SESSION = array();

// Si se desea destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

header("Location: ../Usuario/login.php?logout=1");
exit;
?>