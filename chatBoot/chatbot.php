<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'read_and_close'  => false,
    ]);
}

require_once '../funciones/chatboot_funciones.php';

// Verificar si debemos mostrar la alerta de bienvenida
$mostrarAlerta = isset($_SESSION['usuario']) && ($_SESSION['usuario']['mostrar_alerta'] ?? false);

if ($mostrarAlerta) {
    $_SESSION['usuario']['mostrar_alerta'] = false;
}

// Inicializar variables para modales
$mostrarModalSintomas = false;
$mostrarModalCita = false;

// Inicializar historial de chat si no existe
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Inicializar interacciones si no existen
if (!isset($_SESSION['interactions'])) {
    $_SESSION['interactions'] = [];
}

// Obtener ID de interacción actual o crear nueva
$currentInteractionId = $_POST['current_interaction_id'] ?? uniqid();

// Inicializar o recuperar interacción actual
if (!isset($_SESSION['interactions'][$currentInteractionId])) {
    $_SESSION['interactions'][$currentInteractionId] = [
        'id' => $currentInteractionId,
        'state' => 'initial',
        'symptoms_detected' => false,
        'form_data' => null,
        'diagnosis_data' => null,
        'appointment_data' => null,
        'created_at' => time(),
        'show_diagnosis' => true,
        'sintomas_detectar' => [],
        'descripcion_sintomas' => ''
    ];
}

$currentInteraction = &$_SESSION['interactions'][$currentInteractionId];

// Procesar formulario de síntomas si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sintomas_detallados']) && isset($_POST['tiempo_general']) && isset($_POST['current_interaction_id'])) {
        // Validar que al menos un síntoma tenga intensidad seleccionada
        $hasSymptoms = false;
        foreach ($_POST['sintomas_detallados'] as $sintoma) {
            if (!empty($sintoma['intensidad'])) {
                $hasSymptoms = true;
                break;
            }
        }

        if (!$hasSymptoms) {
            $_SESSION['error'] = "Debes indicar la intensidad de al menos un síntoma";
            $mostrarModalSintomas = true;
        } else {
            // Procesar el formulario de síntomas detallados
            $sintomasDetallados = [];
            $sintomasIds = [];

            foreach ($_POST['sintomas_detallados'] as $sintomaId => $detalles) {
                if (!empty($detalles['intensidad'])) {
                    // Calcular puntaje individual
                    $puntaje = 0;

                    // Puntaje por tiempo
                    $tiempo = $_POST['tiempo_general'];
                    switch ($tiempo) {
                        case 'ahora':
                            $puntaje += 5;
                            break;
                        case '1_dia':
                            $puntaje += 4;
                            break;
                        case '3_dias':
                            $puntaje += 3;
                            break;
                        case '1_semana':
                            $puntaje += 2;
                            break;
                    }

                    // Puntaje por intensidad
                    switch ($detalles['intensidad']) {
                        case 'leve':
                            $puntaje += 1;
                            break;
                        case 'moderado':
                            $puntaje += 3;
                            break;
                        case 'intenso':
                            $puntaje += 5;
                            break;
                    }

                    $sintomasDetallados[] = [
                        'sintoma_id' => $sintomaId,
                        'intensidad' => $detalles['intensidad'],
                        'tiempo' => $tiempo,
                        'puntaje' => $puntaje
                    ];

                    $sintomasIds[] = $sintomaId;
                }
            }

            // Registrar interacción en la base de datos
            $usuarioId = $_SESSION['usuario']['id'] ?? null;
            $descripcion = $currentInteraction['descripcion_sintomas'] ?? 'Descripción no proporcionada';
            $interaccionId = registrarInteraccion($usuarioId, $descripcion);

            if ($interaccionId) {
                // Obtener posibles enfermedades
                $enfermedades = obtenerEnfermedadesPorSintomas($sintomasIds);
                $enfermedadesIds = array_column($enfermedades, 'enfermedad_id');

                // Obtener medicamentos recomendados
                $medicamentos = [];
                if (!empty($enfermedadesIds)) {
                    $medicamentos = obtenerMedicamentosPorEnfermedades($enfermedadesIds);
                }

                // Calcular prioridad
                $prioridad = calcularPrioridad($sintomasDetallados);

                // Registrar diagnóstico en la base de datos
                $diagnosticoId = registrarDiagnostico($interaccionId, $sintomasDetallados, $enfermedades, $medicamentos, $prioridad);

                // Determinar especialidad
                $especialidad = 'Medicina General'; // Valor por defecto
                if (!empty($enfermedades)) {
                    // Contar coincidencias por especialidad
                    $especialidadesCount = [];
                    foreach ($enfermedades as $enfermedad) {
                        $esp = $enfermedad['especialidad_requerida'];
                        $especialidadesCount[$esp] = ($especialidadesCount[$esp] ?? 0) + 1;
                    }

                    // Ordenar por mayor coincidencia
                    arsort($especialidadesCount);

                    // Obtener la especialidad más frecuente mapeada
                    $especialidadEnfermedad = key($especialidadesCount);
                    $especialidad = mapearEspecialidad($especialidadEnfermedad);
                }

                // Obtener doctores disponibles
                $doctores = obtenerDoctoresPorEspecialidad($especialidad);

                // Guardar en sesión para mostrar en el chat
                $currentInteraction['diagnosis_data'] = [
                    'diagnostico' => array_column($enfermedades, 'nombre'),
                    'medicamentos' => array_column($medicamentos, 'nombre'),
                    'especialidad' => $especialidad,
                    'doctores_disponibles' => $doctores,
                    'interaccion_id' => $interaccionId
                ];

                // Actualizar estado
                $currentInteraction['state'] = 'form_completed';
                $currentInteraction['form_data'] = $sintomasDetallados;
                $currentInteraction['show_diagnosis'] = true;
                
                // Limpiar datos temporales
                unset($currentInteraction['descripcion_sintomas']);
                unset($currentInteraction['sintomas_detectar']);
            }
        }
    } elseif (isset($_POST['confirmar_cita']) && isset($_POST['current_interaction_id'])) {
        // Procesar confirmación de cita
        $currentInteractionId = $_POST['current_interaction_id'];
        if (isset($currentInteraction['diagnosis_data'])) {
            $doctorId = $_POST['doctor'];
            $especialidad = $_POST['especialidad'];
            $usuarioId = $_SESSION['usuario']['id'] ?? null;
            $interaccionId = $currentInteraction['diagnosis_data']['interaccion_id'] ?? null;

            if ($usuarioId && $interaccionId) {
                $cita = registrarCita($interaccionId, $usuarioId, $doctorId, $especialidad);

                if ($cita) {
                    $doctor = obtenerInfoDoctor($doctorId);

                    $currentInteraction['appointment_data'] = [
                        'fecha' => $cita['fecha'],
                        'doctor' => $doctor['nombre'] . ' ' . $doctor['apellido'],
                        'especialidad' => $doctor['especialidad'],
                        'codigo' => $cita['ticket'],
                        'interaccion_id' => $interaccionId
                    ];

                    // Actualizar estado
                    $currentInteraction['state'] = 'appointment_completed';
                    
                    // Mostrar confirmación con toda la información
                    $_SESSION['chat_history'][] = [
                        'tipo' => 'bot',
                        'texto' => '¡Cita reservada con éxito!<br>' .
                                   '<strong>Ticket:</strong> ' . $cita['ticket'] . '<br>' .
                                   '<strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($cita['fecha'])) . '<br>' .
                                   '<strong>Doctor:</strong> ' . $doctor['nombre'] . ' ' . $doctor['apellido'] . '<br>' .
                                   '<strong>Especialidad:</strong> ' . $doctor['especialidad'] . '<br>' .
                                   'Por favor presenta este ticket al llegar a la clínica.',
                        'interaction_id' => $currentInteractionId
                    ];
                    
                    // Mensaje final de la interacción
                    $_SESSION['chat_history'][] = [
                        'tipo' => 'bot',
                        'texto' => 'Gracias por usar PriorizaNow. Si necesitas ayuda con otro problema de salud, por favor descríbemelo.',
                        'interaction_id' => $currentInteractionId
                    ];
                    
                    // Crear nueva interacción para futuras consultas
                    $newInteractionId = uniqid();
                    $_SESSION['interactions'][$newInteractionId] = [
                        'id' => $newInteractionId,
                        'state' => 'initial',
                        'symptoms_detected' => false,
                        'form_data' => null,
                        'diagnosis_data' => null,
                        'appointment_data' => null,
                        'created_at' => time(),
                        'show_diagnosis' => true,
                        'sintomas_detectar' => [],
                        'descripcion_sintomas' => ''
                    ];
                    
                    // Actualizar interacción actual
                    $currentInteractionId = $newInteractionId;
                    $currentInteraction = &$_SESSION['interactions'][$newInteractionId];
                }
            }
        }
    } elseif (isset($_POST['rechazar_cita']) && isset($_POST['current_interaction_id'])) {
        // Procesar rechazo de cita
        $currentInteractionId = $_POST['current_interaction_id'];
        $newInteractionId = $_POST['new_interaction_id'] ?? uniqid();
        
        // Actualizar la interacción actual como completada sin cita
        $currentInteraction['state'] = 'rejected';
        
        // Mensaje de agradecimiento
        $_SESSION['chat_history'][] = [
            'tipo' => 'bot',
            'texto' => 'Gracias por usar PriorizaNow. Si necesitas ayuda con otro problema de salud, por favor descríbemelo.',
            'interaction_id' => $currentInteractionId
        ];
        
        // Crear nueva interacción para futuras consultas
        $_SESSION['interactions'][$newInteractionId] = [
            'id' => $newInteractionId,
            'state' => 'initial',
            'symptoms_detected' => false,
            'form_data' => null,
            'diagnosis_data' => null,
            'appointment_data' => null,
            'created_at' => time(),
            'show_diagnosis' => true,
            'sintomas_detectar' => [],
            'descripcion_sintomas' => ''
        ];
        
        // Actualizar interacción actual
        $currentInteractionId = $newInteractionId;
        $currentInteraction = &$_SESSION['interactions'][$newInteractionId];
    } elseif (isset($_POST['mensaje'])) {
        $mensaje = trim($_POST['mensaje']);

        if (!empty($mensaje)) {
            // Manejar estado actual
            $currentState = $currentInteraction['state'];
            
            // Agregar mensaje al historial
            $_SESSION['chat_history'][] = [
                'tipo' => 'user', 
                'texto' => $mensaje,
                'interaction_id' => $currentInteractionId
            ];

            // Si estamos en estado inicial o diagnosis_shown (nueva interacción)
            if ($currentState === 'initial' || $currentState === 'diagnosis_shown' || $currentState === 'appointment_completed' || $currentState === 'rejected') {
                // Detectar síntomas en el texto
                $sintomasDetectados = detectarSintomasEnTexto($mensaje);

                if (!empty($sintomasDetectados)) {
                    // Guardar en la interacción ACTUAL
                    $currentInteraction['descripcion_sintomas'] = $mensaje;
                    $currentInteraction['sintomas_detectar'] = $sintomasDetectados;
                    $mostrarModalSintomas = true;
                    
                    $_SESSION['chat_history'][] = [
                        'tipo' => 'bot',
                        'texto' => 'He detectado algunos síntomas en tu mensaje. Por favor, detállalos:',
                        'mostrar_formulario' => true,
                        'sintomas' => $sintomasDetectados,
                        'interaction_id' => $currentInteractionId
                    ];
                    
                    // Actualizar estado
                    $currentInteraction['state'] = 'symptoms_detected';
                    $currentInteraction['symptoms_detected'] = true;
                } else {
                    $_SESSION['chat_history'][] = [
                        'tipo' => 'bot',
                        'texto' => 'Por favor, describe tus síntomas con más detalle (por ejemplo: "Tengo dolor de cabeza y fiebre")',
                        'interaction_id' => $currentInteractionId
                    ];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriorizaNow | Chatbot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/chatboot.css">
    <style>
        .modal-sintomas {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content-sintomas {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-sintomas.show .modal-content-sintomas {
            transform: scale(1);
            opacity: 1;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: black;
        }

        .mensaje.bot {
            background-color: #e3f2fd;
            border-radius: 15px;
            padding: 10px 15px;
            margin: 5px 0;
            max-width: 80%;
            align-self: flex-start;
        }

        .mensaje.user {
            background-color: #d1e7dd;
            border-radius: 15px;
            padding: 10px 15px;
            margin: 5px 0;
            max-width: 80%;
            align-self: flex-end;
        }

        .chat-mensajes {
            display: flex;
            flex-direction: column;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            scroll-behavior: smooth;
        }

        .btn-cita {
            margin: 5px;
            padding: 8px 15px;
            border-radius: 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cita:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
        }

        .btn-cita.btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-cita.btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .sintoma-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        .sintoma-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #0d6efd;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .doctor-option {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }

        .doctor-option:hover {
            background-color: #e9ecef;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }

        .btn-send {
            background: none;
            border: none;
            color: #0d6efd;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 10px;
        }

        .disabled-form {
            opacity: 0.6;
            pointer-events: none;
        }

        .new-interaction-marker {
            text-align: center;
            margin: 10px 0;
            color: #6c757d;
            font-size: 0.9em;
            font-style: italic;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            padding: 5px 0;
        }

        .interaction-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .diagnosis-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }

        .btn-group-citas {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <main class="container-fluid">
        <?php include "slider.php" ?>

        <div class="chatboot w-100">
            <div class="chat-header">
                <h4 class="mb-0">PriorizaNow a tu Disposición</h4>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span class="user-greeting">Hola, <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Usuario'); ?></span>
                <?php endif; ?>
            </div>

            <div class="chat-mensajes" id="chat-mensajes">
                <div class="mensaje bot">
                    <p>Hola, soy el ChatBot de PriorizaNow. ¿En qué puedo ayudarte hoy?</p>
                    <p>Puedes describirme tus síntomas y te ayudaré a reservar una cita médica.</p>
                    <?php if (!isset($_SESSION['usuario'])): ?>
                        <p class="login-suggestion">Para guardar tu historial de conversación, <a href="../Usuario/login.php">inicia sesión</a></p>
                    <?php endif; ?>
                </div>

                <!-- Mostrar historial del chat agrupado por interacciones -->
                <?php if (!empty($_SESSION['chat_history'])): ?>
                    <?php 
                    $lastInteractionId = null;
                    foreach ($_SESSION['chat_history'] as $index => $mensaje): 
                        $currentMsgInteractionId = $mensaje['interaction_id'] ?? null;
                        
                        // Mostrar marcador de nueva interacción si cambia
                        if ($lastInteractionId !== null && $currentMsgInteractionId !== $lastInteractionId): ?>
                            <div class="new-interaction-marker">--- Nueva consulta ---</div>
                        <?php endif; ?>
                        
                        <div class="mensaje <?php echo $mensaje['tipo']; ?>">
                            <?php if ($mensaje['tipo'] === 'bot' && strpos($mensaje['texto'], '<br>') !== false): ?>
                                <?php echo $mensaje['texto']; ?>
                            <?php else: ?>
                                <p><?php echo htmlspecialchars($mensaje['texto']); ?></p>
                            <?php endif; ?>

                            <?php if ($mensaje['tipo'] === 'bot' && ($mensaje['mostrar_formulario'] ?? false) && 
                                    isset($_SESSION['interactions'][$currentMsgInteractionId]) && 
                                    $_SESSION['interactions'][$currentMsgInteractionId]['state'] === 'symptoms_detected'): ?>
                                <?php
                                $sintomasParaJson = $mensaje['sintomas'] ?? [];
                                $jsonSintomas = json_encode($sintomasParaJson);
                                ?>
                                <button class="btn-cita" onclick="mostrarFormularioSintomas(this, '<?php echo $currentMsgInteractionId; ?>')" 
                                        data-sintomas='<?php echo htmlspecialchars($jsonSintomas, ENT_QUOTES, 'UTF-8'); ?>'>
                                    Detallar síntomas
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php $lastInteractionId = $currentMsgInteractionId; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Mostrar diagnósticos de interacciones completadas -->
                <?php foreach ($_SESSION['interactions'] as $interactionId => $interaction): ?>
                    <?php if ($interaction['state'] === 'form_completed' && $interaction['show_diagnosis']): ?>
                        <div class="interaction-section" data-interaction-id="<?php echo $interactionId; ?>">
                            <div class="diagnosis-card">
                                <p><strong>Posible diagnóstico:</strong> <?php echo implode(', ', $interaction['diagnosis_data']['diagnostico']); ?></p>
                                <p><strong>Medicamentos recomendados:</strong> <?php echo implode(', ', $interaction['diagnosis_data']['medicamentos']); ?></p>
                                <p><strong>Especialidad requerida:</strong> <?php echo $interaction['diagnosis_data']['especialidad']; ?></p>
                                <?php if (!empty($interaction['diagnosis_data']['doctores_disponibles'])): ?>
                                    <div class="btn-group-citas">
                                        <button class="btn-cita" onclick="mostrarModalConfirmacion('<?php echo $interactionId; ?>')">Sí, quiero reservar cita</button>
                                        <button class="btn-cita btn-secondary" onclick="rechazarCita('<?php echo $interactionId; ?>')">No, no quiero reservar cita</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="box-inputs">
                <form method="post" id="chatForm" onsubmit="return enviarMensaje(event)" action="">
                    <input type="hidden" name="current_interaction_id" id="current_interaction_id" value="<?php echo $currentInteractionId; ?>">
                    <input type="text" class="form-control me-2" name="mensaje" id="mensaje"
                        placeholder="Escribe tu mensaje aquí..." autocomplete="off" required>
                    <button type="submit" class="btn-send"><i class="fa-solid fa-square-arrow-up-right"></i></button>
                </form>
            </div>
        </div>

        <!-- Modal para síntomas detallados -->
        <div id="modalSintomas" class="modal-sintomas" style="<?php echo $mostrarModalSintomas ? 'display: block;' : 'display: none;' ?>">
            <div class="modal-content-sintomas <?php echo $mostrarModalSintomas ? 'show' : ''; ?>">
                <span class="close-modal" onclick="cerrarModal()">&times;</span>
                <h4>Detalle tus síntomas</h4>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['error']; ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form id="formSintomas" method="post">
                    <input type="hidden" name="current_interaction_id" id="form_interaction_id" value="<?php echo $currentInteractionId; ?>">
                    <div class="mb-3">
                        <label class="form-label">¿Desde cuándo presentas los síntomas?</label>
                        <select name="tiempo_general" class="form-select" required>
                            <option value="ahora">Hoy</option>
                            <option value="1_dia">Hace 1 día</option>
                            <option value="3_dias">Hace 3 días</option>
                            <option value="1_semana">Hace 1 semana</option>
                        </select>
                    </div>
                    <div id="camposSintomas">
                        <?php if (isset($currentInteraction['sintomas_detectar'])): ?>
                            <?php foreach ($currentInteraction['sintomas_detectar'] as $sintoma): ?>
                                <div class="sintoma-card">
                                    <div class="sintoma-title"><?php echo htmlspecialchars($sintoma['nombre']); ?></div>
                                    <input type="hidden" name="sintomas_detallados[<?php echo $sintoma['sintoma_id']; ?>][sintoma_id]" value="<?php echo $sintoma['sintoma_id']; ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Intensidad:</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sintomas_detallados[<?php echo $sintoma['sintoma_id']; ?>][intensidad]" id="intensidad_<?php echo $sintoma['sintoma_id']; ?>_leve" value="leve" required>
                                                <label class="form-check-label" for="intensidad_<?php echo $sintoma['sintoma_id']; ?>_leve">Leve</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sintomas_detallados[<?php echo $sintoma['sintoma_id']; ?>][intensidad]" id="intensidad_<?php echo $sintoma['sintoma_id']; ?>_moderado" value="moderado">
                                                <label class="form-check-label" for="intensidad_<?php echo $sintoma['sintoma_id']; ?>_moderado">Moderado</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sintomas_detallados[<?php echo $sintoma['sintoma_id']; ?>][intensidad]" id="intensidad_<?php echo $sintoma['sintoma_id']; ?>_intenso" value="intenso">
                                                <label class="form-check-label" for="intensidad_<?php echo $sintoma['sintoma_id']; ?>_intenso">Intenso</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Enviar síntomas</button>
                </form>
            </div>
        </div>

        <!-- Modal para confirmar cita -->
        <div id="modalConfirmarCita" class="modal-sintomas" style="<?php echo $mostrarModalCita ? 'display: block;' : 'display: none;' ?>">
            <div class="modal-content-sintomas <?php echo $mostrarModalCita ? 'show' : ''; ?>">
                <span class="close-modal" onclick="cerrarModalConfirmacion()">&times;</span>
                <h4>Confirmar cita médica - <?php echo $currentInteraction['diagnosis_data']['especialidad'] ?? ''; ?></h4>

                <?php if (!empty($currentInteraction['diagnosis_data']['doctores_disponibles'])): ?>
                    <form id="formConfirmarCita" method="post">
                        <input type="hidden" name="confirmar_cita" value="1">
                        <input type="hidden" name="current_interaction_id" value="<?php echo $currentInteractionId; ?>">
                        <input type="hidden" name="especialidad" value="<?php echo $currentInteraction['diagnosis_data']['especialidad'] ?? ''; ?>">

                        <div class="mb-3">
                            <label class="form-label">Seleccione un doctor:</label>
                            <select name="doctor" class="form-select" required>
                                <?php foreach ($currentInteraction['diagnosis_data']['doctores_disponibles'] as $doctor): ?>
                                    <option value="<?php echo $doctor['usuario_id']; ?>">
                                        Dr. <?php echo $doctor['nombre'] . ' ' . $doctor['apellido']; ?>
                                        (<?php echo $doctor['especialidad']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2" id="btnConfirmarCita">
                            <i class="fas fa-calendar-check me-2"></i> Confirmar cita
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <p class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i> No hay doctores disponibles para <?php echo $currentInteraction['diagnosis_data']['especialidad'] ?? 'esta especialidad'; ?>.</p>
                        <p class="mb-0">Por favor intenta más tarde o contacta con recepción.</p>
                    </div>
                    <button onclick="cerrarModalConfirmacion()" class="btn btn-secondary w-100">
                        <i class="fas fa-times me-2"></i> Cerrar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if (isset($_SESSION['usuario'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para enviar mensaje
        function enviarMensaje(event) {
            event.preventDefault();
            const input = document.getElementById('mensaje');
            const mensaje = input.value.trim();

            if (mensaje) {
                // Mostrar mensaje del usuario inmediatamente
                const chat = document.getElementById('chat-mensajes');
                const userDiv = document.createElement('div');
                userDiv.className = 'mensaje user';
                userDiv.innerHTML = `<p>${mensaje}</p>`;
                chat.appendChild(userDiv);

                // Limpiar el input y hacer scroll
                input.value = '';
                chat.scrollTop = chat.scrollHeight;

                // Mostrar indicador de carga
                const icon = document.querySelector('.fa-square-arrow-up-right');
                icon.classList.remove('fa-square-arrow-up-right');
                icon.classList.add('fa-spinner', 'fa-spin');

                // Enviar formulario con AJAX
                const formData = new FormData();
                formData.append('mensaje', mensaje);
                formData.append('current_interaction_id', document.getElementById('current_interaction_id').value);

                fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Parsear la respuesta
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Reemplazar el chat
                        const newChat = doc.getElementById('chat-mensajes');
                        if (newChat) {
                            document.getElementById('chat-mensajes').innerHTML = newChat.innerHTML;
                            chat.scrollTop = chat.scrollHeight;
                        }

                        // Verificar si hay que mostrar modal de síntomas
                        const sintomasButton = doc.querySelector('.btn-cita[data-sintomas]');
                        if (sintomasButton) {
                            const interactionId = sintomasButton.closest('.mensaje').getAttribute('data-interaction-id') || 
                                                 document.getElementById('current_interaction_id').value;
                            mostrarFormularioSintomas(sintomasButton, interactionId);
                        }

                        // Verificar si hay que mostrar modal de confirmación de cita
                        if (doc.getElementById('modalConfirmarCita').style.display === 'block') {
                            mostrarModalConfirmacion(document.getElementById('current_interaction_id').value);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Mostrar mensaje de error al usuario
                        const botDiv = document.createElement('div');
                        botDiv.className = 'mensaje bot';
                        botDiv.innerHTML = '<p>Ocurrió un error al procesar tu mensaje. Por favor intenta nuevamente.</p>';
                        if (error.message) {
                            botDiv.innerHTML += `<p><small>Detalle: ${error.message}</small></p>`;
                        }
                        chat.appendChild(botDiv);
                        chat.scrollTop = chat.scrollHeight;
                    })
                    .finally(() => {
                        // Restaurar icono
                        const icon = document.querySelector('.fa-spinner');
                        if (icon) {
                            icon.classList.remove('fa-spinner', 'fa-spin');
                            icon.classList.add('fa-square-arrow-up-right');
                        }
                    });
            }
            return false;
        }

        // Función para mostrar formulario de síntomas
        function mostrarFormularioSintomas(buttonElement, interactionId) {
            // Cerrar modal de confirmación si está abierto
            cerrarModalConfirmacion();
            
            try {
                const sintomas = JSON.parse(buttonElement.getAttribute('data-sintomas'));
                const camposDiv = document.getElementById('camposSintomas');
                camposDiv.innerHTML = '';

                sintomas.forEach(sintoma => {
                    camposDiv.innerHTML += `
                        <div class="sintoma-card">
                            <div class="sintoma-title">${sintoma.nombre}</div>
                            <input type="hidden" name="sintomas_detallados[${sintoma.sintoma_id}][sintoma_id]" value="${sintoma.sintoma_id}">
                            
                            <div class="mb-3">
                                <label class="form-label">Intensidad:</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sintomas_detallados[${sintoma.sintoma_id}][intensidad]" id="intensidad_${sintoma.sintoma_id}_leve" value="leve" required>
                                        <label class="form-check-label" for="intensidad_${sintoma.sintoma_id}_leve">Leve</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sintomas_detallados[${sintoma.sintoma_id}][intensidad]" id="intensidad_${sintoma.sintoma_id}_moderado" value="moderado">
                                        <label class="form-check-label" for="intensidad_${sintoma.sintoma_id}_moderado">Moderado</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sintomas_detallados[${sintoma.sintoma_id}][intensidad]" id="intensidad_${sintoma.sintoma_id}_intenso" value="intenso">
                                        <label class="form-check-label" for="intensidad_${sintoma.sintoma_id}_intenso">Intenso</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Actualizar el ID de interacción en el formulario
                document.getElementById('form_interaction_id').value = interactionId;

                // Mostrar modal
                const modal = document.getElementById('modalSintomas');
                modal.style.display = 'block';
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            } catch (e) {
                console.error("Error al mostrar formulario de síntomas:", e);
            }
        }

        // Función para mostrar modal de confirmación de cita
        function mostrarModalConfirmacion(interactionId) {
            // Cerrar modal de síntomas si está abierto
            cerrarModal();
            
            // Actualizar el ID de interacción en el formulario si se especifica
            if (interactionId) {
                document.getElementById('current_interaction_id').value = interactionId;
            }
            
            const modal = document.getElementById('modalConfirmarCita');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        // Función para rechazar cita
        function rechazarCita(interactionId) {
            // Deshabilitar botones para evitar múltiples clics
            const buttons = document.querySelectorAll(`[onclick*="${interactionId}"]`);
            buttons.forEach(btn => btn.disabled = true);
            
            // Mostrar mensaje de agradecimiento
            const chat = document.getElementById('chat-mensajes');
            const botDiv = document.createElement('div');
            botDiv.className = 'mensaje bot';
            botDiv.innerHTML = '<p>Gracias por usar PriorizaNow. Si necesitas ayuda con otro problema de salud, por favor descríbemelo.</p>';
            chat.appendChild(botDiv);
            chat.scrollTop = chat.scrollHeight;
            
            // Crear nueva interacción
            const newInteractionId = 'interaction_' + Date.now();
            document.getElementById('current_interaction_id').value = newInteractionId;
            
            // Enviar solicitud al servidor para actualizar la interacción
            const formData = new FormData();
            formData.append('rechazar_cita', '1');
            formData.append('current_interaction_id', interactionId);
            formData.append('new_interaction_id', newInteractionId);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
        }

        // Función para cerrar modales
        function cerrarModal() {
            const modal = document.getElementById('modalSintomas');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function cerrarModalConfirmacion() {
            const modal = document.getElementById('modalConfirmarCita');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.className === 'modal-sintomas') {
                event.target.classList.remove('show');
                setTimeout(() => {
                    event.target.style.display = 'none';
                }, 300);
            }
        }

        // Manejar el formulario de síntomas para incluir el tiempo general
        document.getElementById('formSintomas')?.addEventListener('submit', function(e) {
            const tiempoGeneral = document.querySelector('select[name="tiempo_general"]').value;
            const sintomaInputs = document.querySelectorAll('input[name^="sintomas_detallados"]');

            // Crear inputs hidden para el tiempo en cada síntoma
            const sintomasIds = new Set();
            sintomaInputs.forEach(input => {
                const match = input.name.match(/\[(\d+)\]/);
                if (match) {
                    sintomasIds.add(match[1]);
                }
            });

            // Agregar inputs hidden para el tiempo
            sintomasIds.forEach(id => {
                const tiempoInput = document.createElement('input');
                tiempoInput.type = 'hidden';
                tiempoInput.name = `sintomas_detallados[${id}][tiempo]`;
                tiempoInput.value = tiempoGeneral;
                this.appendChild(tiempoInput);
            });
        });

        // Manejar el envío del formulario de confirmación de cita
        document.getElementById('formConfirmarCita')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnConfirmarCita');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        });

        // Inicializar scroll al final al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const chat = document.getElementById('chat-mensajes');
            chat.scrollTop = chat.scrollHeight;

            <?php if ($mostrarAlerta): ?>
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#f8f9fa',
                    color: '#212529',
                    iconColor: 'green',
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: 'Hola <?php echo $_SESSION['usuario']['nombre']; ?>!',
                    text: 'Has iniciado sesión correctamente'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>