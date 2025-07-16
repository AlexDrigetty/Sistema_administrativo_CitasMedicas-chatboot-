-- Tabla de Roles (nueva)
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar roles básicos
INSERT INTO roles (nombre, descripcion) VALUES 
('administrador', 'Acceso completo al sistema'),
('doctor', 'Personal médico que atiende pacientes'),
('paciente', 'Usuarios que reciben atención médica');

-- Tabla de Usuarios (actualizada para autenticación centralizada)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    direccion VARCHAR(100),
    fecha_nacimiento DATE,
    telefono VARCHAR(15),
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Administradores (simplificada, relacionada con usuarios)
CREATE TABLE IF NOT EXISTS administradores (
    usuario_id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    telefono VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Doctores (actualizada para usar sistema de usuarios)
CREATE TABLE IF NOT EXISTS doctores (
    usuario_id INT PRIMARY KEY,
    dni VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    nro_colegiatura VARCHAR(50) UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla Temporal de Pacientes (para simulación API - se eliminará después)
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(8) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    direccion VARCHAR(100),
    telefono VARCHAR(15),
    email VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Perfiles de Pacientes (para cuando elimines la tabla temporal)
CREATE TABLE IF NOT EXISTS perfiles_pacientes (
    usuario_id INT PRIMARY KEY,
    dni VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    direccion VARCHAR(100),
    fecha_nacimiento DATE,
    telefono VARCHAR(15),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Enfermedades (mantenida igual)
CREATE TABLE IF NOT EXISTS enfermedades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Síntomas (mantenida igual)
CREATE TABLE IF NOT EXISTS sintomas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    intensidad_tipica VARCHAR(20),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Medicamentos (mantenida igual)
CREATE TABLE IF NOT EXISTS medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50),
    descripcion TEXT,
    contraindicaciones TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Relación Enfermedad-Síntoma (mantenida igual)
CREATE TABLE IF NOT EXISTS enfermedad_sintoma (
    enfermedad_id INT,
    sintoma_id INT,
    frecuencia VARCHAR(20),
    PRIMARY KEY (enfermedad_id, sintoma_id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(id) ON DELETE CASCADE,
    FOREIGN KEY (sintoma_id) REFERENCES sintomas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Relación Enfermedad-Medicamento (mantenida igual)
CREATE TABLE IF NOT EXISTS enfermedad_medicamento (
    enfermedad_id INT,
    medicamento_id INT,
    efectividad VARCHAR(20),
    notas TEXT,
    PRIMARY KEY (enfermedad_id, medicamento_id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(id) ON DELETE CASCADE,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Citas Médicas (actualizada para usar usuarios)
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT COMMENT 'Paciente (referencia a usuarios)',
    doctor_id INT COMMENT 'Doctor (referencia a doctores.usuario_id)',
    fecha_hora DATETIME NOT NULL,
    motivo TEXT,
    estado ENUM('pendiente', 'completada', 'cancelada') DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctores(usuario_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Historial Médico (actualizada para usar usuarios)
CREATE TABLE IF NOT EXISTS historial_medico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT COMMENT 'Paciente',
    doctor_id INT COMMENT 'Doctor que atendió',
    fecha_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    diagnostico TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctores(usuario_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Diagnósticos (mantenida igual)
CREATE TABLE IF NOT EXISTS diagnosticos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    historial_id INT,
    enfermedad_id INT,
    observaciones TEXT,
    tratamiento TEXT,
    FOREIGN KEY (historial_id) REFERENCES historial_medico(id) ON DELETE CASCADE,
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Recetas Médicas (actualizada para usar usuarios)
CREATE TABLE IF NOT EXISTS recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    historial_id INT,
    doctor_id INT COMMENT 'Doctor que receta',
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    instrucciones TEXT,
    FOREIGN KEY (historial_id) REFERENCES historial_medico(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctores(usuario_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Medicamentos Recetados (mantenida igual)
CREATE TABLE IF NOT EXISTS receta_medicamentos (
    receta_id INT,
    medicamento_id INT,
    dosis VARCHAR(100),
    frecuencia VARCHAR(100),
    duracion VARCHAR(100),
    PRIMARY KEY (receta_id, medicamento_id),
    FOREIGN KEY (receta_id) REFERENCES recetas(id) ON DELETE CASCADE,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pacientes (dni, nombre, apellido, direccion, telefono, email) VALUES
('12345678', 'Juan', 'Pérez', 'Av. Libertad 123', '987654321', 'juan.perez@email.com'),
('23456789', 'María', 'Gómez', 'Calle Primavera 45', '987123456', 'maria.gomez@email.com'),
('34567890', 'Carlos', 'López', 'Jr. San Martín 678', '987456123', 'carlos.lopez@email.com'),
('45678901', 'Ana', 'Rodríguez', 'Av. Los Olivos 345', '987789456', 'ana.rodriguez@email.com'),
('56789012', 'Luis', 'Martínez', 'Calle Las Flores 89', '987321654', 'luis.martinez@email.com');