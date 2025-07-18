<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el nombre del archivo actual
$current_page = basename($_SERVER['PHP_SELF']);

// Manejar el tema oscuro
if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'] === 'dark' ? 'dark' : 'light';
}

$current_theme = $_SESSION['theme'] ?? 'light';
?>

<!DOCTYPE html>
<html lang="es" data-theme="<?php echo $current_theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Aplicación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inicio.css">

</head>
<body>
<div class="slider">
    <div class="slider-cabeza">
        <h4 class="py-3 m"><i class="fa-solid fa-filter me-2"></i>Opciones</h4>
    </div>

    <div class="slider-opcion <?php echo ($current_page == 'chatbot.php') ? 'active' : ''; ?> mb-3">
        <a href="../chatBoot/chatbot.php">
            <i class="fas fa-comment me-2"></i> ChatBot
        </a>
    </div>

    <?php if (isset($_SESSION['usuario'])): ?>
        <div class="slider-opcion <?php echo ($current_page == 'Usuario_historial.php') ? 'active' : ''; ?> mb-3">
            <a href="../Usuario/Usuario_historial.php">
                <i class="fas fa-history me-2"></i> Ver Historial
            </a>
        </div>
    <?php endif; ?>

    <div class="slider-secion">
        <h2 class="mb-3">Ajustes Rápidos</h2>
    </div>

    <!-- Modo oscuro con funcionalidad -->
    <div class="slider-opcion mb-3 theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon me-2"></i> Modo Oscuro
        <span class="theme-status float-end">
            <?php echo ($current_theme == 'dark') ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>'; ?>
        </span>
    </div>

    <div class="slider-opcion mb-3">
        <i class="fas fa-language me-2"></i> Cambio de Idioma
    </div>

    <?php if (isset($_SESSION['usuario'])): ?>
        <div class="slider-opcion mb-3">
            <a href="#" id="btnCerrarSesion" class="logout-link">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </div>
    <?php else: ?>
        <div class="slider-opcion mb-3">
            <a href="../Usuario/login.php" class="login-link">
                <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['usuario'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('btnCerrarSesion').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: 'Estás a punto de salir del sistema',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../bd/cerrar_sesion.php';
                }
            });
        });
    </script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navegación activa
    const currentUrl = window.location.pathname.split('/').pop();
    const sliderOptions = document.querySelectorAll('.slider-opcion');
    
    sliderOptions.forEach(option => {
        option.classList.remove('active');
        const link = option.querySelector('a');
        if (link) {
            const linkUrl = link.getAttribute('href').split('/').pop();
            if (linkUrl === currentUrl) {
                option.classList.add('active');
            }
        }
    });
});

// Función para cambiar el tema
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Cambiar el tema visualmente
    document.documentElement.setAttribute('data-theme', newTheme);
    
    // Actualizar el icono del toggle
    const themeIcon = document.querySelector('.theme-status');
    themeIcon.innerHTML = newTheme === 'dark' ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>';
    
    // Guardar la preferencia en el servidor
    fetch(window.location.pathname + `?theme=${newTheme}`)
        .then(() => {
            // Recargar para aplicar cambios persistentes
            // window.location.reload(); // Opcional: descomentar si prefieres recargar
        });
}
</script>
</body>
</html>