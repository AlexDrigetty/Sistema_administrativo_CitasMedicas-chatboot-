<?php
require_once '../funciones/chatboot_funciones.php';

// Especialidades a verificar
$especialidades = [
    'Cardiología',
    'Pediatría',
    'Neurología',
    'Dermatología',
    'Traumatología',
    'Oftalmología',
    'Oncología',
    'Ginecología',
    'Psiquiatría',
    'Endocrinología',
    'Medicina General'
];

foreach ($especialidades as $especialidad) {
    $doctores = obtenerDoctoresPorEspecialidad($especialidad);
    
    echo "<h3>Doctores en $especialidad:</h3>";
    if (empty($doctores)) {
        echo "<p>No se encontraron doctores</p>";
    } else {
        echo "<ul>";
        foreach ($doctores as $doctor) {
            echo "<li>{$doctor['nombre']} {$doctor['apellido']} - {$doctor['especialidad']}</li>";
        }
        echo "</ul>";
    }
}
?>