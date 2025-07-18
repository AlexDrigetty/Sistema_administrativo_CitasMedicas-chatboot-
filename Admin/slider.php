<?php 
require_once 'Auth.php'; 
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Panel de Administración</h3>
    </div>
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="Admin_Panel.php" class="active" id="dashboard-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="Admin_doctores.php" id="doctors-link">
                    <i class="fas fa-user-md"></i> Ver Doctores
                </a>
            </li>
            <li>
                <a href="Admin_crearDoctor.php" id="add-doctor-link">
                    <i class="fas fa-plus-circle"></i> Crear Doctor
                </a>
            </li>
            <li>
                <a href="Admin_citas.php" id="consultations-link">
                    <i class="fas fa-calendar-check"></i> Citas
                </a>
            </li>
            <li>
                <a href="../bd/cerrar_sesion_admin.php" id="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Obtener la URL actual
    const currentUrl = window.location.pathname.split('/').pop();
    
    // Seleccionar todos los enlaces del sidebar
    const links = document.querySelectorAll('.sidebar-menu a');
    
    // Remover la clase 'active' de todos los enlaces primero
    links.forEach(link => {
        link.classList.remove('active');
        
        // Obtener el href del enlace y comparar con la URL actual
        const linkUrl = link.getAttribute('href').split('/').pop();
        if (linkUrl === currentUrl) {
            link.classList.add('active');
        }
    });
});
</script>