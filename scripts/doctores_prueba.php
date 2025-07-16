<?php
function encriptarContrasena($contrasenaPlana) {
    return password_hash($contrasenaPlana, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Conexión a la base de datos
$db = new PDO('mysql:host=localhost;dbname=prioriza', 'root', 'master.');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Datos de los doctores
$doctores = [
    [
        'dni' => '12345678A',
        'nombre' => 'Juan',
        'apellido' => 'Pérez García',
        'email' => 'j.perez@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Calle Cardiología 123',
        'telefono' => '600123456',
        'especialidad' => 'Cardiología',
        'fecha_ingreso' => '2020-03-15'
    ],
    [
        'dni' => '23456789B',
        'nombre' => 'María',
        'apellido' => 'López Sánchez',
        'email' => 'm.lopez@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Avenida Pediatría 456',
        'telefono' => '600234567',
        'especialidad' => 'Pediatría',
        'fecha_ingreso' => '2019-05-20'
    ],
    [
        'dni' => '34567890C',
        'nombre' => 'Carlos',
        'apellido' => 'Martínez Ruiz',
        'email' => 'c.martinez@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Bulevar Neurología 789',
        'telefono' => '600345678',
        'especialidad' => 'Neurología',
        'fecha_ingreso' => '2021-01-10'
    ],
    [
        'dni' => '45678901D',
        'nombre' => 'Ana',
        'apellido' => 'Rodríguez Fernández',
        'email' => 'a.rodriguez@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Plaza Dermatología 101',
        'telefono' => '600456789',
        'especialidad' => 'Dermatología',
        'fecha_ingreso' => '2018-11-05'
    ],
    [
        'dni' => '56789012E',
        'nombre' => 'Luis',
        'apellido' => 'Gómez Álvarez',
        'email' => 'l.gomez@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Paseo Traumatología 112',
        'telefono' => '600567890',
        'especialidad' => 'Traumatología',
        'fecha_ingreso' => '2022-02-28'
    ],
    [
        'dni' => '67890123F',
        'nombre' => 'Elena',
        'apellido' => 'Díaz Castro',
        'email' => 'e.diaz@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Camino Oftalmología 131',
        'telefono' => '600678901',
        'especialidad' => 'Oftalmología',
        'fecha_ingreso' => '2020-07-15'
    ],
    [
        'dni' => '78901234G',
        'nombre' => 'Pedro',
        'apellido' => 'Sanz Navarro',
        'email' => 'p.sanz@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Ronda Oncología 415',
        'telefono' => '600789012',
        'especialidad' => 'Oncología',
        'fecha_ingreso' => '2019-09-30'
    ],
    [
        'dni' => '89012345H',
        'nombre' => 'Sofía',
        'apellido' => 'Romero Jiménez',
        'email' => 's.romero@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Vía Ginecología 161',
        'telefono' => '600890123',
        'especialidad' => 'Ginecología',
        'fecha_ingreso' => '2021-04-22'
    ],
    [
        'dni' => '90123456I',
        'nombre' => 'Javier',
        'apellido' => 'Torres Molina',
        'email' => 'j.torres@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Sendero Psiquiatría 718',
        'telefono' => '600901234',
        'especialidad' => 'Psiquiatría',
        'fecha_ingreso' => '2018-12-10'
    ],
    [
        'dni' => '01234567J',
        'nombre' => 'Laura',
        'apellido' => 'Ortega Gil',
        'email' => 'l.ortega@clinica.com',
        'contraseña' => 'doctor123',
        'direccion' => 'Callejón Endocrinología 192',
        'telefono' => '600012345',
        'especialidad' => 'Endocrinología',
        'fecha_ingreso' => '2022-06-05'
    ]
];

try {
    $db->beginTransaction();
    
    foreach ($doctores as $doctor) {
        // 1. Insertar en la tabla usuarios
        $stmtUsuario = $db->prepare("INSERT INTO usuarios 
            (dni, nombre, apellido, direccion, telefono, email, contraseña, rol_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, (SELECT id FROM roles WHERE nombre = 'doctor'))");
        
        $stmtUsuario->execute([
            $doctor['dni'],
            $doctor['nombre'],
            $doctor['apellido'],
            $doctor['direccion'],
            $doctor['telefono'],
            $doctor['email'],
            encriptarContrasena($doctor['contraseña'])
        ]);
        
        // Obtener el ID del usuario recién insertado
        $usuario_id = $db->lastInsertId();
        
        // 2. Insertar en la tabla doctores
        $stmtDoctor = $db->prepare("INSERT INTO doctores 
            (usuario_id, dni, nombre, apellidos, telefono, especialidad, fecha_ingreso, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmtDoctor->execute([
            $usuario_id,
            $doctor['dni'],
            $doctor['nombre'],
            $doctor['apellido'], 
            $doctor['telefono'],
            $doctor['especialidad'],
            $doctor['fecha_ingreso'],
            true
        ]);
    }
    
    $db->commit();
    echo "Se insertaron correctamente 10 doctores de ejemplo.";
    
} catch (PDOException $e) {
    $db->rollBack();
    echo "Error al insertar doctores: " . $e->getMessage();
}
?>