<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="slider">
    <div class="slider-cabeza">
        <h4 class="py-3 m"><i class="fa-solid fa-filter me-2"></i>Opciones</h4>
    </div>

    <div class="slider-opcion active mb-3">
        <a href="../chatBoot/chatbot.php">
            <i class="fas fa-comment me-2"></i> ChatBot
        </a>
    </div>

    <?php if (isset($_SESSION['usuario'])): ?>
        <!-- Opciones solo para usuarios logueados -->
        <div class="slider-opcion  mb-3">
            <a href="../Usuario/Usuario_historial.php">
                <i class="fas fa-history me-2"></i> Ver Historial
            </a>
        </div>
    <?php endif; ?>

    <div class="slider-secion">
        <h2 class="mb-3">Ajustes Rápidos</h2>
    </div>

    <!-- Opciones generales -->
    <div class="slider-opcion mb-3">
        <i class="fas fa-moon me-2"></i> Modo Oscuro
    </div>

    <div class="slider-opcion mb-3">
        <i class="fas fa-language me-2"></i> Cambio de Idioma
    </div>

    <!-- Gestión de sesión -->
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