<?php
require_once '../bd/conexion.php';
require_once '../funciones/procesar_login_AD.php'; // Cambiado a procesar_login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 1) {
        header('Location: Admin_panel.php');
        exit;
    } elseif ($_SESSION['rol_id'] == 2) {
        header('Location: dashboard_doctor.php');
        exit;
    }
}

// Procesar formulario
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    $rol_id = loginUser($email, $password);

    if ($rol_id === 1) {
        header('Location: Admin_panel.php');
        exit;
    } elseif ($rol_id === 2) {
        header('Location: ../Doctor/dashboard_doctor.php');
        exit;
    } else {
        $error = "Credenciales incorrectas o no tiene permisos de acceso";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Clínica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login_AD.css">

</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <h3 class="text-center mb-3"> Login PriorizaNow</h3>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Ingrese email">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Ingrese contraseña">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="boton-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>