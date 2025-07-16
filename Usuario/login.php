<?php
session_start();
$mostrarAlerta = isset($_GET['registro']) && $_GET['registro'] === 'exitoso';
$mostrarAlertaLogout = isset($_GET['logout']) && $_GET['logout'] == 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/formularios.css">
</head>
<body>
    <div class="container">
        <form id="loginUsuario">
            <h3 class="py-4 mb-3"><i class="fa fa-user-lock"></i> INGRESO PACIENTES</h3>

            <div class="login mb-4">
                <label class="form-label mb-3">Ingrese su DNI</label>
                <div class="box-input mb-3">
                    <input type="text" class="form-control" id="dni" name="dni" placeholder="DNI (8 dígitos)" maxlength="8">
                    <i class="bi bi-card-text"></i>
                </div>

                <p class="text-muted">¿No tienes cuenta? <a href="../Usuario/registro.php">Regístrate aquí</a></p>
            </div>

            <div class="iniciar mb-3">
                <button type="submit" class="btn btn-primary"><i class="fa fa-sign-in"></i> Ingresar</button>
            </div>
        </form>

        <a href="../Usuario/Tipo_user.php" class="menu"><i class="fa-solid fa-reply"></i> Regresar</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // TUS ALERTAS EXISTENTES (sin modificaciones)
        function mostrarAlerta(icon, title, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                text: message,
                showConfirmButton: false,
                timer: 3000,
                background: '#f8f9fa',
                color: '#212529'
            });
        }

        document.getElementById('dni').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });

        document.getElementById('loginUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const dni = document.getElementById('dni').value.trim();
            const btnSubmit = this.querySelector('button[type="submit"]');
            
            if (dni.length !== 8) {
                mostrarAlerta('error', 'DNI incorrecto', 'Debe tener exactamente 8 dígitos');
                return;
            }

            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Ingresando...';
            btnSubmit.disabled = true;

            fetch('../funciones/login_proceso.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `dni=${dni}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "../chatBoot/chatbot.php";
                } else {
                    mostrarAlerta('error', 'Error', data.message || 'DNI no registrado');
                }
            })
            .catch(error => {
                mostrarAlerta('error', 'Error', 'No se pudo conectar al servidor');
            })
            .finally(() => {
                btnSubmit.innerHTML = '<i class="fa fa-sign-in"></i> Ingresar';
                btnSubmit.disabled = false;
            });
        });

        <?php if ($mostrarAlertaLogout): ?>
            document.addEventListener('DOMContentLoaded', function() {
                mostrarAlerta('success', 'Sesión cerrada', 'Has salido del sistema');
                history.replaceState(null, '', window.location.pathname);
            });
        <?php endif; ?>

        <?php if ($mostrarAlerta): ?>
            document.addEventListener('DOMContentLoaded', function() {
                mostrarAlerta('success', 'Registro exitoso', 'Ahora puedes iniciar sesión con tu DNI');
                history.replaceState(null, '', window.location.pathname);
            });
        <?php endif; ?>
    </script>
</body>
</html>