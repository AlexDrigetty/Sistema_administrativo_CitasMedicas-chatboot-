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
-- Tabla de Síntomas (mantenida igual)

INSERT INTO pacientes (dni, nombre, apellido, direccion, telefono, email) VALUES
('12345678', 'Juan', 'Pérez', 'Av. Libertad 123', '987654321', 'juan.perez@email.com'),
('23456789', 'María', 'Gómez', 'Calle Primavera 45', '987123456', 'maria.gomez@email.com'),
('34567890', 'Carlos', 'López', 'Jr. San Martín 678', '987456123', 'carlos.lopez@email.com'),
('45678901', 'Ana', 'Rodríguez', 'Av. Los Olivos 345', '987789456', 'ana.rodriguez@email.com'),
('56789012', 'Luis', 'Martínez', 'Calle Las Flores 89', '987321654', 'luis.martinez@email.com');


-- Insertar síntomas comunes con prioridades
INSERT INTO sintomas (nombre, descripcion, parte_cuerpo, prioridad) VALUES
-- Síntomas de cabeza (prioridad alta - 5 puntos)
('Dolor de cabeza', 'Dolor en la región craneal', 'cabeza', 5),
('Mareos', 'Sensación de vértigo o desequilibrio', 'cabeza', 5),
('Visión borrosa', 'Dificultad para enfocar la vista', 'ojos', 5),
('Dolor ocular', 'Molestia en uno o ambos ojos', 'ojos', 5),
('Zumbido en oídos', 'Percepción de sonido sin fuente externa', 'oídos', 5),

-- Síntomas respiratorios (prioridad alta - 5 puntos)
('Dificultad para respirar', 'Falta de aire o disnea', 'pecho', 5),
('Tos persistente', 'Tos que dura más de una semana', 'garganta', 5),
('Tos con sangre', 'Expectoración con sangre', 'pecho', 5),
('Silbido al respirar', 'Sonido agudo al inhalar o exhalar', 'pecho', 5),
('Dolor en el pecho', 'Molestia en la zona torácica', 'pecho', 5),

-- Síntomas abdominales (prioridad variable)
('Dolor abdominal', 'Molestia en la zona del vientre', 'abdomen', 3),
('Náuseas', 'Sensación de querer vomitar', 'abdomen', 3),
('Vómitos', 'Expulsión del contenido gástrico', 'abdomen', 3),
('Diarrea', 'Evacuaciones líquidas frecuentes', 'abdomen', 3),
('Estreñimiento', 'Dificultad para defecar', 'abdomen', 3),

-- Síntomas urinarios (prioridad alta - 5 puntos)
('Dolor al orinar', 'Ardor o molestia al miccionar', 'genitales', 5),
('Orina turbia', 'Orina con apariencia lechosa', 'genitales', 5),
('Sangre en orina', 'Presencia de sangre al orinar', 'genitales', 5),
('Micción frecuente', 'Necesidad de orinar muchas veces', 'genitales', 5),
('Incontinencia', 'Pérdida del control de la vejiga', 'genitales', 5),

-- Síntomas musculares (prioridad media - 3 puntos)
('Dolor muscular', 'Molestia en los músculos', 'extremidades', 3),
('Calambres', 'Contracciones musculares involuntarias', 'extremidades', 3),
('Hinchazón articular', 'Aumento de tamaño en articulaciones', 'articulaciones', 3),
('Rigidez matutina', 'Dificultad para moverse al despertar', 'articulaciones', 3),
('Debilidad muscular', 'Falta de fuerza en los músculos', 'extremidades', 3),

-- Síntomas dermatológicos (prioridad variable)
('Erupción cutánea', 'Aparición de lesiones en la piel', 'piel', 5),
('Picazón intensa', 'Comezón que provoca rascado', 'piel', 5),
('Urticaria', 'Ronchas rojas que pican', 'piel', 5),
('Hinchazón facial', 'Aumento de volumen en la cara', 'cabeza', 5),
('Caída de cabello', 'Pérdida anormal de pelo', 'cuero cabelludo', 3),

-- Síntomas generales (prioridad variable)
('Fiebre', 'Aumento de la temperatura corporal', 'general', 5),
('Escalofríos', 'Sensación de frío con temblores', 'general', 3),
('Sudoración nocturna', 'Sudor excesivo durante la noche', 'general', 3),
('Fatiga crónica', 'Cansancio extremo persistente', 'general', 3),
('Pérdida de peso', 'Reducción de peso no intencional', 'general', 5),

-- Síntomas neurológicos (prioridad alta - 5 puntos)
('Convulsiones', 'Movimientos involuntarios bruscos', 'cabeza', 5),
('Pérdida de conciencia', 'Desmayo o sincope', 'cabeza', 5),
('Hormigueo en extremidades', 'Sensación de pinchazos', 'extremidades', 3),
('Pérdida de sensibilidad', 'Falta de tacto en zonas del cuerpo', 'piel', 5),
('Dificultad para hablar', 'Problemas en el habla', 'cabeza', 5),

-- Síntomas psicológicos (prioridad variable)
('Ansiedad', 'Nerviosismo o preocupación excesiva', 'general', 3),
('Depresión', 'Tristeza profunda persistente', 'general', 3),
('Insomnio', 'Dificultad para conciliar el sueño', 'general', 3),
('Cambios de humor', 'Altibajos emocionales bruscos', 'general', 3),
('Pensamientos suicidas', 'Ideas de autolesión', 'general', 5),

-- Síntomas cardiovasculares (prioridad alta - 5 puntos)
('Palpitaciones', 'Sensación de latidos fuertes', 'pecho', 5),
('Mareo al levantarse', 'Vértigo postural', 'cabeza', 3),
('Hinchazón de piernas', 'Edema en extremidades inferiores', 'piernas', 3),
('Venas varicosas', 'Venas dilatadas y visibles', 'piernas', 3),
('Dolor en las piernas', 'Molestia al caminar o en reposo', 'piernas', 3); 

-- Insertar enfermedades comunes
INSERT INTO enfermedades (nombre, descripcion, categoria, especialidad_requerida) VALUES
('Migraña', 'Dolor de cabeza intenso recurrente', 'Neurológica', 'Neurología'),
('Hipertensión arterial', 'Presión arterial elevada persistentemente', 'Cardiovascular', 'Cardiología'),
('Diabetes mellitus', 'Alteración en el metabolismo de la glucosa', 'Endocrinológica', 'Endocrinología'),
('Asma', 'Enfermedad inflamatoria de las vías respiratorias', 'Respiratoria', 'Neumología'),
('Gastritis', 'Inflamación de la mucosa gástrica', 'Digestiva', 'Gastroenterología'),
('Infección urinaria', 'Infección en el tracto urinario', 'Urológica', 'Urología'),
('Artritis reumatoide', 'Enfermedad autoinmune que afecta articulaciones', 'Reumatológica', 'Reumatología'),
('Depresión mayor', 'Trastorno del estado de ánimo', 'Psiquiátrica', 'Psiquiatría'),
('Dermatitis atópica', 'Inflamación crónica de la piel', 'Dermatológica', 'Dermatología'),
('Bronquitis aguda', 'Inflamación de los bronquios', 'Respiratoria', 'Neumología'),
('Anemia ferropénica', 'Deficiencia de hierro en la sangre', 'Hematológica', 'Hematología'),
('Hipotensión ortostática', 'Caída de presión al cambiar de posición', 'Cardiovascular', 'Cardiología'),
('Conjuntivitis', 'Inflamación de la conjuntiva ocular', 'Oftalmológica', 'Oftalmología'),
('Osteoartritis', 'Degeneración del cartílago articular', 'Reumatológica', 'Traumatología'),
('Sinusitis', 'Inflamación de los senos paranasales', 'Otorrinolaringológica', 'Otorrinolaringología'),
('Gota', 'Artritis por cristales de urato', 'Reumatológica', 'Reumatología'),
('Ansiedad generalizada', 'Trastorno de ansiedad persistente', 'Psiquiátrica', 'Psiquiatría'),
('Hepatitis viral', 'Inflamación del hígado por virus', 'Hepatológica', 'Gastroenterología'),
('Neumonía', 'Infección del tejido pulmonar', 'Respiratoria', 'Neumología'),
('Varices esofágicas', 'Dilatación de venas en el esófago', 'Digestiva', 'Gastroenterología');

-- Establecer relaciones entre enfermedades y síntomas
INSERT INTO enfermedad_sintoma (enfermedad_id, sintoma_id) VALUES
-- Migraña
(1, 1), (1, 2), (1, 3), (1, 4), (1, 31),
-- Hipertensión
(2, 1), (2, 2), (2, 10), (2, 41), (2, 42),
-- Diabetes
(3, 12), (3, 13), (3, 14), (3, 30), (3, 31),
-- Asma
(4, 6), (4, 7), (4, 9), (4, 10), (4, 36),
-- Gastritis
(5, 11), (5, 12), (5, 13), (5, 14), (5, 15),
-- Infección urinaria
(6, 16), (6, 17), (6, 18), (6, 19), (6, 20),
-- Artritis reumatoide
(7, 21), (7, 22), (7, 23), (7, 24), (7, 25),
-- Depresión
(8, 36), (8, 37), (8, 38), (8, 39), (8, 40),
-- Dermatitis
(9, 26), (9, 27), (9, 28), (9, 29), (9, 30),
-- Bronquitis
(10, 6), (10, 7), (10, 8), (10, 9), (10, 31),
-- Anemia
(11, 2), (11, 25), (11, 31), (11, 32), (11, 42),
-- Hipotensión
(12, 2), (12, 10), (12, 42), (12, 43), (12, 44),
-- Conjuntivitis
(13, 3), (13, 4), (13, 26), (13, 27), (13, 29),
-- Osteoartritis
(14, 21), (14, 22), (14, 23), (14, 24), (14, 25),
-- Sinusitis
(15, 1), (15, 3), (15, 4), (15, 7), (15, 31),
-- Gota
(16, 21), (16, 22), (16, 23), (16, 24), (16, 31),
-- Ansiedad
(17, 2), (17, 10), (17, 36), (17, 37), (17, 38),
-- Hepatitis
(18, 11), (18, 12), (18, 13), (18, 14), (18, 30),
-- Neumonía
(19, 6), (19, 7), (19, 8), (19, 10), (19, 31),
-- Varices esofágicas
(20, 8), (20, 12), (20, 13), (20, 14), (20, 30);

-- Insertar medicamentos comunes
INSERT INTO medicamentos (nombre, tipo, descripcion) VALUES
('Paracetamol', 'Analgésico', 'Alivia el dolor y reduce la fiebre'),
('Ibuprofeno', 'Antiinflamatorio', 'Reduce dolor, inflamación y fiebre'),
('Omeprazol', 'Inhibidor de bomba de protones', 'Reduce la producción de ácido estomacal'),
('Loratadina', 'Antihistamínico', 'Alivia síntomas de alergia'),
('Salbutamol', 'Broncodilatador', 'Alivia el broncoespasmo en asma'),
('Metformina', 'Hipoglucemiante', 'Controla los niveles de azúcar en sangre'),
('Sertralina', 'Antidepresivo', 'Trata depresión y trastornos de ansiedad'),
('Atorvastatina', 'Hipolipemiante', 'Reduce el colesterol en sangre'),
('Amoxicilina', 'Antibiótico', 'Trata infecciones bacterianas'),
('Diazepam', 'Ansiolítico', 'Alivia la ansiedad y relaja los músculos'),
('Losartán', 'Antihipertensivo', 'Controla la presión arterial alta'),
('Insulina glargina', 'Hipoglucemiante', 'Control de diabetes tipo 1 y 2'),
('Prednisona', 'Corticoide', 'Reduce la inflamación y suprime el sistema inmune'),
('Warfarina', 'Anticoagulante', 'Previene la formación de coágulos'),
('Levotiroxina', 'Hormona tiroidea', 'Trata el hipotiroidismo'),
('Metoclopramida', 'Antiemético', 'Controla náuseas y vómitos'),
('Dipirona', 'Analgésico', 'Alivia el dolor moderado y reduce fiebre'),
('Dexametasona', 'Corticoide', 'Antiinflamatorio potente'),
('Fluoxetina', 'Antidepresivo', 'Inhibidor selectivo de la recaptación de serotonina'),
('Clonazepam', 'Anticonvulsivo', 'Trata convulsiones y trastornos de ansiedad'),
('Ranitidina', 'Antihistamínico H2', 'Reduce la producción de ácido estomacal'),
('Hidroclorotiazida', 'Diurético', 'Trata la hipertensión y edema'),
('Montelukast', 'Antileucotrieno', 'Previene síntomas de asma'),
('Tramadol', 'Analgésico opioide', 'Alivia el dolor moderado a severo'),
('Ciprofloxacino', 'Antibiótico', 'Trata infecciones bacterianas'),
('Diclofenaco', 'Antiinflamatorio', 'Alivia dolor e inflamación'),
('Esomeprazol', 'Inhibidor de bomba de protones', 'Trata reflujo gastroesofágico'),
('Amlodipino', 'Bloqueador de canales de calcio', 'Trata hipertensión y angina'),
('Cetirizina', 'Antihistamínico', 'Alivia síntomas de alergia'),
('Metronidazol', 'Antibiótico', 'Trata infecciones por bacterias y parásitos');

-- Establecer relaciones entre enfermedades y medicamentos
INSERT INTO enfermedad_medicamento (enfermedad_id, medicamento_id) VALUES
-- Migraña
(1, 1), (1, 2), (1, 20),
-- Hipertensión
(2, 11), (2, 22), (2, 28),
-- Diabetes
(3, 6), (3, 12), (3, 15),
-- Asma
(4, 5), (4, 23), (4, 18),
-- Gastritis
(5, 3), (5, 21), (5, 27),
-- Infección urinaria
(6, 9), (6, 25), (6, 30),
-- Artritis reumatoide
(7, 2), (7, 13), (7, 18),
-- Depresión
(8, 7), (8, 19), (8, 20),
-- Dermatitis
(9, 4), (9, 13), (9, 29),
-- Bronquitis
(10, 5), (10, 9), (10, 18),
-- Anemia
(11, 15), (11, 16), (11, 17),
-- Hipotensión
(12, 11), (12, 22), (12, 28),
-- Conjuntivitis
(13, 4), (13, 9), (13, 29),
-- Osteoartritis
(14, 2), (14, 17), (14, 26),
-- Sinusitis
(15, 1), (15, 9), (15, 13),
-- Gota
(16, 2), (16, 13), (16, 26),
-- Ansiedad
(17, 10), (17, 20), (17, 7),
-- Hepatitis
(18, 3), (18, 15), (18, 30),
-- Neumonía
(19, 9), (19, 13), (19, 25),
-- Varices esofágicas
(20, 3), (20, 14), (20, 27);