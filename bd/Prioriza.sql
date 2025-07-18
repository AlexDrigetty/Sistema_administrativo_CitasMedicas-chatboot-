use prioriza;

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

-- Tabla de enfermedades
CREATE TABLE enfermedades (
    enfermedad_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    especialidad_requerida VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de síntomas
CREATE TABLE sintomas (
    sintoma_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    parte_cuerpo VARCHAR(50) NOT NULL,
    prioridad INT NOT NULL COMMENT '5 (alta) o 3 (media)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relación enfermedad-síntoma
CREATE TABLE enfermedad_sintoma (
    enfermedad_id INT,
    sintoma_id INT,
    PRIMARY KEY (enfermedad_id, sintoma_id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(enfermedad_id) ON DELETE CASCADE,
    FOREIGN KEY (sintoma_id) REFERENCES sintomas(sintoma_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de medicamentos
CREATE TABLE medicamentos (
    medicamento_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50),
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relación enfermedad-medicamento
CREATE TABLE enfermedad_medicamento (
    enfermedad_id INT,
    medicamento_id INT,
    PRIMARY KEY (enfermedad_id, medicamento_id),
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(enfermedad_id) ON DELETE CASCADE,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(medicamento_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de interacciones con el chatbot (historial)
CREATE TABLE interacciones_chatbot (
    interaccion_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    descripcion_inicial TEXT NOT NULL,
    fecha_interaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de diagnósticos
CREATE TABLE diagnosticos (
    diagnostico_id INT AUTO_INCREMENT PRIMARY KEY,
    interaccion_id INT NOT NULL,
    puntaje_total INT NOT NULL,
    nivel_prioridad ENUM('moderado', 'rapido', 'maxima') NOT NULL,
    notas TEXT COMMENT 'Visible solo para doctores',
    FOREIGN KEY (interaccion_id) REFERENCES interacciones_chatbot(interaccion_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de síntomas reportados en diagnóstico
CREATE TABLE diagnostico_sintomas (
    diagnostico_id INT,
    sintoma_id INT,
    intensidad ENUM('leve', 'moderado', 'intenso') NOT NULL,
    tiempo_presentacion ENUM('ahora', '1_dia', '3_dias', '1_semana') NOT NULL,
    puntaje_individual INT NOT NULL,
    PRIMARY KEY (diagnostico_id, sintoma_id),
    FOREIGN KEY (diagnostico_id) REFERENCES diagnosticos(diagnostico_id) ON DELETE CASCADE,
    FOREIGN KEY (sintoma_id) REFERENCES sintomas(sintoma_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de enfermedades sugeridas en diagnóstico
CREATE TABLE diagnostico_enfermedades (
    diagnostico_id INT,
    enfermedad_id INT,
    probabilidad INT NOT NULL COMMENT 'Porcentaje de coincidencia',
    PRIMARY KEY (diagnostico_id, enfermedad_id),
    FOREIGN KEY (diagnostico_id) REFERENCES diagnosticos(diagnostico_id) ON DELETE CASCADE,
    FOREIGN KEY (enfermedad_id) REFERENCES enfermedades(enfermedad_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de citas médicas
CREATE TABLE citas_medicas (
    cita_id INT AUTO_INCREMENT PRIMARY KEY,
    interaccion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    doctor_id INT NOT NULL,
    ticket_code VARCHAR(20) NOT NULL,
    fecha_hora DATETIME NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (interaccion_id) REFERENCES interacciones_chatbot(interaccion_id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctores(usuario_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de medicamentos recomendados
CREATE TABLE diagnostico_medicamentos (
    diagnostico_id INT,
    medicamento_id INT,
    instrucciones TEXT,
    PRIMARY KEY (diagnostico_id, medicamento_id),
    FOREIGN KEY (diagnostico_id) REFERENCES diagnosticos(diagnostico_id) ON DELETE CASCADE,
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(medicamento_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pacientes (dni, nombre, apellido, direccion, telefono, email) VALUES
('12345678', 'Juan', 'Pérez', 'Av. Libertad 123', '987654321', 'juan.perez@email.com'),
('23456789', 'María', 'Gómez', 'Calle Primavera 45', '987123456', 'maria.gomez@email.com'),
('34567890', 'Carlos', 'López', 'Jr. San Martín 678', '987456123', 'carlos.lopez@email.com'),
('45678901', 'Ana', 'Rodríguez', 'Av. Los Olivos 345', '987789456', 'ana.rodriguez@email.com'),
('56789012', 'Luis', 'Martínez', 'Calle Las Flores 89', '987321654', 'luis.martinez@email.com');
